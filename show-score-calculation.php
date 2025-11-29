<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Pageant;
use App\Models\Score;
use App\Services\ScoreCalculationService;
use Illuminate\Support\Facades\DB;

echo "=== Detailed Score Calculation Breakdown for ms&mrDAUIS ===\n\n";

$pageant = Pageant::where('name', 'ms&mrDAUIS')->first();
$scoreService = app(ScoreCalculationService::class);

// Get the casual/semi-final round
$casualRound = $pageant->rounds()->where('type', 'Semi-Final')->first();

if (!$casualRound) {
    die("Semi-Final round not found!\n");
}

echo "Round: {$casualRound->name}\n";
echo "Type: {$casualRound->type}\n";
echo "Current Ranking Method: {$pageant->ranking_method}\n\n";

// Get all male contestants
$males = $pageant->contestants()->where('gender', 'male')->orderBy('number')->get();

echo "=== Male Contestants ===\n";
foreach ($males as $male) {
    echo "{$male->id}. {$male->name} (#{$male->number})\n";
}

echo "\n=== Getting All Judge Scores for Casual Round ===\n\n";

// Get all criteria for this round
$criteria = $casualRound->criteria()->orderBy('display_order')->get();

echo "Criteria in this round:\n";
foreach ($criteria as $criterion) {
    echo "- {$criterion->name} (weight: {$criterion->weight}%)\n";
}
echo "\n";

// Get all judges for this pageant
$judges = $pageant->judges()->get();

echo "Judges:\n";
foreach ($judges as $judge) {
    echo "- Judge {$judge->id}: {$judge->name}\n";
}
echo "\n";

// For each male contestant, show their scores
foreach ($males as $contestant) {
    echo str_repeat("=", 80) . "\n";
    echo "CONTESTANT: {$contestant->name} (ID: {$contestant->id})\n";
    echo str_repeat("=", 80) . "\n\n";
    
    // Get all scores for this contestant in this round
    $scores = Score::where('pageant_id', $pageant->id)
        ->where('round_id', $casualRound->id)
        ->where('contestant_id', $contestant->id)
        ->orderBy('judge_id')
        ->orderBy('criteria_id')
        ->get();
    
    if ($scores->isEmpty()) {
        echo "❌ No scores found for this contestant\n\n";
        continue;
    }
    
    // Group by judge
    $scoresByJudge = $scores->groupBy('judge_id');
    
    $judgeScores = [];
    
    foreach ($scoresByJudge as $judgeId => $judgeScores_raw) {
        $judge = $judges->firstWhere('id', $judgeId);
        $judgeName = $judge ? $judge->name : "Judge {$judgeId}";
        
        echo "Judge: {$judgeName}\n";
        echo str_repeat("-", 60) . "\n";
        
        $totalWeightedScore = 0;
        $totalWeight = 0;
        
        foreach ($judgeScores_raw as $score) {
            $criterion = $criteria->firstWhere('id', $score->criteria_id);
            if ($criterion) {
                $weight = $criterion->weight / 100; // Convert percentage to decimal
                $weightedScore = $score->score * $weight;
                $totalWeightedScore += $weightedScore;
                $totalWeight += $weight;
                
                echo sprintf("  %-30s: %6.2f × %.2f (weight) = %.2f\n", 
                    $criterion->name, 
                    $score->score, 
                    $weight,
                    $weightedScore
                );
            }
        }
        
        $judgeAverage = $totalWeight > 0 ? $totalWeightedScore / $totalWeight : 0;
        $judgeScores[$judgeId] = $judgeAverage;
        
        echo sprintf("  Judge Average: %.2f\n\n", $judgeAverage);
    }
    
    // Calculate final score (average of all judges)
    $finalScore = count($judgeScores) > 0 ? array_sum($judgeScores) / count($judgeScores) : 0;
    
    echo "SUMMARY:\n";
    echo sprintf("  Total Judges: %d\n", count($judgeScores));
    echo sprintf("  Judge Scores: %s\n", implode(', ', array_map(fn($s) => number_format($s, 2), $judgeScores)));
    echo sprintf("  FINAL SCORE: %.2f (average of judge scores)\n\n", $finalScore);
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "=== RANKING ALL MALES BY SCORE (HIGHEST TO LOWEST) ===\n";
echo str_repeat("=", 80) . "\n\n";

// Calculate and display all scores
$allScores = [];
foreach ($males as $contestant) {
    $finalScore = $scoreService->calculateContestantRoundScore($contestant, $casualRound, $pageant);
    $allScores[] = [
        'id' => $contestant->id,
        'name' => $contestant->name,
        'score' => $finalScore ?? 0
    ];
}

// Sort by score descending (highest first)
usort($allScores, fn($a, $b) => $b['score'] <=> $a['score']);

echo str_pad("Rank", 6) . str_pad("Name", 30) . str_pad("Score", 12) . "ID\n";
echo str_repeat("-", 60) . "\n";

foreach ($allScores as $index => $data) {
    $rank = $index + 1;
    echo str_pad($rank, 6) . 
         str_pad(substr($data['name'], 0, 28), 30) . 
         str_pad(number_format($data['score'], 2), 12) . 
         $data['id'] . "\n";
}

echo "\n=== CONCLUSION ===\n";
echo "With score_average method (highest score wins):\n";
echo "Top 3 should be:\n";
for ($i = 0; $i < min(3, count($allScores)); $i++) {
    echo ($i + 1) . ". {$allScores[$i]['name']} - " . number_format($allScores[$i]['score'], 2) . "\n";
}

echo "\n✓ Calculation complete!\n";
