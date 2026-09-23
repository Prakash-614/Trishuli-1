<?php

namespace App\Console\Commands;

use App\Mail\RedAlertMail;
use App\Models\AwarioMention;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckRedAlerts extends Command
{
    protected $signature = 'awario:check-red';
    protected $description = 'Check for new RED-tier mentions and alert';

    public function handle(): int
    {
        $newRed = AwarioMention::where('risk_tier', 'RED')
            ->whereNull('alert_sent_at')
            ->get();

        if ($newRed->isEmpty()) {
            return self::SUCCESS;
        }

        foreach ($newRed as $mention) {
            Mail::to(config('services.alert.email'))->send(new \App\Mail\RedAlertMail($mention));
            $mention->update(['alert_sent_at' => now()]);
        }

        $this->info("Sent alerts for {$newRed->count()} RED mentions.");
        return self::SUCCESS;
    }
}