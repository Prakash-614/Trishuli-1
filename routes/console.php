<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// 1. Sync new mentions from Awario and Google Alerts (Minute :00, :15, :30, :45)
Schedule::command('awario:sync')->everyFifteenMinutes();
Schedule::command('alerts:sync-google')->everyFifteenMinutes();

// 2. Automatically fetch full raw article content (Minute :03, :18, :33, :48)
Schedule::command('awario:fetch-content')->cron('3,18,33,48 * * * *');

// 3. Classify with AI using the fetched raw content (Minute :06, :21, :36, :51)
Schedule::command('awario:classify')->cron('6,21,36,51 * * * *');

// 4. Send email alert if any RED items appear (every 10 minutes)
Schedule::command('awario:check-red')->everyTenMinutes();