<?php

namespace App\Services;

use App\Events\RankingsUpdated;
use App\Models\Contestant;
use App\Models\Pageant;
use App\Models\Round;
use App\Models\Score;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ScoreCalculationService
{
    /**
     * Calculate RANK.AVG equivalent - handles ties by averaging ranks.
     * This mimics Excel's RANK.AVG function.
     *
     * @param  float  $value  The value to rank
     * @param  array  $allValues  All values to rank against
     * @param  string  $direction  'desc' for highest=1, 'asc' for lowest=1
     * @return float The rank (may be decimal for ties, e.g., 2.5)
     */
    public function calculateRankAvg(float $value, array $allValues, string $direction = 'desc'): float
    {
        $betterCount = 0;
        $tieCount = 0;

        foreach ($allValues as $otherValue) {
            if ($direction === 'desc') {
                // Higher is better
                if ($otherValue > $value) {
                    $betterCount++;
                } elseif (abs($otherValue - $value) < 0.00001) {
                    $tieCount++;
                }
            } else {
                // Lower is better (for rank sums)
                if ($otherValue < $value) {
                    $betterCount++;
                } elseif (abs($otherValue - $value) < 0.00001) {
                    $tieCount++;
                }
            }
        }

        // RANK.AVG: start rank + (number of ties - 1) / 2
        $startRank = $betterCount + 1;

        return $startRank + ($tieCount - 1) / 2;
    }

    /**
     * Apply ranking with proper tie handling to an array of contestants.
     *
     * @param  array  $contestants  Array of contestant data with score/rankSum field
     * @param  string  $scoreField  The field to rank by ('finalScore' or 'totalRankSum')
     * @param  string  $direction  'desc' for score_average, 'asc' for rank_sum
     * @param  string  $tieHandling  'sequential', 'average', or 'minimum'
     * @return array Contestants with 'rank' field added
     */
    public function applyRanking(array $contestants, string $scoreField, string $direction, string $tieHandling): array
    {
        if (empty($contestants)) {
            return $contestants;
        }

        // Sort contestants
        usort($contestants, function ($a, $b) use ($scoreField, $direction) {
            $aVal = $a[$scoreField] ?? 0;
            $bVal = $b[$scoreField] ?? 0;

            return $direction === 'desc'
                ? $bVal <=> $aVal
                : $aVal <=> $bVal;
        });

        // Extract values for RANK.AVG calculation
        $allValues = array_column($contestants, $scoreField);

        foreach ($contestants as $index => &$contestant) {
            $value = $contestant[$scoreField] ?? 0;

            switch ($tieHandling) {
                case 'average':
                    $contestant['rank'] = $this->calculateRankAvg($value, $allValues, $direction);
                    break;

                case 'minimum':
                    $rank = 1;
                    foreach ($allValues as $otherValue) {
                        if ($direction === 'desc' && $otherValue > $value) {
                            $rank++;
                        } elseif ($direction === 'asc' && $otherValue < $value) {
                            $rank++;
                        }
                    }
                    $contestant['rank'] = $rank;
                    break;

                case 'sequential':
                default:
                    $contestant['rank'] = $index + 1;
                    break;
            }
        }

        return $contestants;
    }

    /**
     * Apply ranking with gender separation for pair pageants.
     */
    private function applyGenderSeparatedRanking(
        array $contestants,
        Pageant $pageant,
        string $scoreField,
        string $direction,
        string $tieHandling
    ): array {
        if ($pageant->isPairsOnly() || $pageant->allowsBothTypes()) {
            $maleContestants = array_filter($contestants, fn ($c) => ($c['gender'] ?? '') === 'male');
            $femaleContestants = array_filter($contestants, fn ($c) => ($c['gender'] ?? '') === 'female');

            $maleContestants = $this->applyRanking($maleContestants, $scoreField, $direction, $tieHandling);
            $femaleContestants = $this->applyRanking($femaleContestants, $scoreField, $direction, $tieHandling);

            foreach ($maleContestants as &$c) {
                $c['genderRank'] = $c['rank'];
            }
            foreach ($femaleContestants as &$c) {
                $c['genderRank'] = $c['rank'];
            }

            return array_merge(array_values($maleContestants), array_values($femaleContestants));
        }

        return $this->applyRanking($contestants, $scoreField, $direction, $tieHandling);
    }

    /**
     * Get all scores from a specific judge for all contestants in a round.
     */
    private function getAllJudgeScoresForRound(int $judgeId, int $roundId, Pageant $pageant): array
    {
        $scores = [];

        foreach ($pageant->contestants as $contestant) {
            $score = $this->calculateJudgeContestantScore($judgeId, $contestant->id, $roundId, $pageant->id);
            if ($score !== null) {
                $scores[$contestant->id] = $score;
            }
        }

        return $scores;
    }

    /**
     * Get judge ranks for a contestant in a specific round.
     */
    private function getJudgeRanksForRound(Contestant $contestant, Round $round, Pageant $pageant, string $tieHandling): array
    {
        $scores = [];
        $ranks = [];
        $judgeDetails = [];

        foreach ($pageant->judges as $judge) {
            $judgeScore = $this->calculateJudgeContestantScore($judge->id, $contestant->id, $round->id, $pageant->id);

            if ($judgeScore !== null) {
                $allJudgeScores = $this->getAllJudgeScoresForRound($judge->id, $round->id, $pageant);
                $rank = $this->calculateRankAvg($judgeScore, $allJudgeScores, 'desc');

                $scores[] = $judgeScore;
                $ranks[] = $rank;

                $judgeDetails[] = [
                    'judge_id' => $judge->id,
                    'judge_name' => $judge->name ?? "Judge {$judge->id}",
                    'score' => round($judgeScore, 2),
                    'rank' => $rank,
                ];
            }
        }

        return [
            'scores' => $scores,
            'ranks' => $ranks,
            'details' => $judgeDetails,
        ];
    }

    /**
     * Calculate comprehensive scores using rank-sum method (Excel-style).
     *
     * @return array<int, array<string, mixed>>
     */
    public function calculateWithRankSum(Pageant $pageant, ?string $stage = null, bool $useCache = true): array
    {
        try {
            $cacheKey = $stage
                ? "pageant_ranksum_scores_{$pageant->id}_{$stage}"
                : "pageant_ranksum_scores_{$pageant->id}_all";

            if ($useCache && Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            $rounds = $stage
                ? $pageant->rounds->filter(fn ($r) => ($r->type ?? null) === $stage)
                : $pageant->rounds;

            if ($rounds->isEmpty()) {
                return [];
            }

            $contestants = [];
            $tieHandling = $pageant->tie_handling ?? 'average';

            foreach ($pageant->contestants as $contestant) {
                $roundScores = [];
                $judgeRanks = [];
                $totalRankSum = 0;
                $totalWeightedScore = 0;
                $totalRoundWeight = 0;

                foreach ($rounds as $round) {
                    $roundWeight = $round->weight ?? 1;
                    if ($roundWeight <= 0) {
                        $roundWeight = 1;
                    }

                    $roundJudgeData = $this->getJudgeRanksForRound($contestant, $round, $pageant, $tieHandling);

                    if (! empty($roundJudgeData['scores'])) {
                        $roundAvgScore = array_sum($roundJudgeData['scores']) / count($roundJudgeData['scores']);
                        $roundScores[$round->name] = round($roundAvgScore, 2);

                        $roundRankSum = array_sum($roundJudgeData['ranks']);
                        $totalRankSum += $roundRankSum;

                        $totalWeightedScore += $roundAvgScore * $roundWeight;
                        $totalRoundWeight += $roundWeight;

                        $judgeRanks[$round->name] = $roundJudgeData;
                    }
                }

                $finalScore = $totalRoundWeight > 0 ? $totalWeightedScore / $totalRoundWeight : 0;

                $memberNames = [];
                $memberGenders = [];
                if ($contestant->is_pair && $contestant->members && $contestant->members->isNotEmpty()) {
                    foreach ($contestant->members as $member) {
                        $memberNames[] = $member->name;
                        $memberGenders[] = $member->gender;
                    }
                }

                $contestants[] = [
                    'id' => $contestant->id,
                    'number' => $contestant->number,
                    'name' => $contestant->name,
                    'gender' => $contestant->gender,
                    'region' => $contestant->origin,
                    'is_pair' => $contestant->is_pair ?? false,
                    'member_names' => $memberNames,
                    'member_genders' => $memberGenders,
                    'image' => $contestant->photo ?? '/images/placeholders/contestant.jpg',
                    'scores' => $roundScores,
                    'judgeRanks' => $judgeRanks,
                    'totalRankSum' => round($totalRankSum, 2),
                    'finalScore' => round($finalScore, 2),
                    'totalScore' => round($finalScore, 2),
                ];
            }

            $result = $this->applyGenderSeparatedRanking(
                $contestants,
                $pageant,
                'totalRankSum',
                'asc',
                $tieHandling
            );

            Cache::put($cacheKey, $result, now()->addMinutes(30));

            return $result;

        } catch (\Exception $e) {
            Log::error('Error calculating rank-sum scores: '.$e->getMessage(), [
                'pageant_id' => $pageant->id,
                'stage' => $stage,
                'trace' => $e->getTraceAsString(),
            ]);

            return [];
        }
    }

    /**
     * Calculate stage-specific final scores.
     * Uses the pageant's ranking_method setting.
     *
     * @param  string  $stage  Accepts 'semi-final' or 'final'
     * @return array<int, array<string, mixed>>
     */
    public function calculatePageantStageScores(Pageant $pageant, string $stage, bool $useCache = true): array
    {
        if (($pageant->ranking_method ?? 'score_average') === 'rank_sum') {
            return $this->calculateWithRankSum($pageant, $stage, $useCache);
        }

        try {
            $cacheKey = "pageant_stage_scores_{$pageant->id}_{$stage}";

            if ($useCache && Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            $contestants = [];
            $tieHandling = $pageant->tie_handling ?? 'average';

            $stageRounds = $pageant->rounds->filter(function ($round) use ($stage) {
                return ($round->type ?? null) === $stage;
            });

            foreach ($pageant->contestants as $contestant) {
                $roundScores = [];
                $judgeRanks = [];
                $totalWeightedScore = 0;
                $totalRoundWeight = 0;

                foreach ($stageRounds as $round) {
                    $roundScore = $this->calculateContestantRoundScore($contestant, $round, $pageant);

                    if ($roundScore !== null) {
                        $roundScores[$round->name] = round($roundScore, 2);

                        $roundWeight = $round->weight ?? 1;
                        if ($roundWeight <= 0) {
                            $roundWeight = 1;
                        }

                        $totalWeightedScore += $roundScore * $roundWeight;
                        $totalRoundWeight += $roundWeight;

                        $roundJudgeData = $this->getJudgeRanksForRound($contestant, $round, $pageant, $tieHandling);
                        $judgeRanks[$round->name] = $roundJudgeData;
                    }
                }

                $finalScore = $totalRoundWeight > 0 ? $totalWeightedScore / $totalRoundWeight : 0;

                $memberNames = [];
                $memberGenders = [];
                if ($contestant->is_pair && $contestant->members && $contestant->members->isNotEmpty()) {
                    foreach ($contestant->members as $member) {
                        $memberNames[] = $member->name;
                        $memberGenders[] = $member->gender;
                    }
                }

                $contestants[] = [
                    'id' => $contestant->id,
                    'number' => $contestant->number,
                    'name' => $contestant->name,
                    'gender' => $contestant->gender,
                    'region' => $contestant->origin,
                    'is_pair' => $contestant->is_pair ?? false,
                    'member_names' => $memberNames,
                    'member_genders' => $memberGenders,
                    'image' => $contestant->photo ?? '/images/placeholders/contestant.jpg',
                    'scores' => $roundScores,
                    'judgeRanks' => $judgeRanks,
                    'finalScore' => round($finalScore, 2),
                    'totalScore' => round($finalScore, 2),
                    'totalRankSum' => 0,
                ];
            }

            $result = $this->applyGenderSeparatedRanking(
                $contestants,
                $pageant,
                'finalScore',
                'desc',
                $tieHandling
            );

            Cache::put($cacheKey, $result, now()->addMinutes(30));

            return $result;

        } catch (\Exception $e) {
            Log::error('Error calculating pageant stage scores: '.$e->getMessage(), [
                'pageant_id' => $pageant->id,
                'stage' => $stage,
                'trace' => $e->getTraceAsString(),
            ]);

            return [];
        }
    }

    /**
     * Get Top N contestants, including all ties at the cutoff.
     *
     * @param  array  $contestants  Already ranked contestants
     * @param  int  $topN  Number of positions to include
     * @return array<int> Contestant IDs
     */
    private function getTopNWithTies(array $contestants, int $topN): array
    {
        if (empty($contestants)) {
            return [];
        }

        $advancingIds = [];

        foreach ($contestants as $contestant) {
            $rank = $contestant['rank'] ?? PHP_INT_MAX;

            if ($rank <= $topN) {
                $advancingIds[] = $contestant['id'];
            }
        }

        return $advancingIds;
    }

    /**
     * Get the IDs of contestants who advance from a given stage based on top_n_proceed.
     * IMPROVED: Uses proper ranking with tie handling and includes all tied contestants at cutoff.
     *
     * @param  string  $stage  The stage type to get results from
     * @return array<int> Array of contestant IDs that advance
     */
    public function getAdvancingContestantIds(Pageant $pageant, string $stage): array
    {
        try {
            $stageRounds = $pageant->rounds->filter(fn ($round) => ($round->type ?? null) === $stage);

            if ($stageRounds->isEmpty()) {
                return [];
            }

            $lastRound = $stageRounds->sortByDesc('display_order')->first();
            $topN = $lastRound->top_n_proceed;

            if ($topN === null || $topN <= 0) {
                return [];
            }

            $stageResults = $this->calculatePageantStageScores($pageant, $stage, false);

            if (empty($stageResults)) {
                return [];
            }

            $rankingMethod = $pageant->ranking_method ?? 'score_average';
            $scoreField = $rankingMethod === 'rank_sum' ? 'totalRankSum' : 'finalScore';

            $scoredContestants = array_filter($stageResults, function ($c) use ($scoreField, $rankingMethod) {
                if ($rankingMethod === 'rank_sum') {
                    return ($c[$scoreField] ?? 0) > 0;
                }

                return ($c['finalScore'] ?? 0) > 0;
            });

            if (empty($scoredContestants)) {
                return [];
            }

            $advancingIds = [];

            if ($pageant->isPairsOnly() || $pageant->allowsBothTypes()) {
                $maleContestants = array_filter($scoredContestants, fn ($c) => ($c['gender'] ?? '') === 'male');
                $femaleContestants = array_filter($scoredContestants, fn ($c) => ($c['gender'] ?? '') === 'female');

                $advancingIds = array_merge(
                    $this->getTopNWithTies($maleContestants, $topN),
                    $this->getTopNWithTies($femaleContestants, $topN)
                );
            } else {
                $advancingIds = $this->getTopNWithTies($scoredContestants, $topN);
            }

            return $advancingIds;

        } catch (\Exception $e) {
            Log::error('Error getting advancing contestant IDs: '.$e->getMessage(), [
                'pageant_id' => $pageant->id,
                'stage' => $stage,
                'trace' => $e->getTraceAsString(),
            ]);

            return [];
        }
    }

    /**
     * Get the previous stage type for a given round.
     *
     * @return string|null The previous stage type, or null if none
     */
    public function getPreviousStageType(Pageant $pageant, Round $round): ?string
    {
        $stageTypes = $pageant->rounds
            ->groupBy('type')
            ->map(function ($rounds) {
                return $rounds->min('display_order');
            })
            ->sortBy(fn ($order) => $order)
            ->keys()
            ->values()
            ->toArray();

        $currentStageType = $round->type;
        $currentIndex = array_search($currentStageType, $stageTypes);

        if ($currentIndex === false || $currentIndex === 0) {
            return null;
        }

        return $stageTypes[$currentIndex - 1];
    }

    /**
     * Compute per-round minor awards for a pageant's stage.
     *
     * @param  string  $stage  Typically 'semi-final'
     * @return array<string, array<int, array<string, mixed>>> keyed by round name
     */
    public function calculateMinorAwardsByStage(Pageant $pageant, string $stage = 'semi-final'): array
    {
        try {
            $resultsByRound = [];
            $isPairPageant = $pageant->isPairsOnly() || $pageant->allowsBothTypes();

            $stageRounds = $pageant->rounds->filter(function ($round) {
                return ($round->use_for_minor_awards ?? false) === true;
            });

            foreach ($stageRounds as $round) {
                $contestantScores = [];

                foreach ($pageant->contestants as $contestant) {
                    $roundScore = $this->calculateContestantRoundScore($contestant, $round, $pageant);
                    if ($roundScore !== null) {
                        $memberNames = [];
                        $memberGenders = [];

                        if ($contestant->is_pair && $contestant->members && $contestant->members->isNotEmpty()) {
                            foreach ($contestant->members as $member) {
                                $memberNames[] = $member->name;
                                $memberGenders[] = $member->gender;
                            }
                        }

                        $contestantScores[] = [
                            'id' => $contestant->id,
                            'number' => $contestant->number,
                            'name' => $contestant->name,
                            'gender' => $contestant->gender,
                            'is_pair' => $contestant->is_pair ?? false,
                            'member_names' => $memberNames,
                            'member_genders' => $memberGenders,
                            'image' => $contestant->photo ?? '/images/placeholders/contestant.jpg',
                            'score' => round($roundScore, 2),
                        ];
                    }
                }

                if (empty($contestantScores)) {
                    $resultsByRound[$round->name] = [];

                    continue;
                }

                if ($isPairPageant) {
                    $maleScores = array_filter($contestantScores, fn ($c) => ($c['gender'] ?? '') === 'male');
                    $femaleScores = array_filter($contestantScores, fn ($c) => ($c['gender'] ?? '') === 'female');

                    $maleWinners = [];
                    $femaleWinners = [];

                    if (! empty($maleScores)) {
                        usort($maleScores, fn ($a, $b) => $b['score'] <=> $a['score']);
                        $topMaleScore = $maleScores[0]['score'];
                        $maleWinners = array_values(array_filter($maleScores, function ($row) use ($topMaleScore) {
                            return abs($row['score'] - $topMaleScore) < 0.00001;
                        }));
                    }

                    if (! empty($femaleScores)) {
                        usort($femaleScores, fn ($a, $b) => $b['score'] <=> $a['score']);
                        $topFemaleScore = $femaleScores[0]['score'];
                        $femaleWinners = array_values(array_filter($femaleScores, function ($row) use ($topFemaleScore) {
                            return abs($row['score'] - $topFemaleScore) < 0.00001;
                        }));
                    }

                    $resultsByRound[$round->name] = [
                        'round' => [
                            'id' => $round->id,
                            'name' => $round->name,
                            'type' => $round->type,
                            'weight' => $round->weight,
                        ],
                        'is_pair_pageant' => true,
                        'male_winners' => $maleWinners,
                        'female_winners' => $femaleWinners,
                        'winners' => array_merge($maleWinners, $femaleWinners),
                    ];
                } else {
                    usort($contestantScores, fn ($a, $b) => $b['score'] <=> $a['score']);
                    $topScore = $contestantScores[0]['score'];
                    $topContestants = array_values(array_filter($contestantScores, function ($row) use ($topScore) {
                        return abs($row['score'] - $topScore) < 0.00001;
                    }));

                    $resultsByRound[$round->name] = [
                        'round' => [
                            'id' => $round->id,
                            'name' => $round->name,
                            'type' => $round->type,
                            'weight' => $round->weight,
                        ],
                        'is_pair_pageant' => false,
                        'winners' => $topContestants,
                    ];
                }
            }

            return $resultsByRound;
        } catch (\Exception $e) {
            Log::error('Error calculating minor awards by stage: '.$e->getMessage(), [
                'pageant_id' => $pageant->id,
                'stage' => $stage,
                'trace' => $e->getTraceAsString(),
            ]);

            return [];
        }
    }

    /**
     * Calculate final scores for all contestants in a pageant.
     * Uses the pageant's ranking_method setting.
     */
    public function calculatePageantFinalScores(Pageant $pageant, bool $useCache = true): array
    {
        if (($pageant->ranking_method ?? 'score_average') === 'rank_sum') {
            return $this->calculateWithRankSum($pageant, null, $useCache);
        }

        try {
            $cacheKey = "pageant_final_scores_{$pageant->id}";

            if ($useCache && Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            $contestants = [];
            $tieHandling = $pageant->tie_handling ?? 'average';

            foreach ($pageant->contestants as $contestant) {
                $roundScores = [];
                $judgeRanks = [];
                $totalWeightedScore = 0;
                $totalRoundWeight = 0;

                foreach ($pageant->rounds as $round) {
                    $roundScore = $this->calculateContestantRoundScore($contestant, $round, $pageant);

                    if ($roundScore !== null) {
                        $roundScores[$round->name] = round($roundScore, 2);

                        $roundWeight = $round->weight ?? 1;
                        if ($roundWeight <= 0) {
                            $roundWeight = 1;
                        }

                        $totalWeightedScore += $roundScore * $roundWeight;
                        $totalRoundWeight += $roundWeight;

                        $roundJudgeData = $this->getJudgeRanksForRound($contestant, $round, $pageant, $tieHandling);
                        $judgeRanks[$round->name] = $roundJudgeData;
                    }
                }

                $finalScore = $totalRoundWeight > 0 ? $totalWeightedScore / $totalRoundWeight : 0;

                $memberNames = [];
                $memberGenders = [];
                if ($contestant->is_pair && $contestant->members && $contestant->members->isNotEmpty()) {
                    foreach ($contestant->members as $member) {
                        $memberNames[] = $member->name;
                        $memberGenders[] = $member->gender;
                    }
                }

                $contestants[] = [
                    'id' => $contestant->id,
                    'number' => $contestant->number,
                    'name' => $contestant->name,
                    'gender' => $contestant->gender,
                    'region' => $contestant->origin,
                    'is_pair' => $contestant->is_pair ?? false,
                    'member_names' => $memberNames,
                    'member_genders' => $memberGenders,
                    'image' => $contestant->photo ?? '/images/placeholders/contestant.jpg',
                    'scores' => $roundScores,
                    'judgeRanks' => $judgeRanks,
                    'finalScore' => round($finalScore, 2),
                    'totalScore' => round($finalScore, 2),
                    'totalRankSum' => 0,
                ];
            }

            $result = $this->applyGenderSeparatedRanking(
                $contestants,
                $pageant,
                'finalScore',
                'desc',
                $tieHandling
            );

            Cache::put($cacheKey, $result, now()->addMinutes(30));
            RankingsUpdated::dispatch($pageant->id, $result);

            return $result;

        } catch (\Exception $e) {
            Log::error('Error calculating pageant final scores: '.$e->getMessage(), [
                'pageant_id' => $pageant->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return [];
        }
    }

    /**
     * Calculate a contestant's score for a specific round
     */
    public function calculateContestantRoundScore(Contestant $contestant, $round, Pageant $pageant): ?float
    {
        try {
            $cacheKey = "contestant_round_score_{$contestant->id}_{$round->id}";

            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            $judgeAverages = [];

            foreach ($pageant->judges as $judge) {
                $judgeScore = $this->calculateJudgeContestantScore($judge->id, $contestant->id, $round->id, $pageant->id);

                if ($judgeScore !== null) {
                    $judgeAverages[] = $judgeScore;
                }
            }

            $roundScore = null;
            if (! empty($judgeAverages)) {
                $roundScore = array_sum($judgeAverages) / count($judgeAverages);
            }

            Cache::put($cacheKey, $roundScore, now()->addMinutes(15));

            return $roundScore;

        } catch (\Exception $e) {
            Log::error('Error calculating contestant round score: '.$e->getMessage(), [
                'contestant_id' => $contestant->id,
                'round_id' => $round->id,
                'pageant_id' => $pageant->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Calculate weighted average score for a specific judge-contestant-round combination
     */
    public function calculateJudgeContestantScore(int $judgeId, int $contestantId, int $roundId, int $pageantId): ?float
    {
        try {
            $scores = Score::where('pageant_id', $pageantId)
                ->where('round_id', $roundId)
                ->where('judge_id', $judgeId)
                ->where('contestant_id', $contestantId)
                ->with('criteria')
                ->get();

            if ($scores->isEmpty()) {
                return null;
            }

            $criteriaWeightedSum = 0;
            $criteriaWeightTotal = 0;

            foreach ($scores as $score) {
                $weight = $score->criteria->weight ?? 1;

                if ($weight <= 0) {
                    $weight = 1;
                }

                if (! is_numeric($score->score)) {
                    continue;
                }

                $criteriaWeightedSum += $score->score * $weight;
                $criteriaWeightTotal += $weight;
            }

            if ($criteriaWeightTotal <= 0) {
                return null;
            }

            return $criteriaWeightedSum / $criteriaWeightTotal;

        } catch (\Exception $e) {
            Log::error('Error calculating judge contestant score: '.$e->getMessage(), [
                'judge_id' => $judgeId,
                'contestant_id' => $contestantId,
                'round_id' => $roundId,
                'pageant_id' => $pageantId,
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Get final score for a specific contestant
     */
    public function getContestantFinalScore(int $pageantId, int $contestantId, bool $useCache = true): ?float
    {
        try {
            $cacheKey = "contestant_final_score_{$pageantId}_{$contestantId}";

            if ($useCache && Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            $pageant = Pageant::with(['contestants', 'rounds', 'judges'])->findOrFail($pageantId);
            $contestant = $pageant->contestants->find($contestantId);

            if (! $contestant) {
                return null;
            }

            $totalWeightedScore = 0;
            $totalRoundWeight = 0;

            foreach ($pageant->rounds as $round) {
                $roundScore = $this->calculateContestantRoundScore($contestant, $round, $pageant);

                if ($roundScore !== null) {
                    $roundWeight = $round->weight ?? 1;
                    if ($roundWeight <= 0) {
                        $roundWeight = 1;
                    }

                    $totalWeightedScore += $roundScore * $roundWeight;
                    $totalRoundWeight += $roundWeight;
                }
            }

            $finalScore = $totalRoundWeight > 0 ? $totalWeightedScore / $totalRoundWeight : null;

            Cache::put($cacheKey, $finalScore, now()->addMinutes(30));

            return $finalScore;

        } catch (\Exception $e) {
            Log::error('Error calculating contestant final score: '.$e->getMessage(), [
                'pageant_id' => $pageantId,
                'contestant_id' => $contestantId,
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Invalidate all cache entries for a contestant
     */
    public function invalidateContestantCache(int $pageantId, int $contestantId): void
    {
        Cache::forget("contestant_final_score_{$pageantId}_{$contestantId}");
        Cache::forget("pageant_final_scores_{$pageantId}");
        Cache::forget("pageant_ranksum_scores_{$pageantId}_all");

        $pageant = Pageant::with('rounds')->find($pageantId);
        if ($pageant) {
            foreach ($pageant->rounds as $round) {
                Cache::forget("contestant_round_score_{$contestantId}_{$round->id}");

                if ($round->type) {
                    Cache::forget("pageant_stage_scores_{$pageantId}_{$round->type}");
                    Cache::forget("pageant_ranksum_scores_{$pageantId}_{$round->type}");
                }
            }
        }
    }

    /**
     * Invalidate all cache entries for a pageant
     */
    public function invalidatePageantCache(int $pageantId): void
    {
        Cache::forget("pageant_final_scores_{$pageantId}");
        Cache::forget("pageant_ranksum_scores_{$pageantId}_all");

        $pageant = Pageant::with(['contestants', 'rounds'])->find($pageantId);
        if ($pageant) {
            $stageTypes = $pageant->rounds->pluck('type')->unique()->filter();
            foreach ($stageTypes as $stageType) {
                Cache::forget("pageant_stage_scores_{$pageantId}_{$stageType}");
                Cache::forget("pageant_ranksum_scores_{$pageantId}_{$stageType}");
            }

            foreach ($pageant->contestants as $contestant) {
                $this->invalidateContestantCache($pageantId, $contestant->id);
            }
        }
    }

    /**
     * Normalize score based on criteria-specific ranges
     */
    public function normalizeScore(float $score, $criteria): float
    {
        $minScore = $criteria->min_score ?? 0;
        $maxScore = $criteria->max_score ?? 100;

        return max($minScore, min($maxScore, $score));
    }

    /**
     * Get top N contestants by final score
     */
    public function getTopContestants(int $pageantId, int $limit = 10): array
    {
        $pageant = Pageant::with(['contestants', 'rounds', 'judges'])->findOrFail($pageantId);
        $allResults = $this->calculatePageantFinalScores($pageant);

        return array_slice($allResults, 0, $limit);
    }
}
