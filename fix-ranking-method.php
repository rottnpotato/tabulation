<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Pageant;
use Illuminate\Support\Facades\DB;

echo "=== Updating ms&mrDAUIS to use score_average ===\n\n";

$pageant = Pageant::where('name', 'ms&mrDAUIS')->first();

if (!$pageant) {
    die("Pageant not found!\n");
}

echo "Current ranking method: {$pageant->ranking_method}\n";

DB::table('pageants')
    ->where('id', $pageant->id)
    ->update(['ranking_method' => 'score_average']);

echo "Updated ranking method to: score_average\n\n";

// Clear cache
\Illuminate\Support\Facades\Cache::flush();
echo "Cache cleared.\n\n";

// Verify the change
$pageant = $pageant->fresh();
echo "Verified - New ranking method: {$pageant->ranking_method}\n";

echo "\n=== Testing with new method ===\n";
$scoreService = app(\App\Services\ScoreCalculationService::class);
$stageResults = $scoreService->calculatePageantStageScores($pageant, 'Semi-Final', false);

$maleResults = array_filter($stageResults, fn($c) => ($c['gender'] ?? '') === 'male');
usort($maleResults, fn($a, $b) => ($a['rank'] ?? 999) <=> ($b['rank'] ?? 999));

echo "\nMale Results (now sorted by score, highest wins):\n";
echo str_pad("Rank", 6) . str_pad("Name", 30) . str_pad("Score", 12) . "\n";
echo str_repeat("-", 50) . "\n";

foreach ($maleResults as $result) {
    echo str_pad($result['rank'] ?? 'N/A', 6) . 
         str_pad(substr($result['name'] ?? 'Unknown', 0, 28), 30) . 
         str_pad(number_format($result['finalScore'] ?? 0, 2), 12) . "\n";
}

echo "\n=== Top 3 who should advance ===\n";
$advancingIds = $scoreService->getAdvancingContestantIds($pageant, 'Semi-Final');
$advancingMales = array_filter($stageResults, fn($c) => in_array($c['id'], $advancingIds) && ($c['gender'] ?? '') === 'male');

foreach ($advancingMales as $result) {
    echo "- {$result['name']} (Score: " . number_format($result['finalScore'] ?? 0, 2) . ")\n";
}

echo "\n✓ Done!\n";
