<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class TestNotificationMail extends Mailable
{
    public function __construct(
        public string $subject,
        public string $body
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.notification',
            with: ['body' => $this->body],
        );
    }
}
