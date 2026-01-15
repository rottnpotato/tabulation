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
     * Calculate RANK.EQ / Competition ranking - handles ties by giving same (minimum) rank.
     * This is used for per-judge rankings in rank_sum method.
     * Ties get the same rank (e.g., two tied for 2nd both get rank 2).
     *
     * @param  float  $value  The value to rank
     * @param  array  $allValues  All values to rank against
     * @param  string  $direction  'desc' for highest=1, 'asc' for lowest=1
     * @return int The rank (always integer, ties get same rank)
     */
    public function calculateRankMin(float $value, array $allValues, string $direction = 'desc'): int
    {
        $betterCount = 0;

        foreach ($allValues as $otherValue) {
            if ($direction === 'desc') {
                // Higher is better
                if ($otherValue > $value) {
                    $betterCount++;
                }
            } else {
                // Lower is better
                if ($otherValue < $value) {
                    $betterCount++;
                }
            }
        }

        // Competition ranking: rank = number of better values + 1
        return $betterCount + 1;
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

        // Sort contestants with tie-breaker
        usort($contestants, function ($a, $b) use ($scoreField, $direction) {
            $aVal = $a[$scoreField] ?? 0;
            $bVal = $b[$scoreField] ?? 0;

            // Primary comparison by score field
            $primaryCompare = $direction === 'desc'
                ? $bVal <=> $aVal
                : $aVal <=> $bVal;

            // If tied, use tie-breaker: for rank_sum (asc), higher finalScore wins
            // For score_average (desc), lower totalRankSum wins
            if ($primaryCompare === 0) {
                if ($direction === 'asc') {
                    // rank_sum method: tie-break by higher score (finalScore or displayTotal)
                    $aScore = $a['finalScore'] ?? $a['displayTotal'] ?? 0;
                    $bScore = $b['finalScore'] ?? $b['displayTotal'] ?? 0;

                    return $bScore <=> $aScore; // Higher score wins
                } else {
                    // score_average method: tie-break by lower rank sum
                    $aRankSum = $a['totalRankSum'] ?? 0;
                    $bRankSum = $b['totalRankSum'] ?? 0;

                    return $aRankSum <=> $bRankSum; // Lower rank sum wins
                }
            }

            return $primaryCompare;
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
    public function applyGenderSeparatedRanking(
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
     *
     * @param  string  $finalScoreMode  'fresh' uses minimum ranking (ties get same rank), 'inherit' uses average ranking
     */
    private function getJudgeRanksForRound(Contestant $contestant, Round $round, Pageant $pageant, string $tieHandling, string $finalScoreMode = 'fresh'): array
    {
        $scores = [];
        $ranks = [];
        $judgeDetails = [];

        foreach ($pageant->judges as $judge) {
            $judgeScore = $this->calculateJudgeContestantScore($judge->id, $contestant->id, $round->id, $pageant->id);

            if ($judgeScore !== null) {
                $allJudgeScores = $this->getAllJudgeScoresForRound($judge->id, $round->id, $pageant);

                // Fresh mode: use minimum ranking (ties get same rank)
                // Inherit mode: use average ranking (ties get averaged rank)
                if ($finalScoreMode === 'fresh') {
                    $rank = $this->calculateRankMin($judgeScore, $allJudgeScores, 'desc');
                } else {
                    $rank = $this->calculateRankAvg($judgeScore, $allJudgeScores, 'desc');
                }

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
     * Calculate ordinal rankings for contestants in a specific round.
     * This implements the Final Ballot system used in major pageants.
     *
     * Flow:
     * 1. Each judge gives a score for each contestant in the round
     * 2. Scores are converted to ranks per judge (1 = best, no ties allowed conceptually)
     * 3. Check for "Majority 1s" - if a contestant has > 50% of judges ranking them #1, they win
     * 4. If no majority, use "Sum of Ranks" (Golf System) - lowest total wins
     *
     * @param  array  $contestants  Array of contestant data with their judge data for this round
     * @param  int  $judgeCount  Total number of judges who scored
     * @return array Contestants sorted by ordinal ranking with ordinalData field
     */
    private function applyOrdinalRanking(array $contestants, int $judgeCount): array
    {
        if (empty($contestants) || $judgeCount <= 0) {
            return $contestants;
        }

        // Calculate majority threshold (N/2 + 1 for odd, N/2 + 1 for even)
        $majorityThreshold = (int) floor($judgeCount / 2) + 1;

        // Calculate ordinal data for each contestant
        foreach ($contestants as &$contestant) {
            $ranks = $contestant['ordinalRanks'] ?? [];

            // Count "Rank 1" votes
            $rank1Count = 0;
            foreach ($ranks as $rank) {
                if ($rank == 1 || (is_float($rank) && $rank < 1.5)) {
                    $rank1Count++;
                }
            }

            // Sum of all ranks
            $rankSum = array_sum($ranks);

            $contestant['ordinalData'] = [
                'ranks' => $ranks,
                'rank1Count' => $rank1Count,
                'rankSum' => round($rankSum, 2),
                'hasMajority' => $rank1Count >= $majorityThreshold,
                'majorityThreshold' => $majorityThreshold,
                'judgeCount' => $judgeCount,
            ];
        }
        unset($contestant);

        // Sort contestants by ordinal rules
        usort($contestants, function ($a, $b) {
            $aData = $a['ordinalData'];
            $bData = $b['ordinalData'];

            // Primary: Majority wins (if one has majority and other doesn't)
            if ($aData['hasMajority'] && ! $bData['hasMajority']) {
                return -1;
            }
            if (! $aData['hasMajority'] && $bData['hasMajority']) {
                return 1;
            }

            // Both have majority or neither has majority
            // Secondary: More #1 votes is better
            if ($aData['rank1Count'] != $bData['rank1Count']) {
                return $bData['rank1Count'] <=> $aData['rank1Count'];
            }

            // Tertiary: Lower rank sum (golf system) is better
            return $aData['rankSum'] <=> $bData['rankSum'];
        });

        // Assign final ranks
        foreach ($contestants as $index => &$contestant) {
            $contestant['rank'] = $index + 1;
            $contestant['ordinalData']['finalRank'] = $index + 1;
        }

        return $contestants;
    }

    /**
     * Apply ordinal ranking with gender separation for pair pageants.
     */
    private function applyGenderSeparatedOrdinalRanking(
        array $contestants,
        Pageant $pageant,
        int $judgeCount
    ): array {
        if ($pageant->isPairsOnly() || $pageant->allowsBothTypes()) {
            $maleContestants = array_filter($contestants, fn ($c) => ($c['gender'] ?? '') === 'male');
            $femaleContestants = array_filter($contestants, fn ($c) => ($c['gender'] ?? '') === 'female');

            $maleContestants = $this->applyOrdinalRanking(array_values($maleContestants), $judgeCount);
            $femaleContestants = $this->applyOrdinalRanking(array_values($femaleContestants), $judgeCount);

            foreach ($maleContestants as &$c) {
                $c['genderRank'] = $c['rank'];
            }
            foreach ($femaleContestants as &$c) {
                $c['genderRank'] = $c['rank'];
            }

            return array_merge($maleContestants, $femaleContestants);
        }

        return $this->applyOrdinalRanking($contestants, $judgeCount);
    }

    /**
     * Calculate scores using Ordinal Ranking method (Final Ballot System).
     *
     * This is the pageant-style ranking used in Miss Universe, Miss World, etc.
     * For each round:
     * 1. Calculate each judge's score for each contestant
     * 2. Convert scores to ranks per judge (highest score = rank 1)
     * 3. Apply ordinal determination: Majority 1s wins, else lowest sum of ranks wins
     *
     * @param  Pageant  $pageant  The pageant to calculate for
     * @param  Round|null  $targetRound  Specific round to calculate (for round-by-round progression)
     * @param  bool  $useCache  Whether to use caching
     * @return array<int, array<string, mixed>>
     */
    public function calculateWithOrdinal(Pageant $pageant, ?Round $targetRound = null, bool $useCache = true): array
    {
        try {
            $cacheKey = $targetRound
                ? "pageant_ordinal_scores_{$pageant->id}_{$targetRound->id}"
                : "pageant_ordinal_scores_{$pageant->id}_all";

            if ($useCache && Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            // If no target round, use the last round
            if (! $targetRound) {
                $targetRound = $pageant->rounds->sortByDesc('display_order')->first();
                if (! $targetRound) {
                    return [];
                }
            }

            $contestants = [];
            $judgeCount = $pageant->judges->count();

            // Get all judges' scores for this round to calculate ranks
            $allJudgeScores = [];
            foreach ($pageant->judges as $judge) {
                $judgeScores = [];
                foreach ($pageant->contestants as $contestant) {
                    $score = $this->calculateJudgeContestantScore($judge->id, $contestant->id, $targetRound->id, $pageant->id);
                    if ($score !== null) {
                        $judgeScores[$contestant->id] = $score;
                    }
                }
                if (! empty($judgeScores)) {
                    $allJudgeScores[$judge->id] = $judgeScores;
                }
            }

            // For each contestant, calculate their ordinal ranks from each judge
            foreach ($pageant->contestants as $contestant) {
                $ordinalRanks = [];
                $judgeDetails = [];
                $avgScore = 0;
                $scoreCount = 0;

                foreach ($pageant->judges as $judge) {
                    if (! isset($allJudgeScores[$judge->id][$contestant->id])) {
                        continue;
                    }

                    $judgeScore = $allJudgeScores[$judge->id][$contestant->id];
                    $avgScore += $judgeScore;
                    $scoreCount++;

                    // Calculate this contestant's rank among all contestants for this judge
                    // Using forced ranking (no ties) - higher score = lower rank number
                    $rank = 1;
                    foreach ($allJudgeScores[$judge->id] as $otherId => $otherScore) {
                        if ($otherId != $contestant->id && $otherScore > $judgeScore) {
                            $rank++;
                        }
                    }

                    $ordinalRanks[] = $rank;
                    $judgeDetails[] = [
                        'judge_id' => $judge->id,
                        'judge_name' => $judge->name ?? "Judge {$judge->id}",
                        'score' => round($judgeScore, 2),
                        'rank' => $rank,
                    ];
                }

                $avgScore = $scoreCount > 0 ? $avgScore / $scoreCount : 0;

                $memberNames = [];
                $memberGenders = [];
                if ($contestant->is_pair && $contestant->members && $contestant->members->isNotEmpty()) {
                    foreach ($contestant->members as $member) {
                        $memberNames[] = $member->name;
                        $memberGenders[] = $member->gender;
                    }
                }

                // Get scores from previous rounds for display
                $roundScores = [];
                foreach ($pageant->rounds->sortBy('display_order') as $round) {
                    if ($round->display_order <= $targetRound->display_order) {
                        $roundScore = $this->calculateContestantRoundScore($contestant, $round, $pageant);
                        if ($roundScore !== null) {
                            $roundScores[$round->name] = round($roundScore, 2);
                        }
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
                    'ordinalRanks' => $ordinalRanks,
                    'judgeRanks' => [$targetRound->name => ['details' => $judgeDetails, 'ranks' => $ordinalRanks]],
                    'finalScore' => round($avgScore, 2),
                    'totalScore' => round($avgScore, 2),
                    'totalRankSum' => array_sum($ordinalRanks),
                ];
            }

            // Filter out contestants with no scores
            $contestants = array_filter($contestants, fn ($c) => ! empty($c['ordinalRanks']));

            // Apply ordinal ranking
            $result = $this->applyGenderSeparatedOrdinalRanking(
                array_values($contestants),
                $pageant,
                $judgeCount
            );

            Cache::put($cacheKey, $result, now()->addMinutes(30));

            return $result;

        } catch (\Exception $e) {
            Log::error('Error calculating ordinal scores: '.$e->getMessage(), [
                'pageant_id' => $pageant->id,
                'round_id' => $targetRound?->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return [];
        }
    }

    /**
     * Calculate ordinal ranking for a specific round, filtering by previous round's top N.
     *
     * This is the key method for round-by-round ordinal progression:
     * 1. Get contestants who qualified from the previous round (based on top_n_proceed)
     * 2. Calculate ordinal ranking for these contestants in the current round
     * 3. Clean slate - previous round scores don't carry over, only determine eligibility
     *
     * @param  Pageant  $pageant  The pageant
     * @param  Round  $targetRound  The round to calculate
     * @param  bool  $useCache  Whether to use caching
     * @return array Ordinal-ranked contestants for this round
     */
    public function calculateOrdinalForRound(Pageant $pageant, Round $targetRound, bool $useCache = true): array
    {
        try {
            $cacheKey = "pageant_ordinal_round_{$pageant->id}_{$targetRound->id}";

            if ($useCache && Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            // Get the previous round to determine who qualifies
            $previousRound = $pageant->rounds
                ->where('display_order', '<', $targetRound->display_order)
                ->sortByDesc('display_order')
                ->first();

            $eligibleContestantIds = null;

            // If there's a previous round with top_n_proceed, get qualifying contestants
            if ($previousRound && $previousRound->top_n_proceed > 0) {
                // Calculate ordinal for previous round to determine who advances
                $previousResults = $this->calculateWithOrdinal($pageant, $previousRound, $useCache);
                $eligibleContestantIds = $this->getTopNFromOrdinalResults(
                    $previousResults,
                    $previousRound->top_n_proceed,
                    $pageant
                );
            }

            // Calculate ordinal for target round
            $results = $this->calculateWithOrdinal($pageant, $targetRound, false);

            // Filter to only eligible contestants if we have eligibility criteria
            if ($eligibleContestantIds !== null) {
                $results = array_filter($results, fn ($c) => in_array($c['id'], $eligibleContestantIds));
                $results = array_values($results);

                // Re-apply ordinal ranking after filtering
                $judgeCount = $pageant->judges->count();
                $results = $this->applyGenderSeparatedOrdinalRanking($results, $pageant, $judgeCount);
            }

            Cache::put($cacheKey, $results, now()->addMinutes(30));

            return $results;

        } catch (\Exception $e) {
            Log::error('Error calculating ordinal for round: '.$e->getMessage(), [
                'pageant_id' => $pageant->id,
                'round_id' => $targetRound->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return [];
        }
    }

    /**
     * Get top N contestant IDs from ordinal results, respecting gender separation.
     */
    private function getTopNFromOrdinalResults(array $results, int $topN, Pageant $pageant): array
    {
        if (empty($results)) {
            return [];
        }

        $advancingIds = [];

        if ($pageant->isPairsOnly() || $pageant->allowsBothTypes()) {
            // Separate by gender and get top N from each
            $maleResults = array_filter($results, fn ($c) => ($c['gender'] ?? '') === 'male');
            $femaleResults = array_filter($results, fn ($c) => ($c['gender'] ?? '') === 'female');

            // Sort by rank and take top N from each
            usort($maleResults, fn ($a, $b) => ($a['rank'] ?? 999) <=> ($b['rank'] ?? 999));
            usort($femaleResults, fn ($a, $b) => ($a['rank'] ?? 999) <=> ($b['rank'] ?? 999));

            foreach (array_slice($maleResults, 0, $topN) as $contestant) {
                $advancingIds[] = $contestant['id'];
            }
            foreach (array_slice($femaleResults, 0, $topN) as $contestant) {
                $advancingIds[] = $contestant['id'];
            }
        } else {
            // Sort by rank and take top N
            usort($results, fn ($a, $b) => ($a['rank'] ?? 999) <=> ($b['rank'] ?? 999));
            foreach (array_slice($results, 0, $topN) as $contestant) {
                $advancingIds[] = $contestant['id'];
            }
        }

        return $advancingIds;
    }

    /**
     * Calculate comprehensive scores using rank-sum method (Excel-style).
     *
     * Excel Formula Implementation:
     * 1. For each round, calculate each contestant's rank sum (sum of judge ranks)
     * 2. Rank contestants within each round by their rank sum (lower = better)
     * 3. Apply weight to the RANK: (roundRank / 100) * weightPercent
     * 4. Average the weighted ranks across all rounds
     * 5. Final ranking by weighted average (ascending - lower is better)
     *
     * @return array<int, array<string, mixed>>
     */
    public function calculateWithRankSum(Pageant $pageant, ?string $stage = null, bool $useCache = true): array
    {
        try {
            $finalScoreMode = $pageant->final_score_mode ?? 'fresh';

            $cacheKey = $stage
                ? "pageant_ranksum_scores_{$pageant->id}_{$stage}_{$finalScoreMode}"
                : "pageant_ranksum_scores_{$pageant->id}_all_{$finalScoreMode}";

            if ($useCache && Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            $rounds = $stage
                ? $pageant->rounds->filter(fn ($r) => ($r->type ?? null) === $stage)
                : $pageant->rounds;

            if ($rounds->isEmpty()) {
                return [];
            }

            $tieHandling = $pageant->tie_handling ?? 'average';

            // Step 1: Calculate rank sum per round for ALL contestants first
            $roundRankSums = [];
            foreach ($rounds as $round) {
                $roundRankSums[$round->id] = [];
                foreach ($pageant->contestants as $contestant) {
                    $roundJudgeData = $this->getJudgeRanksForRound($contestant, $round, $pageant, $tieHandling, $finalScoreMode);
                    if (! empty($roundJudgeData['ranks'])) {
                        $roundRankSums[$round->id][$contestant->id] = array_sum($roundJudgeData['ranks']);
                    }
                }
            }

            // Step 2: Calculate per-round RANK for each contestant (rank by rank sum within round)
            $roundRanks = [];
            foreach ($rounds as $round) {
                $roundRanks[$round->id] = [];
                $allRankSumsInRound = array_values($roundRankSums[$round->id]);

                foreach ($roundRankSums[$round->id] as $contestantId => $rankSum) {
                    // Use RANK.AVG - lower rank sum gets better (lower) rank
                    $roundRanks[$round->id][$contestantId] = $this->calculateRankAvg($rankSum, $allRankSumsInRound, 'asc');
                }
            }

            // Step 3: Build contestant data with weighted ranks (Excel formula)
            $contestants = [];
            foreach ($pageant->contestants as $contestant) {
                $roundScores = [];
                $judgeRanks = [];
                $totalRankSum = 0;
                $weightedRankSum = 0;
                $totalWeight = 0;
                $perRoundRanks = [];

                foreach ($rounds as $round) {
                    $roundWeight = $round->weight ?? 1;
                    if ($roundWeight <= 0) {
                        $roundWeight = 1;
                    }

                    $roundJudgeData = $this->getJudgeRanksForRound($contestant, $round, $pageant, $tieHandling, $finalScoreMode);

                    if (! empty($roundJudgeData['scores'])) {
                        $roundAvgScore = array_sum($roundJudgeData['scores']) / count($roundJudgeData['scores']);
                        $roundScores[$round->name] = round($roundAvgScore, 2);

                        $roundRankSum = array_sum($roundJudgeData['ranks']);
                        $totalRankSum += $roundRankSum;

                        $judgeRanks[$round->name] = $roundJudgeData;

                        // Get this contestant's rank within this round
                        $contestantRoundRank = $roundRanks[$round->id][$contestant->id] ?? 0;
                        $perRoundRanks[$round->name] = $contestantRoundRank;

                        // Excel formula: (rank / 100) * weight%
                        // e.g., rank 3 with 25% weight = (3/100)*25 = 0.75
                        $weightedRank = ($contestantRoundRank / 100) * $roundWeight;
                        $weightedRankSum += $weightedRank;
                        $totalWeight += $roundWeight;
                    }
                }

                // Excel formula: average of weighted ranks = sum / count (or sum / 3 for 3 rounds)
                $roundCount = count(array_filter($perRoundRanks, fn ($r) => $r > 0));
                $weightedRankAvg = $roundCount > 0 ? $weightedRankSum / $roundCount : 0;

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
                    'perRoundRanks' => $perRoundRanks,
                    'totalRankSum' => round($totalRankSum, 2),
                    'weightedRankAvg' => round($weightedRankAvg, 4),
                    'finalScore' => round($totalRankSum, 2),
                    'totalScore' => round($totalRankSum, 2),
                ];
            }

            // Step 4: Apply final ranking by total rank sum (lower is better)
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
        $rankingMethod = $pageant->ranking_method ?? 'score_average';

        if ($rankingMethod === 'rank_sum') {
            return $this->calculateWithRankSum($pageant, $stage, $useCache);
        }

        // For ordinal method, get the last round of this stage and calculate ordinal for it
        if ($rankingMethod === 'ordinal') {
            $stageRounds = $pageant->rounds->filter(fn ($r) => ($r->type ?? null) === $stage);
            $lastStageRound = $stageRounds->sortByDesc('display_order')->first();
            if ($lastStageRound) {
                return $this->calculateOrdinalForRound($pageant, $lastStageRound, $useCache);
            }

            return [];
        }

        try {
            $cacheKey = "pageant_stage_scores_{$pageant->id}_{$stage}";

            if ($useCache && Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            $contestants = [];
            $tieHandling = $pageant->tie_handling ?? 'average';
            $finalScoreMode = $pageant->final_score_mode ?? 'fresh';

            $stageRounds = $pageant->rounds->filter(function ($round) use ($stage) {
                return ($round->type ?? null) === $stage;
            });

            foreach ($pageant->contestants as $contestant) {
                $roundScores = [];
                $judgeRanks = [];
                $totalWeightedScore = 0;
                $totalRoundWeight = 0;
                $totalRankSum = 0;

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

                        $roundJudgeData = $this->getJudgeRanksForRound($contestant, $round, $pageant, $tieHandling, $finalScoreMode);
                        $judgeRanks[$round->name] = $roundJudgeData;

                        // Always calculate rank sum for display purposes
                        if (isset($roundJudgeData['ranks']) && is_array($roundJudgeData['ranks'])) {
                            $roundRankSum = array_sum($roundJudgeData['ranks']);
                            $totalRankSum += $roundRankSum;
                        }
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
                    'totalRankSum' => round($totalRankSum, 2),
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

        // Sort contestants by rank to ensure we process in correct order
        usort($contestants, function ($a, $b) {
            $rankA = $a['rank'] ?? PHP_INT_MAX;
            $rankB = $b['rank'] ?? PHP_INT_MAX;

            return $rankA <=> $rankB;
        });

        // Track the cutoff rank - we need to include all contestants with this rank or better
        $cutoffRank = null;
        $count = 0;

        foreach ($contestants as $contestant) {
            $rank = $contestant['rank'] ?? PHP_INT_MAX;

            // If we haven't reached topN yet, or this contestant has the same rank as the cutoff
            if ($count < $topN) {
                $advancingIds[] = $contestant['id'];
                $cutoffRank = $rank;
                $count++;
            } elseif ($rank == $cutoffRank) {
                // Include tied contestants at the cutoff position (handles average tie ranks like 4.5)
                $advancingIds[] = $contestant['id'];
            } else {
                // We've exceeded topN and this contestant has a worse rank
                break;
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

            // For ordinal method, use ordinal-specific filtering
            if ($rankingMethod === 'ordinal') {
                return $this->getTopNFromOrdinalResults($stageResults, $topN, $pageant);
            }

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
     * Get all contestants with all their scores across all rounds.
     * This method does NOT apply ranking method filtering - it returns ALL contestants.
     * Used for the Overall Tally view in ordinal ranking to show non-finalists too.
     */
    public function calculateAllContestantsWithScores(Pageant $pageant, bool $useCache = true): array
    {
        try {
            $cacheKey = "pageant_all_contestants_scores_{$pageant->id}";

            if ($useCache && Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            $contestants = [];
            $tieHandling = $pageant->tie_handling ?? 'average';
            $finalScoreMode = $pageant->final_score_mode ?? 'fresh';

            foreach ($pageant->contestants as $contestant) {
                $roundScores = [];
                $judgeRanks = [];
                $totalWeightedScore = 0;
                $totalRoundWeight = 0;
                $totalRankSum = 0;

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

                        $roundJudgeData = $this->getJudgeRanksForRound($contestant, $round, $pageant, $tieHandling, $finalScoreMode);
                        $judgeRanks[$round->name] = $roundJudgeData;

                        // Calculate rank sum for display purposes
                        if (isset($roundJudgeData['ranks']) && is_array($roundJudgeData['ranks'])) {
                            $roundRankSum = array_sum($roundJudgeData['ranks']);
                            $totalRankSum += $roundRankSum;
                        }
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
                    'totalRankSum' => round($totalRankSum, 2),
                    'rank' => 0, // Will be set by caller
                ];
            }

            if ($useCache) {
                Cache::put($cacheKey, $contestants, now()->addMinutes(30));
            }

            return $contestants;

        } catch (\Exception $e) {
            Log::error('Error calculating all contestants with scores: '.$e->getMessage(), [
                'pageant_id' => $pageant->id,
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
        $rankingMethod = $pageant->ranking_method ?? 'score_average';

        if ($rankingMethod === 'rank_sum') {
            return $this->calculateWithRankSum($pageant, null, $useCache);
        }

        // For ordinal method, use the last round for final ranking
        if ($rankingMethod === 'ordinal') {
            $lastRound = $pageant->rounds->sortByDesc('display_order')->first();
            if ($lastRound) {
                return $this->calculateOrdinalForRound($pageant, $lastRound, $useCache);
            }

            return [];
        }

        try {
            $cacheKey = "pageant_final_scores_{$pageant->id}";

            if ($useCache && Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            $contestants = [];
            $tieHandling = $pageant->tie_handling ?? 'average';
            $finalScoreMode = $pageant->final_score_mode ?? 'fresh';

            foreach ($pageant->contestants as $contestant) {
                $roundScores = [];
                $judgeRanks = [];
                $totalWeightedScore = 0;
                $totalRoundWeight = 0;
                $totalRankSum = 0;

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

                        $roundJudgeData = $this->getJudgeRanksForRound($contestant, $round, $pageant, $tieHandling, $finalScoreMode);
                        $judgeRanks[$round->name] = $roundJudgeData;

                        // Always calculate rank sum for display purposes
                        if (isset($roundJudgeData['ranks']) && is_array($roundJudgeData['ranks'])) {
                            $roundRankSum = array_sum($roundJudgeData['ranks']);
                            $totalRankSum += $roundRankSum;
                        }
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
                    'totalRankSum' => round($totalRankSum, 2),
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
        Cache::forget("pageant_ordinal_scores_{$pageantId}_all");

        $pageant = Pageant::with(['contestants', 'rounds'])->find($pageantId);
        if ($pageant) {
            $stageTypes = $pageant->rounds->pluck('type')->unique()->filter();
            foreach ($stageTypes as $stageType) {
                Cache::forget("pageant_stage_scores_{$pageantId}_{$stageType}");
                Cache::forget("pageant_ranksum_scores_{$pageantId}_{$stageType}");
            }

            // Invalidate ordinal cache for each round
            foreach ($pageant->rounds as $round) {
                Cache::forget("pageant_ordinal_scores_{$pageantId}_{$round->id}");
                Cache::forget("pageant_ordinal_round_{$pageantId}_{$round->id}");
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

    /**
     * Calculate scores for a specific round view (unified calculation).
     *
     * Rules:
     * 1. Same type rounds are accumulated together
     * 2. Different types are calculated independently (no cross-type accumulation)
     * 3. Final round type always starts fresh (no accumulation from previous stages)
     *
     * @param  Round  $targetRound  The round to calculate results for
     * @param  bool  $useCache  Whether to use caching
     * @return array Array of contestant results with scores and ranks
     */
    public function calculateRoundViewScores(Pageant $pageant, Round $targetRound, bool $useCache = true): array
    {
        // For ordinal method, use the ordinal calculation for this specific round
        if (($pageant->ranking_method ?? 'score_average') === 'ordinal') {
            return $this->calculateOrdinalForRound($pageant, $targetRound, $useCache);
        }

        try {
            $cacheKey = "round_view_scores_{$pageant->id}_{$targetRound->id}";

            if ($useCache && Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            $targetType = strtolower($targetRound->type ?? 'preliminary');

            // Check if final round should inherit scores from previous stages
            $finalScoreMode = $pageant->final_score_mode ?? 'fresh';
            $finalScoreInheritance = $pageant->final_score_inheritance ?? [];
            $shouldInheritScores = $targetType === 'final' && $finalScoreMode === 'inherit' && ! empty($finalScoreInheritance);

            // For Final round type, check if we should inherit or start fresh
            if ($targetType === 'final') {
                if ($shouldInheritScores) {
                    // Inherit mode: include all rounds from stages configured in inheritance
                    $roundsToUse = $pageant->rounds
                        ->sortBy('display_order')
                        ->filter(function ($r) use ($targetRound, $finalScoreInheritance) {
                            $roundType = strtolower($r->type ?? '');

                            // Include this round if its type is in the inheritance config AND display_order <= target
                            return isset($finalScoreInheritance[$roundType]) && $r->display_order <= $targetRound->display_order;
                        });
                    $roundsForDisplay = $pageant->rounds
                        ->sortBy('display_order')
                        ->filter(function ($r) use ($targetRound) {
                            return $r->display_order <= $targetRound->display_order;
                        });
                } else {
                    // Fresh mode (default): only use the final round itself
                    $roundsToUse = collect([$targetRound]);
                    $roundsForDisplay = $pageant->rounds
                        ->sortBy('display_order')
                        ->filter(function ($r) use ($targetRound) {
                            return $r->display_order <= $targetRound->display_order;
                        });
                }
            } else {
                // For non-final rounds, only accumulate rounds of the SAME type
                $roundsToUse = $pageant->rounds
                    ->sortBy('display_order')
                    ->filter(function ($r) use ($targetRound, $targetType) {
                        $roundType = strtolower($r->type ?? 'preliminary');

                        return $roundType === $targetType && $r->display_order <= $targetRound->display_order;
                    });
                $roundsForDisplay = $roundsToUse; // Same for non-final rounds
            }

            // Calculate scores using only rounds of the same type (or inherited rounds)
            $contestants = [];
            $tieHandling = $pageant->tie_handling ?? 'average';

            foreach ($pageant->contestants as $contestant) {
                $roundScores = [];
                $judgeRanks = [];
                $totalWeightedScore = 0;
                $totalRoundWeight = 0;
                $totalRankSum = 0;

                // Get scores for ALL rounds up to target round (for display)
                foreach ($roundsForDisplay as $round) {
                    $roundScore = $this->calculateContestantRoundScore($contestant, $round, $pageant);

                    if ($roundScore !== null) {
                        $roundScores[$round->name] = round($roundScore, 2);
                    }
                }

                // Calculate weighted score and rank sum
                if ($shouldInheritScores) {
                    // Inheritance mode: calculate weighted average per stage type, then combine using inheritance percentages
                    $stageScores = [];
                    $stageWeights = [];

                    foreach ($roundsToUse as $round) {
                        $roundType = strtolower($round->type ?? '');
                        $roundScore = $this->calculateContestantRoundScore($contestant, $round, $pageant);

                        if ($roundScore !== null) {
                            if (! isset($stageScores[$roundType])) {
                                $stageScores[$roundType] = 0;
                                $stageWeights[$roundType] = 0;
                            }
                            $roundWeight = $round->weight ?? 1;
                            if ($roundWeight <= 0) {
                                $roundWeight = 1;
                            }
                            $stageScores[$roundType] += $roundScore * $roundWeight;
                            $stageWeights[$roundType] += $roundWeight;
                        }
                    }

                    // Apply inheritance percentages
                    foreach ($stageScores as $stageType => $weightedSum) {
                        if ($stageWeights[$stageType] > 0) {
                            $stageAverage = $weightedSum / $stageWeights[$stageType];
                            $inheritancePercentage = ($finalScoreInheritance[$stageType] ?? 0) / 100;
                            $totalWeightedScore += $stageAverage * $inheritancePercentage;
                            $totalRoundWeight += $inheritancePercentage;
                        }
                    }
                } else {
                    // Standard calculation (fresh mode or non-final rounds)
                    foreach ($roundsToUse as $round) {
                        $roundScore = $this->calculateContestantRoundScore($contestant, $round, $pageant);

                        if ($roundScore !== null) {
                            $roundWeight = $round->weight ?? 1;
                            if ($roundWeight <= 0) {
                                $roundWeight = 1;
                            }
                            $totalWeightedScore += $roundScore * $roundWeight;
                            $totalRoundWeight += $roundWeight;
                        }

                        // Always get judge ranks for display purposes (regardless of ranking method)
                        $roundJudgeData = $this->getJudgeRanksForRound($contestant, $round, $pageant, $tieHandling, $finalScoreMode);
                        if ($pageant->ranking_method === 'rank_sum') {
                            $judgeRanks[$round->name] = $roundJudgeData;
                        }

                        // Always sum up the rank sum for this round (for secondary display)
                        if (isset($roundJudgeData['ranks']) && is_array($roundJudgeData['ranks'])) {
                            $roundRankSum = array_sum($roundJudgeData['ranks']);
                            $totalRankSum += $roundRankSum;
                        }
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
                    'totalScore' => round($finalScore, 2),
                    'finalScore' => round($finalScore, 2),
                    'totalRankSum' => round($totalRankSum, 2),
                ];
            }

            // Sort by score and apply ranking
            $scoreField = $pageant->ranking_method === 'rank_sum' ? 'totalRankSum' : 'finalScore';
            $sortOrder = $pageant->ranking_method === 'rank_sum' ? 'asc' : 'desc';
            $result = $this->applyGenderSeparatedRanking(
                $contestants,
                $pageant,
                $scoreField,
                $sortOrder,
                $tieHandling
            );

            // Apply filtering based on previous STAGE's top_n_proceed
            $previousStageType = $this->getPreviousStageType($pageant, $targetRound);
            if ($previousStageType !== null) {
                $advancedContestants = $this->getAdvancingContestantIds($pageant, $previousStageType);

                if (! empty($advancedContestants)) {
                    $result = array_filter($result, function ($c) use ($advancedContestants) {
                        return in_array($c['id'], $advancedContestants);
                    });
                    $result = array_values($result);

                    // Re-apply ranking after filtering
                    $result = $this->applyGenderSeparatedRanking(
                        $result,
                        $pageant,
                        'finalScore',
                        'desc',
                        $tieHandling
                    );
                }
            }

            Cache::put($cacheKey, $result, now()->addMinutes(30));

            return $result;

        } catch (\Exception $e) {
            Log::error('Error calculating round view scores: '.$e->getMessage(), [
                'pageant_id' => $pageant->id,
                'round_id' => $targetRound->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return [];
        }
    }
}
