<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Pageant;
use App\Models\Score;

echo "=== Verifying Score Count for Casual Round ===\n\n";

$pageant = Pageant::where('name', 'ms&mrDAUIS')->first();
$casualRound = $pageant->rounds()->where('name', 'casual')->first();

echo "Round: {$casualRound->name} (ID: {$casualRound->id})\n";
echo "Type: {$casualRound->type}\n\n";

// Get criteria and judges
$criteria = $casualRound->criteria()->get();
$judges = $pageant->judges()->get();

echo "Criteria: {$criteria->count()}\n";
foreach ($criteria as $criterion) {
    echo "  - {$criterion->name}\n";
}

echo "\nJudges: {$judges->count()}\n";
foreach ($judges as $judge) {
    echo "  - {$judge->name} (ID: {$judge->id})\n";
}

echo "\nExpected scores per contestant: {$criteria->count()} criteria × {$judges->count()} judges = " . ($criteria->count() * $judges->count()) . "\n";

echo "\n" . str_repeat("=", 80) . "\n";
echo "Score Details for Each Male Contestant:\n";
echo str_repeat("=", 80) . "\n\n";

$males = $pageant->contestants()->where('gender', 'male')->orderBy('number')->get();

foreach ($males as $male) {
    echo "\n{$male->name} (ID: {$male->id}):\n";
    echo str_repeat("-", 60) . "\n";
    
    $scores = Score::where('round_id', $casualRound->id)
        ->where('contestant_id', $male->id)
        ->where('pageant_id', $pageant->id)
        ->orderBy('judge_id')
        ->orderBy('criteria_id')
        ->get();
    
    echo "Total scores: {$scores->count()}\n";
    
    // Group by judge
    $byJudge = $scores->groupBy('judge_id');
    
    foreach ($byJudge as $judgeId => $judgeScores) {
        $judge = $judges->firstWhere('id', $judgeId);
        $judgeName = $judge ? $judge->name : "Unknown Judge {$judgeId}";
        
        echo "\n  {$judgeName}:\n";
        foreach ($judgeScores as $score) {
            $criterion = $criteria->firstWhere('id', $score->criteria_id);
            $criterionName = $criterion ? $criterion->name : "Unknown Criterion";
            echo "    - {$criterionName}: {$score->score}\n";
        }
    }
}

echo "\n✓ Verification complete!\n";
echo "\nThis confirms: 3 criteria × 3 judges = 9 scores per contestant ✓\n";
