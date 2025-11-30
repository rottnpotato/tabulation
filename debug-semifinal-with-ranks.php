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

$semifinalRound = $pageant->rounds->filter(function($r) {
    return strtolower($r->type) === 'semi-final';
})->first();

if (!$semifinalRound) {
    die("No semi-final round found!\n");
}

echo "Pageant: {$pageant->name}\n";
echo "Ranking Method: {$pageant->ranking_method}\n";
echo "Tie Handling: {$pageant->tie_handling}\n";
echo "Semi-Final Round: {$semifinalRound->name}\n\n";

$result = $service->calculateRoundViewScores($pageant, $semifinalRound, false);

echo "Contestants with ranks:\n";
foreach ($result as $contestant) {
    echo sprintf(
        "  %s: Score=%.2f, RankSum=%.2f, Rank=%d\n",
        str_pad($contestant['name'], 12),
        $contestant['totalScore'],
        $contestant['totalRankSum'],
        $contestant['rank'] ?? 0
    );
}
