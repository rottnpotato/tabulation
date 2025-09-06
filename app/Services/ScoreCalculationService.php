<?php

namespace App\Services;

use App\Events\RankingsUpdated;
use App\Models\Contestant;
use App\Models\Pageant;
use App\Models\Score;
use Illuminate\Support\Facades\Cache;

class ScoreCalculationService
{
    /**
     * Calculate final scores for all contestants in a pageant
     */
    public function calculatePageantFinalScores(Pageant $pageant, bool $useCache = true): array
    {
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
                    $totalWeightedScore += $roundScore * $roundWeight;
                    $totalRoundWeight += $roundWeight;
                }
            }

            $finalScore = $totalRoundWeight > 0 ? $totalWeightedScore / $totalRoundWeight : 0;

            $contestants[] = [
                'id' => $contestant->id,
                'number' => $contestant->number,
                'name' => $contestant->name,
                'region' => $contestant->origin,
                'image' => $contestant->photo ?? '/images/placeholders/contestant.jpg',
                'scores' => $roundScores,
                'finalScore' => round($finalScore, 2),
            ];
        }

        // Sort by final score descending and add ranks
        usort($contestants, fn ($a, $b) => $b['finalScore'] <=> $a['finalScore']);

        foreach ($contestants as $index => &$contestant) {
            $contestant['rank'] = $index + 1;
        }

        $result = $contestants;

        // Cache for 30 minutes
        Cache::put($cacheKey, $result, now()->addMinutes(30));

        // Broadcast rankings update
        RankingsUpdated::dispatch($pageant->id, $result);

        return $result;
    }

    /**
     * Calculate a contestant's score for a specific round
     */
    public function calculateContestantRoundScore(Contestant $contestant, $round, Pageant $pageant): ?float
    {
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
    }

    /**
     * Calculate weighted average score for a specific judge-contestant-round combination
     */
    public function calculateJudgeContestantScore(int $judgeId, int $contestantId, int $roundId, int $pageantId): ?float
    {
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
            $criteriaWeightedSum += $score->score * $weight;
            $criteriaWeightTotal += $weight;
        }

        return $criteriaWeightTotal > 0 ? $criteriaWeightedSum / $criteriaWeightTotal : null;
    }

    /**
     * Get final score for a specific contestant
     */
    public function getContestantFinalScore(int $pageantId, int $contestantId, bool $useCache = true): ?float
    {
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
                $totalWeightedScore += $roundScore * $roundWeight;
                $totalRoundWeight += $roundWeight;
            }
        }

        $finalScore = $totalRoundWeight > 0 ? $totalWeightedScore / $totalRoundWeight : null;

        // Cache for 30 minutes
        Cache::put($cacheKey, $finalScore, now()->addMinutes(30));

        return $finalScore;
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
