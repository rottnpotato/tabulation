<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule automatic pageant status updates every 5 minutes
// This ensures pageants transition from Draft to Ongoing promptly when start date is reached
Schedule::command('pageants:update-status')->everyFiveMinutes();
