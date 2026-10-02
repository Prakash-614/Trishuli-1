<?php

namespace App\Http\Controllers;

use App\Mail\DailyReportMail;
use App\Models\AwarioMention;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ReportExportController extends Controller
{
    /**
     * Build report data for a specific date.
     */
    protected function getReportData(?string $date = null): array
    {
        // 1. If date is not provided or has no mentions, pick the latest date with data
        if (!$date || AwarioMention::whereDate('mentioned_at', $date)->count() === 0) {
            $latest = AwarioMention::max('mentioned_at');
            $date = $latest ? Carbon::parse($latest, 'Asia/Kathmandu')->toDateString() : Carbon::today('Asia/Kathmandu')->toDateString();
        }

        // 2. Fetch mentions for this date (limit to top 40 to prevent browser timeout)
        $mentions = AwarioMention::whereDate('mentioned_at', $date)
            ->orderByRaw("
                CASE 
                    WHEN risk_tier LIKE 'RED%' THEN 1 
                    WHEN risk_tier LIKE '%HIGH%' THEN 2 
                    WHEN risk_tier LIKE 'YELLOW%' THEN 3 
                    ELSE 4 
                END
            ")
            ->orderByDesc('mentioned_at')
            ->limit(40)
            ->get();

        $redCount = $mentions->filter(fn($m) => str_starts_with($m->risk_tier ?? '', 'RED'))->count();
        $yellowCount = $mentions->filter(fn($m) => str_starts_with($m->risk_tier ?? '', 'YELLOW'))->count();
        $greenCount = $mentions->filter(fn($m) => str_starts_with($m->risk_tier ?? '', 'GREEN'))->count();

        $highestRisk = $redCount > 0 ? 'RED (Critical)' : ($yellowCount > 0 ? 'YELLOW (Elevated)' : 'GREEN (Normal)');

        return compact('mentions', 'date', 'redCount', 'yellowCount', 'greenCount', 'highestRisk');
    }

    /**
     * Download the PDF report directly.
     */
    public function downloadPdf(Request $request)
    {
        ini_set('max_execution_time', 120);
        ini_set('memory_limit', '256M');

        $date = $request->query('date');
        $data = $this->getReportData($date);

        $pdf = Pdf::loadView('reports.monitoring-12pm', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
                'chroot' => base_path(),
            ]);

        return $pdf->download("Daily_Risk_Report_{$data['date']}.pdf");
    }

    /**
     * Generate the PDF and email it to the client with attachment.
     */
    public function emailReport(Request $request)
    {
        $date = $request->input('date') ?: Carbon::today('Asia/Kathmandu')->toDateString();

        // Priority: Form input -> config/services.php alert email -> env fallback
        $recipient = $request->filled('email')
            ? $request->input('email')
            : (config('services.alert.email') ?: env('ALERT_EMAIL') ?: env('CLIENT_REPORT_EMAIL'));

        if (!$recipient) {
            return back()->with('error', 'No recipient email configured. Please set ALERT_EMAIL in your .env file or specify an email.');
        }

        $data = $this->getReportData($date);

        try {
           $pdfBinary = Pdf::loadView('reports.monitoring-12pm', $data)
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                    'defaultFont' => 'DejaVu Sans',
                    'chroot' => base_path(),
                ])
                ->output();

            $recipients = array_filter(array_map('trim', explode(',', $recipient)));

            Mail::to($recipients)->send(new DailyReportMail(
                $date,
                $pdfBinary,
                $data['mentions']->count(),
                $data['highestRisk']
            ));

            return back()->with('success', "Report for {$date} successfully sent to " . implode(', ', $recipients) . " with PDF attachment.");
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Failed sending daily report email: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Mail server error: ' . $e->getMessage());
        }
    }
}