<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Checking pageant ID 20...\n\n";

$pageant = DB::table('pageants')->where('id', 20)->first();

if ($pageant) {
    echo "ID: {$pageant->id}\n";
    echo "Name: {$pageant->name}\n";
    echo "Ranking Method: {$pageant->ranking_method}\n";
    echo "Updated At: {$pageant->updated_at}\n";
} else {
    echo "Pageant ID 20 not found\n";
}
