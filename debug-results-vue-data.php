<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Pageant;
use App\Services\ScoreCalculationService;

echo "=== Simulating What Results.vue Receives for Casual Round ===\n\n";

$pageant = Pageant::where('name', 'ms&mrDAUIS')->first();
$scoreService = app(ScoreCalculationService::class);

$casualRound = $pageant->rounds()->where('name', 'casual')->first();

echo "Pageant: {$pageant->name}\n";
echo "Round: {$casualRound->name} (ID: {$casualRound->id})\n";
echo "Display Order: {$casualRound->display_order}\n\n";

// This simulates what the TabulatorController::results() does
$orderedRounds = $pageant->rounds->sortBy('display_order');

// Get all rounds up to and including the casual round
$roundsUpToCasual = $orderedRounds->filter(function ($r) use ($casualRound) {
    return $r->display_order <= $casualRound->display_order;
});

echo "Rounds included in calculation (up to casual):\n";
foreach ($roundsUpToCasual as $r) {
    echo "  {$r->display_order}. {$r->name} (type: {$r->type}, weight: {$r->weight})\n";
}
echo "\n";

// Calculate what would be sent to Results.vue
$males = $pageant->contestants()->where('gender', 'male')->get();
$roundContestants = [];

foreach ($males as $contestant) {
    $roundScores = [];
    $totalWeightedScore = 0;
    $totalRoundWeight = 0;

    foreach ($roundsUpToCasual as $round) {
        $roundScore = $scoreService->calculateContestantRoundScore($contestant, $round, $pageant);

        if ($roundScore !== null) {
            $roundScores[$round->name] = $roundScore;
            $roundWeight = $round->weight ?? 1;
            if ($roundWeight <= 0) {
                $roundWeight = 1;
            }
            $totalWeightedScore += $roundScore * $roundWeight;
            $totalRoundWeight += $roundWeight;
        }
    }

    $finalScore = $totalRoundWeight > 0 ? $totalWeightedScore / $totalRoundWeight : 0;

    $roundContestants[] = [
        'id' => $contestant->id,
        'name' => $contestant->name,
        'number' => $contestant->number,
        'round_scores' => $roundScores,
        'totalScore' => round($finalScore, 2),
    ];
}

// Sort by score
usort($roundContestants, fn ($a, $b) => $b['totalScore'] <=> $a['totalScore']);

// Add ranks
foreach ($roundContestants as $rankIndex => &$contestant) {
    $contestant['rank'] = $rankIndex + 1;
}
unset($contestant);

// Apply filtering based on previous STAGE's top_n_proceed
$previousStageType = $scoreService->getPreviousStageType($pageant, $casualRound);

echo "Previous stage type: " . ($previousStageType ?? 'NONE') . "\n\n";

if ($previousStageType !== null) {
    $advancedContestants = $scoreService->getAdvancingContestantIds($pageant, $previousStageType);
    echo "Advanced contestants from {$previousStageType}: " . implode(', ', $advancedContestants) . "\n\n";
    
    if (!empty($advancedContestants)) {
        $roundContestants = array_filter($roundContestants, function ($c) use ($advancedContestants) {
            return in_array($c['id'], $advancedContestants);
        });
        $roundContestants = array_values($roundContestants);

        // Re-rank after filtering
        foreach ($roundContestants as $rankIndex => &$contestant) {
            $contestant['rank'] = $rankIndex + 1;
        }
        unset($contestant);
        
        echo "⚠️ FILTERED: Only " . count($roundContestants) . " contestants remain after filtering\n\n";
    }
}

echo "=== RESULTS SENT TO Results.vue FOR CASUAL ROUND ===\n";
echo str_pad("Rank", 6) . str_pad("Name", 30);
foreach ($roundsUpToCasual as $r) {
    echo str_pad(substr($r->name, 0, 12), 14);
}
echo str_pad("Total", 12) . "\n";
echo str_repeat("-", 100) . "\n";

foreach ($roundContestants as $contestant) {
    echo str_pad($contestant['rank'], 6) . str_pad(substr($contestant['name'], 0, 28), 30);
    foreach ($roundsUpToCasual as $r) {
        $score = $contestant['round_scores'][$r->name] ?? 0;
        echo str_pad(number_format($score, 2), 14);
    }
    echo str_pad(number_format($contestant['totalScore'], 2), 12) . "\n";
}

echo "\n=== ISSUE ANALYSIS ===\n";
if (count($roundsUpToCasual) > 1) {
    echo "⚠️ PROBLEM: The casual round includes scores from " . count($roundsUpToCasual) . " rounds!\n";
    echo "   This is accumulating scores from previous rounds (production number, evening gown)\n";
    echo "   and combining them with the casual round scores.\n\n";
    echo "   This is WHY the rankings look different from just the casual round alone!\n";
} else {
    echo "✓ Only showing casual round scores\n";
}

echo "\n=== COMPARISON ===\n";
echo "What you THOUGHT you were seeing (casual round only):\n";
$casualOnly = $scoreService->calculatePageantStageScores($pageant, 'Semi-Final', false);
$malesOnly = array_filter($casualOnly, fn($c) => $c['gender'] === 'male');
usort($malesOnly, fn($a, $b) => ($b['finalScore'] ?? 0) <=> ($a['finalScore'] ?? 0));

$count = 1;
foreach ($malesOnly as $m) {
    echo "  {$count}. {$m['name']} - " . number_format($m['finalScore'] ?? 0, 2) . "\n";
    $count++;
}

echo "\nWhat Results.vue is ACTUALLY showing (accumulated up to casual):\n";
foreach ($roundContestants as $c) {
    echo "  {$c['rank']}. {$c['name']} - " . number_format($c['totalScore'], 2) . "\n";
}
