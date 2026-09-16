<?php

namespace App\Console\Commands;

use App\Models\AwarioMention;
use App\Services\RiskClassificationService;
use Illuminate\Console\Command;

class ClassifyAwarioMentions extends Command
{
    protected $signature = 'awario:classify';
    protected $description = 'Classify unclassified mentions into Green/Yellow/Red risk tiers';

    public function handle(RiskClassificationService $classifier): int
{
    $mentions = AwarioMention::whereNull('classified_at')->get();

    if ($mentions->isEmpty()) {
        $this->info('No unclassified mentions found.');
        return self::SUCCESS;
    }

    $this->info("Classifying {$mentions->count()} mentions...");

    $success = 0;
    $failed = 0;

    foreach ($mentions as $mention) {
        $ok = $classifier->classify($mention);
        $ok ? $success++ : $failed++;
        $this->line("→ #{$mention->id}: " . ($ok ? $mention->fresh()->risk_tier : 'FAILED'));

        // Respect Gemini free-tier rate limits
        usleep(1_200_000); // 1.2 second delay between calls
    }

    $this->info("Done. Success: {$success}, Failed: {$failed}");

    return self::SUCCESS;
}
}