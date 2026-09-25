<?php

namespace App\Console\Commands;

use App\Services\GoogleAlertsService;
use Illuminate\Console\Command;

class SyncGoogleAlerts extends Command
{
    protected $signature = 'alerts:sync-google';
    protected $description = 'Fetch mentions from Google Alerts RSS feeds';

    public function handle(GoogleAlertsService $service): int
    {
        $this->info('Fetching Google Alerts feeds...');
        $count = $service->syncAll();
        $this->info("Alerts: processed {$count} items.");

        $this->info('Fetching Google News RSS...');
        $newsCount = $service->syncGoogleNews();
        $this->info("News: processed {$newsCount} items.");

        return self::SUCCESS;
    }
}
