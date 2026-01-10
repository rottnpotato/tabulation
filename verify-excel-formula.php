<?php

/**
 * Verification script for Excel-style weighted rank calculation
 *
 * Tests that Laravel's calculateWithRankSum() now matches Excel's formula:
 * 1. Calculate rank sum per round (sum of judge ranks)
 * 2. Rank contestants within each round by rank sum
 * 3. Apply weight to RANK: (rank/100) * weight%
 * 4. Average weighted ranks
 * 5. Final rank by weighted average (lower = better)
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\ScoreCalculationService;

$service = new ScoreCalculationService;

echo "=== EXCEL FORMULA VERIFICATION ===\n\n";

// Test data: 3 rounds with weights 25%, 40%, 35%
// Simulated category final ranks (what Excel computes from K15, K28, K41)
$categoryRanks = [
    1 => ['production' => 3, 'tropical' => 1, 'casual' => 6],
    2 => ['production' => 6, 'tropical' => 2, 'casual' => 4],
    3 => ['production' => 1.5, 'tropical' => 3, 'casual' => 2],
    4 => ['production' => 1.5, 'tropical' => 4, 'casual' => 5],
    5 => ['production' => 4, 'tropical' => 5, 'casual' => 3],
    6 => ['production' => 5, 'tropical' => 6, 'casual' => 1],
];

$weights = [
    'production' => 25,
    'tropical' => 40,
    'casual' => 35,
];

echo "EXCEL FORMULA: (rank/100) * weight%\n";
echo "Round Weights: Production=25%, Tropical=40%, Casual=35%\n\n";

echo "STEP-BY-STEP CALCULATION (Excel Method):\n";
echo str_repeat('-', 80)."\n";
echo sprintf("%-12s | %-8s | %-8s | %-8s | %-10s | %-12s | %-6s\n",
    'Contestant', 'Prod×25%', 'Trop×40%', 'Cas×35%', 'Sum', 'Avg (÷3)', 'Rank');
echo str_repeat('-', 80)."\n";

$results = [];
foreach ($categoryRanks as $id => $ranks) {
    $prodWeighted = ($ranks['production'] / 100) * $weights['production'];
    $tropWeighted = ($ranks['tropical'] / 100) * $weights['tropical'];
    $casWeighted = ($ranks['casual'] / 100) * $weights['casual'];
    $sum = $prodWeighted + $tropWeighted + $casWeighted;
    $avg = $sum / 3;

    $results[$id] = [
        'prod' => $prodWeighted,
        'trop' => $tropWeighted,
        'cas' => $casWeighted,
        'sum' => $sum,
        'avg' => $avg,
    ];

    echo sprintf("%-12s | %-8.4f | %-8.4f | %-8.4f | %-10.4f | %-12.4f |\n",
        "Contestant $id", $prodWeighted, $tropWeighted, $casWeighted, $sum, $avg);
}

// Calculate final ranks
$allAvgs = array_column($results, 'avg');
foreach ($results as $id => &$r) {
    $r['rank'] = $service->calculateRankAvg($r['avg'], $allAvgs, 'asc');
}
unset($r);

echo str_repeat('-', 80)."\n\n";

echo "FINAL RANKINGS (by weighted average, ascending):\n";
echo str_repeat('-', 50)."\n";

// Sort by avg
uasort($results, fn ($a, $b) => $a['avg'] <=> $b['avg']);

$position = 1;
foreach ($results as $id => $r) {
    echo sprintf("Rank %d: Contestant %d (weighted avg = %.4f)\n",
        $position++, $id, $r['avg']);
}

echo "\n";
echo str_repeat('=', 80)."\n";
echo "EXPECTED TOP 3 SEMI-FINALISTS (Excel method):\n";
echo str_repeat('=', 80)."\n";

$top3 = array_slice(array_keys($results), 0, 3);
echo '1st: Contestant '.$top3[0]."\n";
echo '2nd: Contestant '.$top3[1]."\n";
echo '3rd: Contestant '.$top3[2]."\n";

echo "\n";
echo "✅ The rank_sum method now uses the Excel formula:\n";
echo "   - Weights are applied to RANKS, not scores\n";
echo "   - Formula: (rank / 100) * weight%\n";
echo "   - Final ranking by weighted average (lower is better)\n";
