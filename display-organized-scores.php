<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Pageant;
use App\Models\Score;
use App\Services\ScoreCalculationService;

echo "╔════════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                    ms&mrDAUIS - CASUAL ROUND SCORING SUMMARY                   ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════════╝\n\n";

$pageant = Pageant::where('name', 'ms&mrDAUIS')->first();
$casualRound = $pageant->rounds()->where('name', 'casual')->first();
$scoreService = app(ScoreCalculationService::class);

// Get criteria, judges, and contestants
$criteria = $casualRound->criteria()->orderBy('display_order')->get();
$judges = $pageant->judges()->orderBy('id')->get();
$males = $pageant->contestants()->where('gender', 'male')->orderBy('number')->get();

echo "ROUND: {$casualRound->name} (Semi-Final)\n";
echo "TOP N PROCEED: {$casualRound->top_n_proceed}\n";
echo "RANKING METHOD: {$pageant->ranking_method}\n\n";

echo "JUDGES: " . $judges->pluck('name')->implode(', ') . "\n";
echo "CRITERIA: " . $criteria->pluck('name')->implode(', ') . "\n";
echo "WEIGHTS: " . $criteria->map(fn($c) => "{$c->weight}%")->implode(', ') . "\n\n";

echo str_repeat("═", 120) . "\n\n";

foreach ($males as $contestant) {
    echo "┌" . str_repeat("─", 118) . "┐\n";
    echo "│ CONTESTANT: " . str_pad($contestant->name, 104) . "│\n";
    echo "│ NUMBER: #" . str_pad($contestant->number, 108) . "│\n";
    echo "└" . str_repeat("─", 118) . "┘\n\n";
    
    // Table header
    echo str_pad("JUDGE", 20) . " │ ";
    foreach ($criteria as $criterion) {
        echo str_pad(substr($criterion->name, 0, 15), 17) . " │ ";
    }
    echo str_pad("WEIGHTED AVG", 15) . "\n";
    echo str_repeat("─", 120) . "\n";
    
    $judgeAverages = [];
    
    foreach ($judges as $judge) {
        echo str_pad(substr($judge->name, 0, 19), 20) . " │ ";
        
        $criteriaScores = [];
        $weightedSum = 0;
        $totalWeight = 0;
        
        foreach ($criteria as $criterion) {
            $score = Score::where('pageant_id', $pageant->id)
                ->where('round_id', $casualRound->id)
                ->where('contestant_id', $contestant->id)
                ->where('judge_id', $judge->id)
                ->where('criteria_id', $criterion->id)
                ->value('score');
            
            if ($score !== null) {
                $weight = $criterion->weight / 100;
                $weightedScore = $score * $weight;
                $weightedSum += $weightedScore;
                $totalWeight += $weight;
                
                echo str_pad(number_format($score, 2), 17) . " │ ";
            } else {
                echo str_pad("N/A", 17) . " │ ";
            }
        }
        
        $judgeAvg = $totalWeight > 0 ? $weightedSum / $totalWeight : 0;
        $judgeAverages[] = $judgeAvg;
        
        echo str_pad(number_format($judgeAvg, 2), 15) . "\n";
    }
    
    echo str_repeat("─", 120) . "\n";
    echo str_pad("CALCULATION:", 20) . " │ ";
    foreach ($criteria as $criterion) {
        echo str_pad("×" . ($criterion->weight / 100), 17) . " │ ";
    }
    echo str_pad("= Judge Score", 15) . "\n";
    echo str_repeat("─", 120) . "\n";
    
    // Final calculation
    $finalScore = count($judgeAverages) > 0 ? array_sum($judgeAverages) / count($judgeAverages) : 0;
    
    echo "\n";
    echo "  JUDGE SCORES:  " . implode(' + ', array_map(fn($s) => number_format($s, 2), $judgeAverages)) . "\n";
    echo "  FINAL SCORE:   (" . implode(' + ', array_map(fn($s) => number_format($s, 2), $judgeAverages)) . ") ÷ " . count($judgeAverages) . " = " . number_format($finalScore, 2) . "\n";
    echo "\n";
}

echo str_repeat("═", 120) . "\n";
echo "                                 FINAL RANKINGS (MALE)                                  \n";
echo str_repeat("═", 120) . "\n\n";

// Calculate all scores and rank
$allScores = [];
foreach ($males as $contestant) {
    $finalScore = $scoreService->calculateContestantRoundScore($contestant, $casualRound, $pageant);
    $allScores[] = [
        'id' => $contestant->id,
        'number' => $contestant->number,
        'name' => $contestant->name,
        'score' => $finalScore ?? 0
    ];
}

// Sort by score descending
usort($allScores, fn($a, $b) => $b['score'] <=> $a['score']);

echo str_pad("RANK", 8) . str_pad("NUMBER", 10) . str_pad("NAME", 30) . str_pad("FINAL SCORE", 15) . "ADVANCES?\n";
echo str_repeat("─", 120) . "\n";

$topN = $casualRound->top_n_proceed;
foreach ($allScores as $index => $data) {
    $rank = $index + 1;
    $advances = ($rank <= $topN) ? "✓ YES" : "✗ No";
    $highlight = ($rank <= $topN) ? ">>> " : "    ";
    
    echo $highlight . 
         str_pad($rank, 8) . 
         str_pad("#{$data['number']}", 10) . 
         str_pad(substr($data['name'], 0, 28), 30) . 
         str_pad(number_format($data['score'], 2), 15) . 
         $advances . "\n";
}

echo "\n";
echo str_repeat("═", 120) . "\n";
echo "CONCLUSION: Top {$topN} males who should advance to the final round:\n";
for ($i = 0; $i < min($topN, count($allScores)); $i++) {
    echo "  " . ($i + 1) . ". {$allScores[$i]['name']} - " . number_format($allScores[$i]['score'], 2) . "\n";
}
echo str_repeat("═", 120) . "\n";
