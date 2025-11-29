<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Pageant;
use App\Services\ScoreCalculationService;

echo "=== Detailed Rank Analysis for ms&mrDAUIS ===\n\n";

$pageant = Pageant::where('name', 'ms&mrDAUIS')->first();
$scoreService = app(ScoreCalculationService::class);

// Get casual round (semi-final) results
$stageResults = $scoreService->calculatePageantStageScores($pageant, 'Semi-Final', false);

echo "All contestants in Semi-Final stage:\n";
echo str_pad("ID", 6) . str_pad("Name", 30) . str_pad("Gender", 10) . str_pad("Score", 12) . str_pad("RankSum", 12) . str_pad("Rank", 8) . "Gender Rank\n";
echo str_repeat("-", 100) . "\n";

foreach ($stageResults as $result) {
    $id = $result['id'] ?? 'N/A';
    $name = substr($result['name'] ?? 'Unknown', 0, 28);
    $gender = $result['gender'] ?? 'N/A';
    $score = number_format($result['finalScore'] ?? 0, 2);
    $rankSum = number_format($result['totalRankSum'] ?? 0, 2);
    $rank = $result['rank'] ?? 'N/A';
    $genderRank = $result['genderRank'] ?? 'N/A';
    
    echo str_pad($id, 6) . str_pad($name, 30) . str_pad($gender, 10) . str_pad($score, 12) . str_pad($rankSum, 12) . str_pad($rank, 8) . $genderRank . "\n";
}

echo "\n=== Filtering Males Only ===\n";
$maleResults = array_filter($stageResults, fn($c) => ($c['gender'] ?? '') === 'male');

echo str_pad("ID", 6) . str_pad("Name", 30) . str_pad("Score", 12) . str_pad("RankSum", 12) . str_pad("Rank", 8) . "Gender Rank\n";
echo str_repeat("-", 90) . "\n";

foreach ($maleResults as $result) {
    $id = $result['id'] ?? 'N/A';
    $name = substr($result['name'] ?? 'Unknown', 0, 28);
    $score = number_format($result['finalScore'] ?? 0, 2);
    $rankSum = number_format($result['totalRankSum'] ?? 0, 2);
    $rank = $result['rank'] ?? 'N/A';
    $genderRank = $result['genderRank'] ?? 'N/A';
    
    $highlight = '';
    if (stripos($name, 'michael') !== false || stripos($name, 'garfield') !== false || 
        stripos($name, 'ken') !== false || stripos($name, 'marco') !== false) {
        $highlight = ' <---';
    }
    
    echo str_pad($id, 6) . str_pad($name, 30) . str_pad($score, 12) . str_pad($rankSum, 12) . str_pad($rank, 8) . $genderRank . $highlight . "\n";
}

echo "\n=== Testing getAdvancingContestantIds ===\n";
$advancingIds = $scoreService->getAdvancingContestantIds($pageant, 'Semi-Final');

echo "Advancing IDs returned: " . implode(", ", $advancingIds) . "\n\n";

echo "Matching contestants:\n";
foreach ($stageResults as $result) {
    if (in_array($result['id'], $advancingIds)) {
        $gender = $result['gender'] ?? 'unknown';
        if ($gender === 'male') {
            echo "- {$result['name']} (ID: {$result['id']}, Rank: {$result['rank']}, Gender Rank: " . ($result['genderRank'] ?? 'N/A') . ")\n";
        }
    }
}

echo "\n=== Expected vs Actual ===\n";
echo "Expected (Top 3 by gender rank):\n";
echo "- michael V. (rank should be 1)\n";
echo "- garfield dos (rank should be 2 or 3)\n";
echo "- ken (rank should be 3 or 2)\n\n";

echo "Actual (what getAdvancingContestantIds returned for males):\n";
$actualMales = array_filter($stageResults, fn($c) => in_array($c['id'], $advancingIds) && ($c['gender'] ?? '') === 'male');
foreach ($actualMales as $result) {
    echo "- {$result['name']} (ID: {$result['id']}, Rank: {$result['rank']}, Gender Rank: " . ($result['genderRank'] ?? 'N/A') . ")\n";
}

echo "\n=== Analyzing getTopNWithTies Logic ===\n";
// Manually test the logic
$topN = 3;
echo "Top N = {$topN}\n";
echo "Contestants where rank <= {$topN}:\n";

foreach ($maleResults as $result) {
    $rank = $result['rank'] ?? PHP_INT_MAX;
    if ($rank <= $topN) {
        echo "- {$result['name']} (rank: {$rank}) ✓ SHOULD ADVANCE\n";
    } else {
        echo "- {$result['name']} (rank: {$rank}) ✗ should not advance\n";
    }
}
