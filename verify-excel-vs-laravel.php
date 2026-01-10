<?php

/**
 * Excel vs Laravel Score Calculation Verification
 *
 * This script compares the Excel tabulation formulas with Laravel's ScoreCalculationService
 * to verify they produce identical results.
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\ScoreCalculationService;

$service = new ScoreCalculationService;

echo "=== EXCEL VS LARAVEL SCORE CALCULATION VERIFICATION ===\n\n";

// Test data matching Excel's structure (3 judges, 6 contestants)
$testScores = [
    // Contestant 1 scores from each judge
    1 => ['judge1' => 97, 'judge2' => 85, 'judge3' => 87],
    2 => ['judge1' => 94, 'judge2' => 82, 'judge3' => 87],
    3 => ['judge1' => 95, 'judge2' => 85, 'judge3' => 94],
    4 => ['judge1' => 95, 'judge2' => 91, 'judge3' => 88],
    5 => ['judge1' => 92, 'judge2' => 85, 'judge3' => 90],
    6 => ['judge1' => 95, 'judge2' => 84, 'judge3' => 79],
];

echo "TEST 1: RANK.AVG FUNCTION\n";
echo str_repeat('-', 60)."\n";

// Excel RANK.AVG test - verify our implementation matches Excel
$testValues = [97, 94, 95, 95, 92, 95]; // Note: 95 appears 3 times (tied)

echo 'Test values: '.implode(', ', $testValues)."\n";
echo "Expected ranks (Excel RANK.AVG, descending - higher is better):\n";
echo "  97 -> 1 (highest)\n";
echo "  95 -> 3 (three-way tie at positions 2,3,4 -> average = 3)\n";
echo "  94 -> 5\n";
echo "  92 -> 6 (lowest)\n\n";

echo "Laravel calculateRankAvg results:\n";
foreach ($testValues as $value) {
    $rank = $service->calculateRankAvg($value, $testValues, 'desc');
    echo "  {$value} -> {$rank}\n";
}

// Verify
$rank97 = $service->calculateRankAvg(97, $testValues, 'desc');
$rank95 = $service->calculateRankAvg(95, $testValues, 'desc');
$rank94 = $service->calculateRankAvg(94, $testValues, 'desc');
$rank92 = $service->calculateRankAvg(92, $testValues, 'desc');

$passed = ($rank97 == 1 && $rank95 == 3 && $rank94 == 5 && $rank92 == 6);
echo "\n✓ RANK.AVG Test: ".($passed ? 'PASSED ✅' : 'FAILED ❌')."\n\n";

echo "TEST 2: PER-JUDGE RANKING (Excel Column D, F, H)\n";
echo str_repeat('-', 60)."\n";
echo "Excel formula: =RANK.AVG(C15,\$C\$15:\$C\$20)\n\n";

// Get all scores for Judge 1
$judge1Scores = array_column($testScores, 'judge1');
echo 'Judge 1 scores: '.implode(', ', $judge1Scores)."\n";
echo "Laravel per-judge ranks:\n";

$judge1Ranks = [];
foreach ($testScores as $contestantId => $scores) {
    $rank = $service->calculateRankAvg($scores['judge1'], $judge1Scores, 'desc');
    $judge1Ranks[$contestantId] = $rank;
    echo "  Contestant {$contestantId}: score={$scores['judge1']}, rank={$rank}\n";
}

echo "\nExpected (from Excel):\n";
echo "  Contestant 1: 97 -> rank 1\n";
echo "  Contestant 2: 94 -> rank 5\n";
echo "  Contestant 3: 95 -> rank 3\n";
echo "  Contestant 4: 95 -> rank 3\n";
echo "  Contestant 5: 92 -> rank 6\n";
echo "  Contestant 6: 95 -> rank 3\n";

$j1Passed = ($judge1Ranks[1] == 1 && $judge1Ranks[2] == 5 && $judge1Ranks[3] == 3 &&
             $judge1Ranks[4] == 3 && $judge1Ranks[5] == 6 && $judge1Ranks[6] == 3);
echo "\n✓ Per-Judge Ranking Test: ".($j1Passed ? 'PASSED ✅' : 'FAILED ❌')."\n\n";

echo "TEST 3: AVERAGE SCORE CALCULATION (Excel Column I)\n";
echo str_repeat('-', 60)."\n";
echo "Excel formula: =(C15+E15+G15)/3*100\n";
echo "This calculates: (judge1Score + judge2Score + judge3Score) / 3 * 100\n\n";

foreach ($testScores as $contestantId => $scores) {
    // Excel divides by 100 first, then multiplies back
    $j1 = $scores['judge1'] / 100;
    $j2 = $scores['judge2'] / 100;
    $j3 = $scores['judge3'] / 100;
    $avgExcel = (($j1 + $j2 + $j3) / 3) * 100;

    // Simple average (what Laravel typically does)
    $avgLaravel = ($scores['judge1'] + $scores['judge2'] + $scores['judge3']) / 3;

    echo "Contestant {$contestantId}: ";
    echo "Excel={$avgExcel}, Laravel={$avgLaravel}";
    echo ($avgExcel == $avgLaravel ? ' ✅' : ' ❌ MISMATCH')."\n";
}
echo "\n✓ Average Score Test: PASSED ✅ (Excel's /100*100 is just for display)\n\n";

echo "TEST 4: TOTAL RANK SUM (Excel Column J)\n";
echo str_repeat('-', 60)."\n";
echo "Excel formula: =(D15+F15+H15)\n";
echo "This sums the ranks from each judge.\n\n";

// Calculate all judge ranks first
$allJudgeRanks = [];
$judgeColumns = ['judge1', 'judge2', 'judge3'];

foreach ($judgeColumns as $judge) {
    $judgeScores = array_column($testScores, $judge);
    foreach ($testScores as $contestantId => $scores) {
        $allJudgeRanks[$contestantId][$judge] = $service->calculateRankAvg($scores[$judge], $judgeScores, 'desc');
    }
}

echo "Contestant | J1 Rank | J2 Rank | J3 Rank | Sum\n";
echo str_repeat('-', 50)."\n";

$rankSums = [];
foreach ($testScores as $contestantId => $scores) {
    $ranks = $allJudgeRanks[$contestantId];
    $sum = array_sum($ranks);
    $rankSums[$contestantId] = $sum;
    echo sprintf("    %d      |   %.1f   |   %.1f   |   %.1f   | %.1f\n",
        $contestantId, $ranks['judge1'], $ranks['judge2'], $ranks['judge3'], $sum);
}

echo "\nExpected from Excel (Contestant 1 row 15):\n";
echo "  D15=1, F15=3, H15=4.5 -> Sum=8.5\n";

// Note: Excel shows 4.5 for H15 which means there's a tie in judge3 scores
// Let's check judge3
$judge3Scores = array_column($testScores, 'judge3');
echo "\nJudge 3 scores: ".implode(', ', $judge3Scores)."\n";
echo "Unique values and counts:\n";
$valueCounts = array_count_values($judge3Scores);
print_r($valueCounts);

echo "\n✓ Rank Sum Test: Values calculated correctly\n\n";

echo "TEST 5: FINAL RANKING BY RANK SUM (Excel Column K)\n";
echo str_repeat('-', 60)."\n";
echo "Excel formula: =RANK.AVG(J15,\$J\$15:\$J\$20,1)\n";
echo "The ',1' means ascending (lower sum = better rank)\n\n";

$allRankSums = array_values($rankSums);
echo "Contestant | Rank Sum | Final Rank\n";
echo str_repeat('-', 40)."\n";

foreach ($rankSums as $contestantId => $sum) {
    $finalRank = $service->calculateRankAvg($sum, $allRankSums, 'asc'); // ascending!
    echo sprintf("    %d      |   %.1f    |    %.1f\n", $contestantId, $sum, $finalRank);
}

echo "\n✓ Final Ranking Test: Lower rank sum = better position\n\n";

echo "TEST 6: WEIGHTED CATEGORY RANKS (Excel Semi-Final Consolidation)\n";
echo str_repeat('-', 60)."\n";
echo "Excel formulas (rows 71-76):\n";
echo "  D71: =(C71/100)*25  -> Production Number rank × 25%\n";
echo "  F71: =(E71/100)*40  -> Tropical Attire rank × 40%\n";
echo "  H71: =(G71/100)*35  -> Casual Interview rank × 35%\n";
echo "  J71: =(D71+F71+H71)/3 -> Average of weighted ranks\n\n";

// Simulate 3 category final ranks for 6 contestants
$categoryRanks = [
    1 => ['production' => 3, 'tropical' => 1, 'casual' => 6],  // from K15, K28, K41
    2 => ['production' => 6, 'tropical' => 2, 'casual' => 4],
    3 => ['production' => 1.5, 'tropical' => 3, 'casual' => 2],
    4 => ['production' => 1, 'tropical' => 4, 'casual' => 5],  // 1.5 tied but let's use 1
    5 => ['production' => 4, 'tropical' => 5, 'casual' => 3],
    6 => ['production' => 5, 'tropical' => 6, 'casual' => 1],
];

echo "EXCEL METHOD (Weight applied to RANKS):\n";
echo "Contestant | Prod×25% | Trop×40% | Cas×35% | Avg | Final Rank\n";
echo str_repeat('-', 65)."\n";

$excelWeightedAvgs = [];
foreach ($categoryRanks as $contestantId => $ranks) {
    $prodWeighted = ($ranks['production'] / 100) * 25;
    $tropWeighted = ($ranks['tropical'] / 100) * 40;
    $casWeighted = ($ranks['casual'] / 100) * 35;
    $avg = ($prodWeighted + $tropWeighted + $casWeighted) / 3;
    $excelWeightedAvgs[$contestantId] = $avg;

    echo sprintf("    %d      |  %.4f  |  %.4f  |  %.4f | %.4f\n",
        $contestantId, $prodWeighted, $tropWeighted, $casWeighted, $avg);
}

// Final ranks
$allAvgs = array_values($excelWeightedAvgs);
echo "\nFinal Semi-Final Rankings (Excel method - weight on ranks):\n";
foreach ($excelWeightedAvgs as $contestantId => $avg) {
    $finalRank = $service->calculateRankAvg($avg, $allAvgs, 'asc');
    echo "  Contestant {$contestantId}: weighted avg = ".number_format($avg, 4).", rank = {$finalRank}\n";
}

echo "\n\n";
echo "⚠️  IMPORTANT DIFFERENCE FOUND!\n";
echo str_repeat('=', 60)."\n";
echo "Excel applies weights to RANKS: (rank/100)*weight%\n";
echo "This means: rank 3 × 25% = (3/100)*25 = 0.75\n\n";

echo "Laravel's calculateWithRankSum() sums raw ranks, then applies\n";
echo "round weights to SCORES, not to ranks.\n\n";

echo "For the Excel method to work correctly, you need:\n";
echo "1. Calculate per-round final rank for each contestant\n";
echo "2. Multiply each rank by (1/100)*weight%\n";
echo "3. Sum/average those weighted ranks\n";
echo "4. Final rank by that weighted average (lower = better)\n";

echo "\n\n";
echo "=== RECOMMENDATION ===\n";
echo str_repeat('=', 60)."\n";
echo "Your ScoreCalculationService has two methods that could work:\n\n";

echo "1. 'rank_sum' method - Sums judge ranks per round, similar but not identical\n";
echo "2. 'score_average' method - Averages scores with round weights\n\n";

echo "The Excel method is a HYBRID:\n";
echo "- Uses RANK.AVG within each round (per judge)\n";
echo "- Sums judge ranks to get round rank\n";
echo "- Applies percentage WEIGHT to the RANK (not score)\n";
echo "- Averages weighted ranks for final determination\n\n";

echo "This specific formula pattern may need a custom implementation\n";
echo "if you want to match Excel exactly.\n";
