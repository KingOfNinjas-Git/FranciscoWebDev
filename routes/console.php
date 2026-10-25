<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Needs the host's cron to run `php artisan schedule:run` every minute (see docs/OPERATIONS.md)
Schedule::command('site:check --quiet-ok')->everyFifteenMinutes();
