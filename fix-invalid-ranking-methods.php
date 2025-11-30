<?php

/**
 * Fix invalid ranking_method values in the pageants table
 * Maps 'sum_of_ranks' to 'rank_sum' and 'average' to 'score_average'
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Fixing invalid ranking_method values in pageants table...\n\n";

// Map sum_of_ranks to rank_sum
$sumOfRanksCount = DB::table('pageants')
    ->where('ranking_method', 'sum_of_ranks')
    ->update(['ranking_method' => 'rank_sum']);

echo "Updated {$sumOfRanksCount} pageant(s) from 'sum_of_ranks' to 'rank_sum'\n";

// Map average to score_average
$averageCount = DB::table('pageants')
    ->where('ranking_method', 'average')
    ->update(['ranking_method' => 'score_average']);

echo "Updated {$averageCount} pageant(s) from 'average' to 'score_average'\n";

// Show current distribution
echo "\nCurrent ranking_method distribution:\n";
$distribution = DB::table('pageants')
    ->select('ranking_method', DB::raw('count(*) as count'))
    ->groupBy('ranking_method')
    ->get();

foreach ($distribution as $row) {
    echo "  - {$row->ranking_method}: {$row->count}\n";
}

echo "\nDone!\n";
