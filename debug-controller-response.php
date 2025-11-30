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

// Simulate what the controller does
$sortedRounds = $pageant->rounds->sortBy('display_order');

echo "All Rounds from controller:\n";
$rounds = $sortedRounds->map(function ($round) {
    return [
        'id' => $round->id,
        'name' => $round->name,
        'type' => $round->type,
        'weight' => $round->weight,
        'top_n_proceed' => $round->top_n_proceed,
        'display_order' => $round->display_order,
    ];
})->values();

foreach ($rounds as $round) {
    echo "  ID: {$round['id']}, Name: '{$round['name']}', Type: {$round['type']}, Order: {$round['display_order']}\n";
}

echo "\n";

// Get the semi-final round
$semifinalRound = $sortedRounds->filter(function($r) {
    return strtolower($r->type) === 'semi-final';
})->first();

if (!$semifinalRound) {
    die("No semi-final round found!\n");
}

echo "Semi-Final Round Selected: ID={$semifinalRound->id}, Name='{$semifinalRound->name}'\n\n";

// Calculate round view scores (what goes in roundResults)
$roundContestants = $service->calculateRoundViewScores($pageant, $semifinalRound, false);

echo "Round Result for round_{$semifinalRound->id}:\n";
echo "  Number of contestants: " . count($roundContestants) . "\n";
if (count($roundContestants) > 0) {
    $first = $roundContestants[0];
    echo "  First contestant: {$first['name']}\n";
    echo "  Score keys: " . implode(', ', array_keys($first['scores'])) . "\n";
    echo "  Round names in scores array:\n";
    foreach (array_keys($first['scores']) as $key) {
        echo "    - '{$key}' (length: " . strlen($key) . ")\n";
    }
}

echo "\n";
echo "Rounds array that would be sent to frontend:\n";
foreach ($rounds as $round) {
    echo "  - '{$round['name']}' (length: " . strlen($round['name']) . ")\n";
}
