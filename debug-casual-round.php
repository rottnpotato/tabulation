<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Pageant;
use App\Models\Round;
use App\Services\ScoreCalculationService;

// Find the casual round (assuming it's a semi-final type round)
echo "=== Debugging Casual Round Advancement Issue ===\n\n";

// Get all pageants and let user identify which one
$pageants = Pageant::with('rounds')->get();

echo "Available Pageants:\n";
foreach ($pageants as $pageant) {
    echo "{$pageant->id}. {$pageant->name}\n";
}

echo "\nSearching for 'casual' round...\n\n";

$casualRound = Round::where('name', 'LIKE', '%casual%')
    ->orWhere('type', 'LIKE', '%casual%')
    ->first();

if (!$casualRound) {
    echo "Looking for rounds with 'semi-final' or other types...\n";
    $rounds = Round::all();
    foreach ($rounds as $round) {
        echo "Round ID: {$round->id}, Name: {$round->name}, Type: {$round->type}, top_n_proceed: {$round->top_n_proceed}\n";
    }
    die("\nCould not find 'casual' round. Please specify round ID manually.\n");
}

$pageant = $casualRound->pageant;
$scoreService = app(ScoreCalculationService::class);

echo "Found Round:\n";
echo "- ID: {$casualRound->id}\n";
echo "- Name: {$casualRound->name}\n";
echo "- Type: {$casualRound->type}\n";
echo "- Top N Proceed: {$casualRound->top_n_proceed}\n";
echo "- Pageant: {$pageant->name}\n\n";

// Calculate stage scores for the casual round
$stageResults = $scoreService->calculatePageantStageScores($pageant, $casualRound->type, false);

echo "=== Casual Round Results (Males Only) ===\n";
$maleResults = array_filter($stageResults, fn($c) => ($c['gender'] ?? '') === 'male');

// Sort by rank
usort($maleResults, fn($a, $b) => ($a['rank'] ?? 999) <=> ($b['rank'] ?? 999));

echo str_pad("Rank", 6) . str_pad("Name", 30) . str_pad("Score", 12) . "ID\n";
echo str_repeat("-", 60) . "\n";

foreach ($maleResults as $result) {
    $rank = $result['rank'] ?? 'N/A';
    $name = $result['name'] ?? 'Unknown';
    $score = number_format($result['finalScore'] ?? 0, 2);
    $id = $result['id'] ?? 'N/A';
    
    echo str_pad($rank, 6) . str_pad(substr($name, 0, 28), 30) . str_pad($score, 12) . $id . "\n";
}

// Now check what the getAdvancingContestantIds returns
echo "\n=== Who Should Advance (Top {$casualRound->top_n_proceed}) ===\n";
$advancingIds = $scoreService->getAdvancingContestantIds($pageant, $casualRound->type);

echo "Advancing Contestant IDs: " . implode(", ", $advancingIds) . "\n\n";

// Get the names of advancing contestants
$advancingContestants = $pageant->contestants->filter(fn($c) => in_array($c->id, $advancingIds));

echo "Advancing Contestants:\n";
foreach ($advancingContestants as $contestant) {
    if ($contestant->gender === 'male') {
        echo "- {$contestant->name} (ID: {$contestant->id}, Gender: {$contestant->gender})\n";
    }
}

// Check if there's a final round and who is in it
echo "\n=== Checking Final Round ===\n";
$finalRound = $pageant->rounds->first(fn($r) => strtolower($r->type) === 'final');

if ($finalRound) {
    echo "Final Round: {$finalRound->name}\n";
    
    // Get eligible contestants for final round
    $previousStageType = $scoreService->getPreviousStageType($pageant, $finalRound);
    echo "Previous Stage Type: {$previousStageType}\n";
    
    if ($previousStageType) {
        $eligibleForFinal = $scoreService->getAdvancingContestantIds($pageant, $previousStageType);
        echo "Eligible for Final (based on {$previousStageType}): " . implode(", ", $eligibleForFinal) . "\n";
        
        $eligibleMales = $pageant->contestants
            ->filter(fn($c) => in_array($c->id, $eligibleForFinal) && $c->gender === 'male');
        
        echo "\nEligible Males for Final:\n";
        foreach ($eligibleMales as $contestant) {
            echo "- {$contestant->name} (ID: {$contestant->id})\n";
        }
    }
} else {
    echo "No final round found.\n";
}

echo "\n=== Debug Complete ===\n";
