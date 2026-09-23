<?php

namespace App\Mail;

use App\Models\AwarioMention;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RedAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public AwarioMention $mention;

    public function __construct(AwarioMention $mention)
    {
        $this->mention = $mention;
    }

    public function build()
    {
        return $this->subject('🔴 RED ALERT — Upper Trishuli-1 Monitoring')
            ->view('emails.red-alert');
    }
}