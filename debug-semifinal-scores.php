<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Pageant;
use App\Services\ScoreCalculationService;

$pageants = Pageant::all();
echo "Available pageants:\n";
foreach ($pageants as $p) {
    echo "  ID: {$p->id} - {$p->name}\n";
}
echo "\n";

$pageant = Pageant::first();

if (!$pageant) {
    die("Pageant not found!\n");
}

// Find mr.ARDUINO pageant
$pageant = $pageants->firstWhere('name', 'mr.ARDUINO');
if (!$pageant) {
    die("mr.ARDUINO pageant not found!\n");
}

echo "Using Pageant: {$pageant->name}\n\n";

$service = app(ScoreCalculationService::class);

$rounds = $pageant->rounds->sortBy('display_order');
echo "Rounds:\n";
foreach ($rounds as $round) {
    echo "  {$round->name} ({$round->type}) - Order: {$round->display_order}\n";
}
echo "\n";

// Try both lowercase and capitalized versions
$semifinalRound = $rounds->filter(function($r) {
    return strtolower($r->type) === 'semi-final';
})->first();

if (!$semifinalRound) {
    die("No semi-final round found!\n");
}

echo "Semi-Final Round: {$semifinalRound->name}\n\n";

$result = $service->calculateRoundViewScores($pageant, $semifinalRound, false);

echo "Number of contestants in result: " . count($result) . "\n\n";

if (count($result) > 0) {
    echo "First 3 contestants:\n";
    foreach (array_slice($result, 0, 3) as $contestant) {
        echo "\nContestant: {$contestant['name']}\n";
        echo "  Total Score: {$contestant['totalScore']}\n";
        echo "  Final Score: {$contestant['finalScore']}\n";
        echo "  Scores array:\n";
        var_dump($contestant['scores']);
        echo "  Number of scores: " . count($contestant['scores']) . "\n";
        foreach ($contestant['scores'] as $roundName => $score) {
            echo "    Round '{$roundName}': ";
            var_dump($score);
        }
    }
}
