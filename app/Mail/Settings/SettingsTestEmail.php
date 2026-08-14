<?php

namespace App\Mail\Settings;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SettingsTestEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly ?string $recipientName = null,
        public readonly ?string $mailerName = null,
        public readonly ?string $fromAddress = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Glamrush email configuration test',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.settings.test-email',
            with: [
                'recipientName' => $this->recipientName,
                'mailer' => $this->mailerName,
                'fromAddress' => $this->fromAddress,
                'sentAt' => now(),
            ],
        );
    }
}

