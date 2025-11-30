<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Pageant;
use App\Services\ScoreCalculationService;

$pageant = Pageant::where('name', 'mr.ARDUINO')->first();

if (!$pageant) {
    die("mr.ARDUINO pageant not found!\n");
}

$service = app(ScoreCalculationService::class);

echo "Pageant: {$pageant->name}\n";
echo "Ranking Method: {$pageant->ranking_method}\n\n";

// Get all rounds
$rounds = $pageant->rounds->sortBy('display_order');
echo "Rounds:\n";
foreach ($rounds as $round) {
    echo "  {$round->name} ({$round->type})\n";
}
echo "\n";

// Calculate final scores (what's shown in Overall Tally)
echo "=== OVERALL TALLY (Final Scores) ===\n";
$overallContestants = $service->calculatePageantFinalScores($pageant);

foreach ($overallContestants as $contestant) {
    echo sprintf(
        "%s: FinalScore=%.2f, TotalRankSum=%.2f, Rank=%d\n",
        str_pad($contestant['name'], 12),
        $contestant['finalScore'] ?? $contestant['totalScore'],
        $contestant['totalRankSum'] ?? 0,
        $contestant['rank'] ?? 0
    );
    if (isset($contestant['scores'])) {
        echo "  Round scores: " . json_encode($contestant['scores']) . "\n";
    }
}

echo "\n=== EVENING GOWN VIEW (Semi-Final Only) ===\n";
$semifinalRound = $rounds->filter(function($r) {
    return strtolower($r->type) === 'semi-final';
})->first();

$roundContestants = $service->calculateRoundViewScores($pageant, $semifinalRound, false);

foreach ($roundContestants as $contestant) {
    echo sprintf(
        "%s: Score=%.2f, RankSum=%.2f, Rank=%d\n",
        str_pad($contestant['name'], 12),
        $contestant['totalScore'],
        $contestant['totalRankSum'],
        $contestant['rank']
    );
}
