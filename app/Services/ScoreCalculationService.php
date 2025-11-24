<?php

namespace App\Services;

use App\Events\RankingsUpdated;
use App\Models\Contestant;
use App\Models\Pageant;
use App\Models\Score;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ScoreCalculationService
{
    /**
     * Calculate stage-specific final scores (e.g., only semi-final or only final rounds)
     *
     * @param  string  $stage  Accepts 'semi-final' or 'final'
     * @return array<int, array<string, mixed>>
     */
    public function calculatePageantStageScores(Pageant $pageant, string $stage, bool $useCache = true): array
    {
        try {
            $cacheKey = "pageant_stage_scores_{$pageant->id}_{$stage}";

            if ($useCache && Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            $contestants = [];

            // Filter rounds by stage/type
            $stageRounds = $pageant->rounds->filter(function ($round) use ($stage) {
                return ($round->type ?? null) === $stage;
            });

            foreach ($pageant->contestants as $contestant) {
                $roundScores = [];
                $totalWeightedScore = 0;
                $totalRoundWeight = 0;

                foreach ($stageRounds as $round) {
                    $roundScore = $this->calculateContestantRoundScore($contestant, $round, $pageant);

                    if ($roundScore !== null) {
                        $roundScores[$round->name] = $roundScore;

                        $roundWeight = $round->weight ?? 1;
                        if ($roundWeight <= 0) {
                            $roundWeight = 1;
                        }

                        $totalWeightedScore += $roundScore * $roundWeight;
                        $totalRoundWeight += $roundWeight;
                    }
                }

                $finalScore = $totalRoundWeight > 0 ? $totalWeightedScore / $totalRoundWeight : 0;

                $contestants[] = [
                    'id' => $contestant->id,
                    'number' => $contestant->number,
                    'name' => $contestant->name,
                    'gender' => $contestant->gender,
                    'region' => $contestant->origin,
                    'image' => $contestant->photo ?? '/images/placeholders/contestant.jpg',
                    'scores' => $roundScores,
                    'finalScore' => round($finalScore, 2),
                ];
            }

            // For pair pageants, separate rankings by gender
            if ($pageant->isPairsOnly() || $pageant->allowsBothTypes()) {
                $maleContestants = array_filter($contestants, fn ($c) => ($c['gender'] ?? '') === 'male');
                $femaleContestants = array_filter($contestants, fn ($c) => ($c['gender'] ?? '') === 'female');

                // Sort male contestants
                usort($maleContestants, fn ($a, $b) => $b['finalScore'] <=> $a['finalScore']);
                foreach ($maleContestants as $index => &$contestant) {
                    $contestant['rank'] = $index + 1;
                    $contestant['genderRank'] = $index + 1;
                }

                // Sort female contestants
                usort($femaleContestants, fn ($a, $b) => $b['finalScore'] <=> $a['finalScore']);
                foreach ($femaleContestants as $index => &$contestant) {
                    $contestant['rank'] = $index + 1;
                    $contestant['genderRank'] = $index + 1;
                }

                // Combine and maintain separate rankings
                $result = array_merge($maleContestants, $femaleContestants);
            } else {
                // Sort by final score descending and add ranks (standard ranking)
                usort($contestants, fn ($a, $b) => $b['finalScore'] <=> $a['finalScore']);
                foreach ($contestants as $index => &$contestant) {
                    $contestant['rank'] = $index + 1;
                }
                $result = $contestants;
            }

            // Cache for 30 minutes
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
     * Compute per-round minor awards for a pageant's semi-final stage.
     * For each round in the given stage, find the contestant(s) with the highest average score.
     * For pair pageants, separate winners by gender.
     *
     * @param  string  $stage  Typically 'semi-final'
     * @return array<string, array<int, array<string, mixed>>> keyed by round name
     */
    public function calculateMinorAwardsByStage(Pageant $pageant, string $stage = 'semi-final'): array
    {
        try {
            $resultsByRound = [];
            $isPairPageant = $pageant->isPairsOnly() || $pageant->allowsBothTypes();

            $stageRounds = $pageant->rounds->filter(function ($round) use ($stage) {
                return ($round->type ?? null) === $stage;
            });

            foreach ($stageRounds as $round) {
                $contestantScores = [];

                foreach ($pageant->contestants as $contestant) {
                    $roundScore = $this->calculateContestantRoundScore($contestant, $round, $pageant);
                    if ($roundScore !== null) {
                        $memberNames = [];
                        $memberGenders = [];

                        if ($contestant->is_pair && $contestant->members->isNotEmpty()) {
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
                    // Separate by gender for pair pageants
                    $maleScores = array_filter($contestantScores, fn ($c) => ($c['gender'] ?? '') === 'male');
                    $femaleScores = array_filter($contestantScores, fn ($c) => ($c['gender'] ?? '') === 'female');

                    $maleWinners = [];
                    $femaleWinners = [];

                    // Get male winners
                    if (!empty($maleScores)) {
                        usort($maleScores, fn ($a, $b) => $b['score'] <=> $a['score']);
                        $topMaleScore = $maleScores[0]['score'];
                        $maleWinners = array_values(array_filter($maleScores, function ($row) use ($topMaleScore) {
                            return abs($row['score'] - $topMaleScore) < 0.00001;
                        }));
                    }

                    // Get female winners
                    if (!empty($femaleScores)) {
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
                        'winners' => array_merge($maleWinners, $femaleWinners), // Combined for compatibility
                    ];
                } else {
                    // Standard single winner logic
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
     * Calculate final scores for all contestants in a pageant
     */
    public function calculatePageantFinalScores(Pageant $pageant, bool $useCache = true): array
    {
        try {
            $cacheKey = "pageant_final_scores_{$pageant->id}";

            if ($useCache && Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            $contestants = [];

            foreach ($pageant->contestants as $contestant) {
                $roundScores = [];
                $totalWeightedScore = 0;
                $totalRoundWeight = 0;

                foreach ($pageant->rounds as $round) {
                    $roundScore = $this->calculateContestantRoundScore($contestant, $round, $pageant);

                    if ($roundScore !== null) {
                        $roundScores[$round->name] = $roundScore;

                        $roundWeight = $round->weight ?? 1;

                        // Safety check for round weight
                        if ($roundWeight <= 0) {
                            Log::warning("Invalid round weight for round {$round->id}: {$roundWeight}. Using weight of 1.", [
                                'pageant_id' => $pageant->id,
                                'round_id' => $round->id,
                                'contestant_id' => $contestant->id,
                            ]);
                            $roundWeight = 1;
                        }

                        $totalWeightedScore += $roundScore * $roundWeight;
                        $totalRoundWeight += $roundWeight;
                    }
                }

                $finalScore = $totalRoundWeight > 0 ? $totalWeightedScore / $totalRoundWeight : 0;

                $contestants[] = [
                    'id' => $contestant->id,
                    'number' => $contestant->number,
                    'name' => $contestant->name,
                    'gender' => $contestant->gender,
                    'region' => $contestant->origin,
                    'image' => $contestant->photo ?? '/images/placeholders/contestant.jpg',
                    'scores' => $roundScores,
                    'finalScore' => round($finalScore, 2),
                ];
            }

            // For pair pageants, separate rankings by gender
            if ($pageant->isPairsOnly() || $pageant->allowsBothTypes()) {
                $maleContestants = array_filter($contestants, fn ($c) => ($c['gender'] ?? '') === 'male');
                $femaleContestants = array_filter($contestants, fn ($c) => ($c['gender'] ?? '') === 'female');

                // Sort male contestants
                usort($maleContestants, fn ($a, $b) => $b['finalScore'] <=> $a['finalScore']);
                foreach ($maleContestants as $index => &$contestant) {
                    $contestant['rank'] = $index + 1;
                    $contestant['genderRank'] = $index + 1;
                }

                // Sort female contestants
                usort($femaleContestants, fn ($a, $b) => $b['finalScore'] <=> $a['finalScore']);
                foreach ($femaleContestants as $index => &$contestant) {
                    $contestant['rank'] = $index + 1;
                    $contestant['genderRank'] = $index + 1;
                }

                // Combine and maintain separate rankings
                $result = array_merge($maleContestants, $femaleContestants);
            } else {
                // Sort by final score descending and add ranks (standard ranking)
                usort($contestants, fn ($a, $b) => $b['finalScore'] <=> $a['finalScore']);
                foreach ($contestants as $index => &$contestant) {
                    $contestant['rank'] = $index + 1;
                }
                $result = $contestants;
            }

            // Cache for 30 minutes
            Cache::put($cacheKey, $result, now()->addMinutes(30));

            // Broadcast rankings update
            RankingsUpdated::dispatch($pageant->id, $result);

            return $result;

        } catch (\Exception $e) {
            Log::error('Error calculating pageant final scores: '.$e->getMessage(), [
                'pageant_id' => $pageant->id,
                'trace' => $e->getTraceAsString(),
            ]);

            // Return empty array on error to prevent system crashes
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

            // Get all judges for this pageant
            foreach ($pageant->judges as $judge) {
                $judgeScore = $this->calculateJudgeContestantScore($judge->id, $contestant->id, $round->id, $pageant->id);

                if ($judgeScore !== null) {
                    $judgeAverages[] = $judgeScore;
                }
            }

            // Average across all judges for this round
            $roundScore = null;
            if (! empty($judgeAverages)) {
                $roundScore = array_sum($judgeAverages) / count($judgeAverages);
            }

            // Cache for 15 minutes
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

                // Safety checks for weight
                if ($weight <= 0) {
                    Log::warning("Invalid weight for criteria {$score->criteria->id}: {$weight}. Using weight of 1.", [
                        'judge_id' => $judgeId,
                        'contestant_id' => $contestantId,
                        'round_id' => $roundId,
                        'pageant_id' => $pageantId,
                        'criteria_id' => $score->criteria->id,
                    ]);
                    $weight = 1;
                }

                // Safety check for score value
                if (! is_numeric($score->score)) {
                    Log::error("Non-numeric score found: {$score->score} for criteria {$score->criteria->id}", [
                        'judge_id' => $judgeId,
                        'contestant_id' => $contestantId,
                        'round_id' => $roundId,
                        'pageant_id' => $pageantId,
                        'score_id' => $score->id,
                    ]);

                    continue;
                }

                $criteriaWeightedSum += $score->score * $weight;
                $criteriaWeightTotal += $weight;
            }

            if ($criteriaWeightTotal <= 0) {
                Log::warning("Total criteria weight is zero or negative for judge {$judgeId}, contestant {$contestantId}, round {$roundId}", [
                    'total_weight' => $criteriaWeightTotal,
                    'pageant_id' => $pageantId,
                ]);

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
                Log::warning('Contestant not found in pageant', [
                    'pageant_id' => $pageantId,
                    'contestant_id' => $contestantId,
                ]);

                return null;
            }

            $totalWeightedScore = 0;
            $totalRoundWeight = 0;

            foreach ($pageant->rounds as $round) {
                $roundScore = $this->calculateContestantRoundScore($contestant, $round, $pageant);

                if ($roundScore !== null) {
                    $roundWeight = $round->weight ?? 1;

                    // Safety check for round weight
                    if ($roundWeight <= 0) {
                        Log::warning("Invalid round weight for round {$round->id}: {$roundWeight}. Using weight of 1.", [
                            'pageant_id' => $pageantId,
                            'round_id' => $round->id,
                            'contestant_id' => $contestantId,
                        ]);
                        $roundWeight = 1;
                    }

                    $totalWeightedScore += $roundScore * $roundWeight;
                    $totalRoundWeight += $roundWeight;
                }
            }

            $finalScore = $totalRoundWeight > 0 ? $totalWeightedScore / $totalRoundWeight : null;

            // Cache for 30 minutes
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

        // Also invalidate round-specific caches
        $pageant = Pageant::with('rounds')->find($pageantId);
        if ($pageant) {
            foreach ($pageant->rounds as $round) {
                Cache::forget("contestant_round_score_{$contestantId}_{$round->id}");
            }
        }
    }

    /**
     * Invalidate all cache entries for a pageant
     */
    public function invalidatePageantCache(int $pageantId): void
    {
        Cache::forget("pageant_final_scores_{$pageantId}");

        // Get all contestants and invalidate their caches
        $pageant = Pageant::with(['contestants', 'rounds'])->find($pageantId);
        if ($pageant) {
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
