<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('smtp:reset')->daily();
Schedule::command('app:reset-smtp-hourly')->hourly();
Schedule::command('app:fetch-email-replies')->everyFiveMinutes();
Schedule::command('app:warmup-progress')->daily();
Schedule::command('inbox:health')->everyTenMinutes();
Schedule::command('app:reset-health-stats')->daily();
Schedule::command('spam:check')->everyFiveMinutes();
