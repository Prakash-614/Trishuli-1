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
        // 1. If passed via CLI argument (e.g. php artisan awario:sync 12345), use it
        $rawAlertIds = $this->argument('alert_id') ?: config('services.awario.alert_id');

        if (!$rawAlertIds) {
            $this->error('AWARIO_ALERT_ID is not set in .env and no alert ID was provided.');
            return self::FAILURE;
        }

        // 2. Parse comma-separated IDs into an array
        $alertIds = array_filter(array_map('trim', explode(',', (string) $rawAlertIds)));

        $days = (int) $this->option('days');
        $grandTotal = 0;

        foreach ($alertIds as $alertId) {
            $alertIdInt = (int) $alertId;
            $this->info("\n=======================================================");
            $this->info("Syncing mentions for Alert ID [{$alertIdInt}] (past {$days} days)...");
            $this->info("=======================================================");

            try {
                $count = $awario->syncMentions($alertIdInt, function ($page, $batchCount, $totalSoFar) {
                    $this->line("  → Page {$page}: fetched {$batchCount} mentions (Total for this alert: {$totalSoFar})");
                }, $days);

                $this->info("✔ Finished Alert [{$alertIdInt}]: synced {$count} mentions.");
                $grandTotal += $count;
            } catch (\Throwable $e) {
                $this->error("✖ Error syncing alert [{$alertIdInt}]: " . $e->getMessage());
            }
        }

        $this->info("\nAll alerts completed! Total mentions synced across all alerts: {$grandTotal}");

        return self::SUCCESS;
    }
}
