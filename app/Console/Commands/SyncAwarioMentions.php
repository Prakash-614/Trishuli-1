<?php

namespace App\Console\Commands;

use App\Services\AwarioService;
use Illuminate\Console\Command;

class SyncAwarioMentions extends Command
{
    protected $signature = 'awario:sync {alert_id? : Optional alert ID to sync} {--days=2 : Number of past days to sync (default: 2)}';
    protected $description = 'Fetch mentions from Awario API into the database';

    public function handle(AwarioService $awario): int
    {
        $alertId = $this->argument('alert_id') ?: config('services.awario.alert_id');

        if (!$alertId) {
            $this->error('AWARIO_ALERT_ID is not set in .env and no alert ID was provided.');
            return self::FAILURE;
        }

       $days = (int) $this->option('days');
        $this->info("Syncing mentions for alert {$alertId} (from past {$days} days)...");

        $count = $awario->syncMentions((int) $alertId, function ($page, $batchCount, $totalSoFar) {
            $this->line("  → Page {$page}: fetched {$batchCount} mentions (Total so far: {$totalSoFar})");
        }, $days);

        $this->info("Done. Synced {$count} mentions.");

        return self::SUCCESS;
    }
}