<?php

namespace App\Console\Commands;

use App\Services\AwarioService;
use Illuminate\Console\Command;

class SyncAwarioMentions extends Command
{
    protected $signature = 'awario:sync';
    protected $description = 'Fetch mentions from Awario API into the database';

    public function handle(AwarioService $awario): int
    {
        $alertId = config('services.awario.alert_id');

        if (!$alertId) {
            $this->error('AWARIO_ALERT_ID is not set in .env');
            return self::FAILURE;
        }

        $this->info("Syncing mentions for alert {$alertId}...");
        $count = $awario->syncMentions((int) $alertId);
        $this->info("Done. Synced {$count} mentions.");

        return self::SUCCESS;
    }
}