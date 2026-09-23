<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('awario:sync')->everyFifteenMinutes();
Schedule::command('awario:classify')->cron('5,20,35,50 * * * *'); // 5 min after each sync
Schedule::command('alerts:sync-google')->everyFifteenMinutes();