<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$pageant = App\Models\Pageant::where('name', 'mr.ARDUINO')->first();
$rounds = $pageant->rounds->sortBy('display_order');

foreach ($rounds as $r) {
    echo $r->name . ' (' . $r->type . ') - top_n_proceed: ' . ($r->top_n_proceed ?? 'null') . PHP_EOL;
}
