<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CriticalAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $alertTitle,
        public string $alertMessage,
        public array $alertData = []
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🚨 ALERTA CRÍTICA: ' . $this->alertTitle,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.critical-alert',
            with: [
                'title' => $this->alertTitle,
                'message' => $this->alertMessage,
                'data' => $this->alertData,
                'timestamp' => now()
            ]
        );
    }
}