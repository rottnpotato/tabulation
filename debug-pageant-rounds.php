<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Pageant;
use App\Models\Round;
use App\Models\Contestant;

echo "=== Investigating mr/msbohol Pageant ===\n\n";

$pageant = Pageant::where('name', 'mr/msbohol')->first();

if (!$pageant) {
    die("Pageant not found!\n");
}

echo "Pageant: {$pageant->name} (ID: {$pageant->id})\n";
echo "Contestant Type: {$pageant->contestant_type}\n\n";

echo "=== All Rounds ===\n";
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

echo "\n=== Checking Scores in Casual Round ===\n";
$casualRound = $rounds->firstWhere('name', 'casual');

if ($casualRound) {
    use App\Services\ScoreCalculationService;
    $scoreService = app(ScoreCalculationService::class);
    
    echo "Casual Round Top N: " . ($casualRound->top_n_proceed ?? 'NOT SET') . "\n\n";
    
    $stageResults = $scoreService->calculatePageantStageScores($pageant, 'semi-final', false);
    $maleResults = array_filter($stageResults, fn($c) => ($c['gender'] ?? '') === 'male');
    usort($maleResults, fn($a, $b) => ($b['finalScore'] ?? 0) <=> ($a['finalScore'] ?? 0));
    
    echo "Male Results (sorted by score):\n";
    foreach ($maleResults as $i => $result) {
        $rank = $i + 1;
        echo "{$rank}. {$result['name']} - Score: " . number_format($result['finalScore'] ?? 0, 2) . " (ID: {$result['id']})\n";
    }
}

echo "\n=== Checking Scores in Final Round ===\n";
$finalRound = $rounds->firstWhere('type', 'final');

if ($finalRound) {
    use App\Models\Score;
    
    echo "Final Round: {$finalRound->name}\n";
    echo "Final Round Top N: " . ($finalRound->top_n_proceed ?? 'NOT SET') . "\n\n";
    
    // Get all contestants who have scores in the final round
    $scoresInFinal = Score::where('round_id', $finalRound->id)
        ->where('pageant_id', $pageant->id)
        ->get()
        ->pluck('contestant_id')
        ->unique();
    
    echo "Contestants with scores in final round: " . $scoresInFinal->count() . "\n";
    
    $contestantsInFinal = $pageant->contestants()->whereIn('id', $scoresInFinal)->get();
    
    echo "\nMale Contestants in Final:\n";
    foreach ($contestantsInFinal->where('gender', 'male') as $contestant) {
        echo "- {$contestant->name} (ID: {$contestant->id})\n";
    }
}
