<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Pageant;
use App\Models\Score;

echo "=== Investigating ALL Rounds for ms&mrDAUIS ===\n\n";

$pageant = Pageant::where('name', 'ms&mrDAUIS')->first();

echo "Pageant: {$pageant->name} (ID: {$pageant->id})\n\n";

$rounds = $pageant->rounds()->orderBy('display_order')->get();

echo "=== ALL ROUNDS ===\n";
foreach ($rounds as $round) {
    echo "\nRound ID: {$round->id}\n";
    echo "  Name: {$round->name}\n";
    echo "  Type: {$round->type}\n";
    echo "  Display Order: {$round->display_order}\n";
    echo "  Top N Proceed: " . ($round->top_n_proceed ?? 'NULL') . "\n";
    
    // Count scores in this round
    $scoreCount = Score::where('round_id', $round->id)
        ->where('pageant_id', $pageant->id)
        ->count();
    echo "  Scores Submitted: {$scoreCount}\n";
    
    // Get criteria
    $criteria = $round->criteria()->get();
    echo "  Criteria ({$criteria->count()}):\n";
    foreach ($criteria as $criterion) {
        echo "    - {$criterion->name} (weight: {$criterion->weight}%)\n";
    }
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "=== WHICH ROUND IS 'CASUAL' WITH TOP 3? ===\n";
echo str_repeat("=", 80) . "\n\n";

// Find the round that should have top 3
$casualRound = null;
foreach ($rounds as $round) {
    if (stripos($round->name, 'casual') !== false && $round->top_n_proceed == 3) {
        $casualRound = $round;
        break;
    }
}

if (!$casualRound) {
    echo "⚠️ Looking for any round with top_n_proceed = 3...\n";
    foreach ($rounds as $round) {
        if ($round->top_n_proceed == 3) {
            $casualRound = $round;
            echo "Found: {$round->name} (ID: {$round->id})\n";
            break;
        }
    }
}

if ($casualRound) {
    echo "\n✓ Found the casual round:\n";
    echo "  ID: {$casualRound->id}\n";
    echo "  Name: {$casualRound->name}\n";
    echo "  Type: {$casualRound->type}\n";
    echo "  Top N Proceed: {$casualRound->top_n_proceed}\n\n";
    
    // Get male contestants
    $males = $pageant->contestants()->where('gender', 'male')->get();
    
    echo "=== Male Contestants in this Round ===\n";
    
    foreach ($males as $male) {
        $scoreCount = Score::where('round_id', $casualRound->id)
            ->where('contestant_id', $male->id)
            ->where('pageant_id', $pageant->id)
            ->count();
        
        echo "{$male->name} (ID: {$male->id}) - Scores: {$scoreCount}\n";
    }
} else {
    echo "❌ Could not find the casual round!\n";
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "=== FINAL ROUND CHECK ===\n";
echo str_repeat("=", 80) . "\n\n";

$finalRound = $rounds->firstWhere('type', 'Final');
if ($finalRound) {
    echo "Final Round: {$finalRound->name} (ID: {$finalRound->id})\n";
    echo "Criteria:\n";
    foreach ($finalRound->criteria as $criterion) {
        echo "  - {$criterion->name}\n";
    }
    
    echo "\nMales with scores in FINAL round:\n";
    $males = $pageant->contestants()->where('gender', 'male')->get();
    foreach ($males as $male) {
        $scoreCount = Score::where('round_id', $finalRound->id)
            ->where('contestant_id', $male->id)
            ->where('pageant_id', $pageant->id)
            ->count();
        
        if ($scoreCount > 0) {
            echo "  - {$male->name} (ID: {$male->id}) - {$scoreCount} scores\n";
        }
    }
}

echo "\n✓ Investigation complete!\n";
