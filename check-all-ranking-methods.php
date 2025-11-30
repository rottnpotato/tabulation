<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Checking all pageants ranking methods...\n\n";

$pageants = DB::table('pageants')
    ->select('id', 'name', 'ranking_method', 'updated_at')
    ->orderBy('id')
    ->get();

foreach ($pageants as $pageant) {
    echo "ID {$pageant->id}: {$pageant->name} - [{$pageant->ranking_method}] (Updated: {$pageant->updated_at})\n";
}

echo "\n";
