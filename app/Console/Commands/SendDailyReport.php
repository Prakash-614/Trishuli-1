<?php

namespace App\Console\Commands;

use App\Mail\DailyReportMail;
use App\Models\AwarioMention;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendDailyReport extends Command
{
    protected $signature = 'report:send-email 
                            {--slot= : Optional override: 12pm, 1pm, or 9pm} 
                            {--email= : Optional recipient override}';

    protected $description = 'Automated risk report sent at 12pm, 1pm, and 9pm with exact published-time filtering';

    public function handle(): int
    {
        $now = Carbon::now('Asia/Kathmandu');

        // 1. Determine which slot is running (auto-detect by current hour if not passed)
        if ($this->option('slot')) {
            $slot = strtolower($this->option('slot'));
        } else {
            $hour = (int) $now->format('H');
            if ($hour <= 12) {
                $slot = '12pm';
            } elseif ($hour <= 13) {
                $slot = '1pm';
            } else {
                $slot = '9pm';
            }
        }

        $recipient = $this->option('email')
            ?: config('services.alert.email')
            ?: env('ALERT_EMAIL');

        if (!$recipient) {
            $this->error('No recipient email specified in --email option or ALERT_EMAIL/.env');
            return self::FAILURE;
        }

        // 2. Define the exact publication time window for each slot
        $today = Carbon::today('Asia/Kathmandu');
        $formattedDate = strtoupper($today->format('d M Y')); // e.g. 29 SEP 2026

        if ($slot === '12pm') {
            // Strictly Yesterday 9:00 PM to Today 12:00 PM
            $from = $today->copy()->subDay()->setTime(21, 0, 0);
            $to   = $today->copy()->setTime(12, 0, 0);

            $viewName             = 'reports.monitoring-12pm';
            $attachmentFileName   = "SOCIAL MEDIA MONITORING - {$formattedDate} 12PM.pdf";
            $slotTime             = '12:00 PM';
            $timeWindowText       = "first published between 9:00 PM on " . $from->format('d F Y') . " and 12:00 PM on " . $to->format('d F Y') . " Nepal Time";
            $asOfText             = "As of " . $to->format('d F Y') . ", 12:00 PM (NPT)";
            $monitoringWindowText = "within the 9:00 PM (" . $from->format('d M') . ") to 12:00 PM (" . $to->format('d M') . ") Nepal Time monitoring window";
        } elseif ($slot === '1pm') {
            // Strictly Today 12:00 PM to Today 1:00 PM
            $from = $today->copy()->setTime(12, 0, 0);
            $to   = $today->copy()->setTime(13, 0, 0);

            $viewName             = 'reports.monitoring-detailed';
            $attachmentFileName   = "SOCIAL MEDIA MONITORING - {$formattedDate} 1PM.pdf";
            $slotTime             = '1:00 PM';
            $timeWindowText       = "first published between 12:00 PM and 1:00 PM on " . $today->format('d F Y') . " Nepal Time";
            $asOfText             = "As of " . $to->format('d F Y') . ", 1:00 PM (NPT)";
            $monitoringWindowText = "within the 12:00 PM to 1:00 PM Nepal Time monitoring window";
        } else {
            // Strictly Today 1:00 PM to Today 9:00 PM
            $from = $today->copy()->setTime(13, 0, 0);
            $to   = $today->copy()->setTime(21, 0, 0);

            $viewName             = 'reports.monitoring-detailed';
            $attachmentFileName   = "SOCIAL MEDIA MONITORING - {$formattedDate} 9PM.pdf";
            $slotTime             = '9:00 PM';
            $timeWindowText       = "first published between 1:00 PM and 9:00 PM on " . $today->format('d F Y') . " Nepal Time";
            $asOfText             = "As of " . $to->format('d F Y') . ", 9:00 PM (NPT)";
            $monitoringWindowText = "within the 1:00 PM to 9:00 PM Nepal Time monitoring window";
        }

        $this->info("Fetching mentions published strictly between [{$from}] and [{$to}]...");

        $mentions = AwarioMention::whereBetween('mentioned_at', [$from, $to])
            ->orderByRaw("
                CASE 
                    WHEN risk_tier LIKE 'RED%' THEN 1 
                    WHEN risk_tier LIKE '%HIGH%' THEN 2 
                    WHEN risk_tier LIKE 'YELLOW%' THEN 3 
                    ELSE 4 
                END
            ")
            ->orderByDesc('mentioned_at')
            ->get();

        $totalCount    = $mentions->count();
        $redCount      = $mentions->filter(fn($m) => str_starts_with($m->risk_tier ?? '', 'RED'))->count();
        $yellowCount   = $mentions->filter(fn($m) => str_starts_with($m->risk_tier ?? '', 'YELLOW'))->count();
        $greenCount    = $mentions->filter(fn($m) => str_starts_with($m->risk_tier ?? '', 'GREEN'))->count();

        $positiveCount = $mentions->filter(fn($m) => strtolower($m->sentiment ?? '') === 'positive')->count();
        $neutralCount  = $mentions->filter(fn($m) => in_array(strtolower($m->sentiment ?? ''), ['neutral', '', null]))->count();
        $negativeCount = $mentions->filter(fn($m) => strtolower($m->sentiment ?? '') === 'negative')->count();

        $positivePct   = $totalCount > 0 ? round(($positiveCount / $totalCount) * 100) : 0;
        $neutralPct    = $totalCount > 0 ? round(($neutralCount / $totalCount) * 100) : 0;
        $negativePct   = $totalCount > 0 ? round(($negativeCount / $totalCount) * 100) : 0;

        $highestRisk   = $redCount > 0 ? 'RED (Critical)' : ($yellowCount > 0 ? 'YELLOW (Elevated)' : 'GREEN (Normal)');
        $date          = $today->toDateString();

        $this->info("Found {$totalCount} mentions. Rendering '{$viewName}' -> '{$attachmentFileName}'...");

        $pdfBinary = Pdf::loadView($viewName, compact(
            'mentions',
            'date',
            'slotTime',
            'timeWindowText',
            'asOfText',
            'monitoringWindowText',
            'redCount',
            'yellowCount',
            'greenCount',
            'highestRisk',
            'positiveCount',
            'neutralCount',
            'negativeCount',
            'positivePct',
            'neutralPct',
            'negativePct'
        ))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => true,
                'defaultFont'          => 'DejaVu Sans',
                'chroot'               => base_path(),
            ])
            ->output();

        $recipients = array_filter(array_map('trim', explode(',', $recipient)));
        $this->info("Sending email to " . implode(', ', $recipients) . "...");

        Mail::to($recipients)->send(new DailyReportMail(
            $date,
            $pdfBinary,
            $totalCount,
            $highestRisk,
            $attachmentFileName,
            $slotTime,
            $timeWindowText
        ));

        $this->info("Success! [{$slot}] report sent with attachment '{$attachmentFileName}'.");
        return self::SUCCESS;
    }
}