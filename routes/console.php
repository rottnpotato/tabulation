<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule automatic pageant status updates every 10 minutes
// This ensures pageants transition from Draft to Active when start date is reached
// and from Active to Completed when end date has passed
Schedule::command('pageants:update-status')->everyTenMinutes();
