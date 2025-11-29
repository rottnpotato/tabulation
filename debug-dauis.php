<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Pageant;
use App\Models\Round;
use App\Models\Contestant;
use App\Services\ScoreCalculationService;
use App\Models\Score;

echo "=== Investigating ms&mrDAUIS Pageant ===\n\n";

$pageant = Pageant::where('name', 'ms&mrDAUIS')->first();

if (!$pageant) {
    die("Pageant not found!\n");
}

echo "Pageant: {$pageant->name} (ID: {$pageant->id})\n";
echo "Contestant Type: {$pageant->contestant_type}\n";
echo "Ranking Method: {$pageant->ranking_method}\n\n";

echo "=== All Rounds (ordered by display_order) ===\n";
$rounds = $pageant->rounds()->orderBy('display_order')->get();

foreach ($rounds as $round) {
    echo "Round ID: {$round->id}\n";
    echo "  Name: {$round->name}\n";
    echo "  Type: {$round->type}\n";
    echo "  Display Order: {$round->display_order}\n";
    echo "  Top N Proceed: " . ($round->top_n_proceed ?? 'NULL') . "\n";
    echo "  Weight: {$round->weight}\n";
    echo "\n";
}

echo "=== Male Contestants ===\n";
$males = $pageant->contestants()->where('gender', 'male')->orderBy('number')->get();

foreach ($males as $contestant) {
    echo "{$contestant->id}. {$contestant->name} (#{$contestant->number})\n";
}

// Find the casual round
$casualRound = $rounds->first(function($r) {
    return stripos($r->name, 'casual') !== false;
});

if (!$casualRound) {
    echo "\nNo 'casual' round found. Looking for semi-final rounds...\n";
    $casualRound = $rounds->firstWhere('type', 'semi-final');
}

if ($casualRound) {
    echo "\n=== Casual Round: {$casualRound->name} ===\n";
    echo "Type: {$casualRound->type}\n";
    echo "Top N Proceed: " . ($casualRound->top_n_proceed ?? 'NOT SET (NULL)') . "\n\n";
    
    $scoreService = app(ScoreCalculationService::class);
    
    // Calculate stage scores
    $stageResults = $scoreService->calculatePageantStageScores($pageant, $casualRound->type, false);
    $maleResults = array_filter($stageResults, fn($c) => ($c['gender'] ?? '') === 'male');
    
    // Sort by rank
    usort($maleResults, fn($a, $b) => ($a['rank'] ?? 999) <=> ($b['rank'] ?? 999));
    
    echo "Male Results in Casual Round:\n";
    echo str_pad("Rank", 6) . str_pad("Name", 35) . str_pad("Score", 12) . "ID\n";
    echo str_repeat("-", 65) . "\n";
    
    foreach ($maleResults as $result) {
        $rank = $result['rank'] ?? 'N/A';
        $name = $result['name'] ?? 'Unknown';
        $score = number_format($result['finalScore'] ?? 0, 2);
        $id = $result['id'] ?? 'N/A';
        
        $highlight = '';
        if (stripos($name, 'michael') !== false || stripos($name, 'garfield') !== false || 
            stripos($name, 'ken') !== false || stripos($name, 'marco') !== false) {
            $highlight = ' <---';
        }
        
        echo str_pad($rank, 6) . str_pad(substr($name, 0, 33), 35) . str_pad($score, 12) . $id . $highlight . "\n";
    }
    
    echo "\n=== Who Should Advance ===\n";
    if ($casualRound->top_n_proceed) {
        $advancingIds = $scoreService->getAdvancingContestantIds($pageant, $casualRound->type);
        echo "Top {$casualRound->top_n_proceed} advancing contestant IDs: " . implode(", ", $advancingIds) . "\n";
        
        $advancingMales = $pageant->contestants->filter(fn($c) => in_array($c->id, $advancingIds) && $c->gender === 'male');
        echo "\nAdvancing Males:\n";
        foreach ($advancingMales as $contestant) {
            echo "- {$contestant->name} (ID: {$contestant->id})\n";
        }
    } else {
        echo "⚠️ WARNING: top_n_proceed is NOT SET on the casual round!\n";
        echo "This means ALL contestants can proceed to the next round.\n";
    }
}

// Check final round
$finalRound = $rounds->firstWhere('type', 'final');

if ($finalRound) {
    echo "\n=== Final Round: {$finalRound->name} ===\n";
    echo "Top N Proceed: " . ($finalRound->top_n_proceed ?? 'NOT SET') . "\n\n";
    
    // Get all contestants who have scores in the final round
    $scoresInFinal = Score::where('round_id', $finalRound->id)
        ->where('pageant_id', $pageant->id)
        ->get()
        ->pluck('contestant_id')
        ->unique();
    
    echo "Contestants with scores in final round: " . $scoresInFinal->count() . "\n";
    
    $contestantsInFinal = $pageant->contestants()->whereIn('id', $scoresInFinal)->get();
    
    echo "\nMale Contestants who were scored in Final:\n";
    foreach ($contestantsInFinal->where('gender', 'male') as $contestant) {
        $highlight = '';
        if (stripos($contestant->name, 'michael') !== false || stripos($contestant->name, 'garfield') !== false || 
            stripos($contestant->name, 'ken') !== false || stripos($contestant->name, 'marco') !== false) {
            $highlight = ' <--- KEY PERSON';
        }
        echo "- {$contestant->name} (ID: {$contestant->id}){$highlight}\n";
    }
    
    // Check if the system would have allowed them
    $previousStageType = $scoreService->getPreviousStageType($pageant, $finalRound);
    if ($previousStageType) {
        echo "\nPrevious stage for final: {$previousStageType}\n";
        $eligibleIds = $scoreService->getAdvancingContestantIds($pageant, $previousStageType);
        
        if (empty($eligibleIds)) {
            echo "⚠️ No advancing contestants calculated (top_n_proceed likely not set)\n";
            echo "System allows ALL contestants to proceed!\n";
        } else {
            echo "Eligible contestant IDs: " . implode(", ", $eligibleIds) . "\n";
        }
    }
}

echo "\n=== DIAGNOSIS ===\n";
if ($casualRound && !$casualRound->top_n_proceed) {
    echo "❌ PROBLEM FOUND: The casual round has no top_n_proceed value set!\n";
    echo "   This allows ANY contestant to proceed to the final round.\n";
    echo "\n";
    echo "🔧 SOLUTION: Set top_n_proceed = 3 on the casual round.\n";
    echo "   SQL: UPDATE rounds SET top_n_proceed = 3 WHERE id = {$casualRound->id};\n";
}

echo "\n=== Debug Complete ===\n";
