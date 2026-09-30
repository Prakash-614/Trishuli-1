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
        // 1. Only pick RED mentions published TODAY (same calendar day) that haven't been emailed yet
        $newRed = AwarioMention::where('risk_tier', 'LIKE', 'RED%')
            ->whereNull('alert_sent_at')
            ->whereDate('mentioned_at', '>=', now()->toDateString())
            ->get();

        // 2. Mark any older historical RED mentions (published before today) as processed without emailing
        AwarioMention::where('risk_tier', 'LIKE', 'RED%')
            ->whereNull('alert_sent_at')
            ->whereDate('mentioned_at', '<', now()->toDateString())
            ->update(['alert_sent_at' => now()]);

        if ($newRed->isEmpty()) {
            $this->info('No new RED mentions published today requiring email alert.');
            return self::SUCCESS;
        }

        // Split comma-separated emails into an array
        $recipients = array_filter(array_map('trim', explode(',', config('services.alert.email'))));

        foreach ($newRed as $mention) {
            Mail::to($recipients)->send(new \App\Mail\RedAlertMail($mention));
            $mention->update(['alert_sent_at' => now()]);
            sleep(1); // Optional: Sleep for a second to avoid overwhelming the mail server
        }

        $this->info("Sent alerts for {$newRed->count()} RED mentions published today.");
        return self::SUCCESS;
    }
}