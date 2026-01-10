<?php

/**
 * Test tie handling in the Excel-style weighted rank calculation
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\ScoreCalculationService;

$service = new ScoreCalculationService;

echo "=== TIE HANDLING TEST ===\n\n";

// Test 1: RANK.AVG with ties
echo "TEST 1: RANK.AVG Function with Ties\n";
echo str_repeat('-', 60)."\n";

$valuesWithTies = [10, 8, 8, 6, 6, 6, 4]; // 8 appears twice, 6 appears 3 times
echo "Values: ".implode(', ', $valuesWithTies)."\n\n";

echo "Expected RANK.AVG results (ascending - lower value = better rank):\n";
echo "  Value 4  -> Rank 1 (lowest, best)\n";
echo "  Value 6  -> Rank 3 (3 tied at positions 2,3,4 -> avg = 3)\n";
echo "  Value 8  -> Rank 5.5 (2 tied at positions 5,6 -> avg = 5.5)\n";
echo "  Value 10 -> Rank 7 (highest, worst)\n\n";

echo "Laravel RANK.AVG results:\n";
foreach (array_unique($valuesWithTies) as $value) {
    $rank = $service->calculateRankAvg($value, $valuesWithTies, 'asc');
    echo "  Value {$value} -> Rank {$rank}\n";
}

// Verify
$rank4 = $service->calculateRankAvg(4, $valuesWithTies, 'asc');
$rank6 = $service->calculateRankAvg(6, $valuesWithTies, 'asc');
$rank8 = $service->calculateRankAvg(8, $valuesWithTies, 'asc');
$rank10 = $service->calculateRankAvg(10, $valuesWithTies, 'asc');

$passed = ($rank4 == 1 && $rank6 == 3 && $rank8 == 5.5 && $rank10 == 7);
echo "\n✓ RANK.AVG Tie Test: ".($passed ? "PASSED ✅" : "FAILED ❌")."\n\n";


// Test 2: Tied weighted averages
echo str_repeat('=', 60)."\n";
echo "TEST 2: Tied Weighted Averages in Final Ranking\n";
echo str_repeat('-', 60)."\n";

// Create scenario where two contestants have same weighted average
$categoryRanks = [
    1 => ['production' => 1, 'tropical' => 3, 'casual' => 2],  // weighted avg = X
    2 => ['production' => 2, 'tropical' => 1, 'casual' => 3],  // Same weights should give same avg
    3 => ['production' => 3, 'tropical' => 2, 'casual' => 1],  // Same weighted avg as above
    4 => ['production' => 4, 'tropical' => 4, 'casual' => 4],
];

$weights = [
    'production' => 25,
    'tropical' => 40,
    'casual' => 35,
];

echo "Testing contestants with potentially tied weighted averages:\n\n";

$results = [];
foreach ($categoryRanks as $id => $ranks) {
    $prodWeighted = ($ranks['production'] / 100) * $weights['production'];
    $tropWeighted = ($ranks['tropical'] / 100) * $weights['tropical'];
    $casWeighted = ($ranks['casual'] / 100) * $weights['casual'];
    $avg = ($prodWeighted + $tropWeighted + $casWeighted) / 3;
    
    $results[$id] = $avg;
    
    echo "Contestant $id:\n";
    echo "  Prod rank {$ranks['production']} × 25% = ".number_format($prodWeighted, 4)."\n";
    echo "  Trop rank {$ranks['tropical']} × 40% = ".number_format($tropWeighted, 4)."\n";
    echo "  Cas rank {$ranks['casual']} × 35% = ".number_format($casWeighted, 4)."\n";
    echo "  Weighted Avg = ".number_format($avg, 4)."\n\n";
}

// Check for ties
$allAvgs = array_values($results);
$uniqueAvgs = array_unique($allAvgs);

echo str_repeat('-', 60)."\n";
echo "FINAL RANKING (with tie handling):\n";
echo str_repeat('-', 60)."\n";

foreach ($results as $id => $avg) {
    $finalRank = $service->calculateRankAvg($avg, $allAvgs, 'asc');
    echo "Contestant $id: weighted avg = ".number_format($avg, 4)." -> Final Rank = {$finalRank}\n";
}

if (count($uniqueAvgs) < count($allAvgs)) {
    echo "\n⚠️  TIES DETECTED! Multiple contestants have the same weighted average.\n";
    echo "   RANK.AVG gives them averaged ranks (e.g., 1.5 instead of both getting 1 or 2)\n";
} else {
    echo "\n✅ No ties in this test case - all weighted averages are unique.\n";
}

// Test 3: Identical judge scores leading to tied ranks
echo "\n";
echo str_repeat('=', 60)."\n";
echo "TEST 3: Identical Scores -> Identical Rank Sums -> Tied Final\n";
echo str_repeat('-', 60)."\n";

// If two contestants get identical scores from all judges, they'll have identical rank sums
$identicalScenario = [
    1 => ['production' => 2, 'tropical' => 2, 'casual' => 2],  // Identical
    2 => ['production' => 2, 'tropical' => 2, 'casual' => 2],  // Identical - should tie!
    3 => ['production' => 1, 'tropical' => 1, 'casual' => 1],  // Best
];

echo "Scenario: Contestants 1 & 2 have IDENTICAL ranks in all rounds\n\n";

$results3 = [];
foreach ($identicalScenario as $id => $ranks) {
    $prodWeighted = ($ranks['production'] / 100) * $weights['production'];
    $tropWeighted = ($ranks['tropical'] / 100) * $weights['tropical'];
    $casWeighted = ($ranks['casual'] / 100) * $weights['casual'];
    $avg = ($prodWeighted + $tropWeighted + $casWeighted) / 3;
    
    $results3[$id] = $avg;
    echo "Contestant $id: weighted avg = ".number_format($avg, 4)."\n";
}

$allAvgs3 = array_values($results3);
echo "\nFinal Rankings:\n";
foreach ($results3 as $id => $avg) {
    $finalRank = $service->calculateRankAvg($avg, $allAvgs3, 'asc');
    echo "  Contestant $id -> Rank {$finalRank}";
    if ($finalRank != floor($finalRank)) {
        echo " (TIED - averaged rank)";
    }
    echo "\n";
}

echo "\n";
echo str_repeat('=', 60)."\n";
echo "SUMMARY:\n";
echo str_repeat('=', 60)."\n";
echo "• The Excel formula does NOT prevent ties\n";
echo "• It HANDLES ties using RANK.AVG (averaged ranks)\n";
echo "• Tied contestants get the average of the positions they occupy\n";
echo "• Example: Two tied for 2nd place both get rank 2.5\n";
echo "\n";
echo "To PREVENT ties, you would need a tiebreaker rule such as:\n";
echo "  1. Higher total score wins the tie\n";
echo "  2. Better performance in final round wins\n";
echo "  3. Judges' majority preference wins\n";

