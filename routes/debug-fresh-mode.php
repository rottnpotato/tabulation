<?php

use App\Models\Pageant;
use Illuminate\Support\Facades\Route;

// Temporary debug route - DELETE AFTER TESTING
Route::get('/debug-fresh-mode/{pageantId}', function ($pageantId) {
    $pageant = Pageant::with(['rounds', 'contestants', 'judges'])->find($pageantId);

    if (! $pageant) {
        return response()->json(['error' => 'Pageant not found']);
    }

    $service = app(\App\Services\ScoreCalculationService::class);

    // Get final round
    $lastFinalRound = $pageant->rounds
        ->filter(fn ($round) => strtolower($round->type) === 'final')
        ->sortByDesc('display_order')
        ->first();

    if (! $lastFinalRound) {
        return response()->json(['error' => 'No final round found']);
    }

    $results = [];
    $finalScoreMode = $pageant->final_score_mode ?? 'fresh';
    $tieHandling = $pageant->tie_handling ?? 'average';

    foreach ($pageant->contestants as $contestant) {
        // Get judge ranks using the service method via reflection (private method)
        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('getJudgeRanksForRound');
        $method->setAccessible(true);

        $judgeData = $method->invoke($service, $contestant, $lastFinalRound, $pageant, $tieHandling, $finalScoreMode);

        $results[] = [
            'contestant' => $contestant->name,
            'finalScoreMode' => $finalScoreMode,
            'judgeDetails' => $judgeData['details'],
            'ranks' => $judgeData['ranks'],
            'rankSum' => array_sum($judgeData['ranks']),
        ];
    }

    return response()->json([
        'pageant' => $pageant->name,
        'finalScoreMode' => $finalScoreMode,
        'finalRound' => $lastFinalRound->name,
        'results' => $results,
    ]);
})->middleware('web');
