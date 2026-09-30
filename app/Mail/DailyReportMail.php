<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DailyReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $reportDate;
    public string $pdfBinary;
    public int $totalMentions;
    public string $highestRisk;
    public string $fileName;

    public function __construct(string $reportDate, string $pdfBinary, int $totalMentions, string $highestRisk, ?string $fileName = null)
    {
        $this->reportDate    = $reportDate;
        $this->pdfBinary     = $pdfBinary;
        $this->totalMentions = $totalMentions;
        $this->highestRisk   = $highestRisk;
        $this->fileName      = $fileName ?: ('Risk_Monitoring_Report_' . str_replace('-', '', $reportDate) . '.pdf');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address('info@gtechvision.com', 'Gtech Vision'),
            subject: "Daily Risk Monitoring Report — {$this->reportDate}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.daily-report',
        );
    }

    public function attachments(): array
    {
        Log::info("Preparing attachment for daily report: {$this->fileName}");

        return [
            Attachment::fromData(fn() => $this->pdfBinary, $this->fileName)
                ->withMime('application/pdf'),
        ];
    }
}