<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// 1. Sync new mentions from Awario (every 15 minutes)
Schedule::command('awario:sync')->everyFifteenMinutes()->withoutOverlapping();

// 2. Automatically fetch full raw article content (Minute :03, :18, :33, :48)
Schedule::command('awario:fetch-content')->cron('3,18,33,48 * * * *')->withoutOverlapping();

// 3. Classify with AI using the fetched raw content (Minute :06, :21, :36, :51)
Schedule::command('awario:classify')->cron('6,21,36,51 * * * *')->withoutOverlapping();

// 4. Send email alert if any RED items appear (every 10 minutes)
Schedule::command('awario:check-red')->everyTenMinutes();

// 1 single scheduled cron job that runs at 12:00 PM, 1:00 PM, and 9:00 PM Nepal Time
Schedule::command('report:send-email')
    ->cron('0 12,13,21 * * *')
    ->timezone('Asia/Kathmandu')
    ->withoutOverlapping();