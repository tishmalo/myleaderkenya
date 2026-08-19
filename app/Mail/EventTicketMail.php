<?php

namespace App\Mail;

use App\Models\EventRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventTicketMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public EventRegistration $registration,
        public array $template
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->replacePlaceholders($this->template['subject'] ?? 'Your event ticket'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.event-ticket',
            with: [
                'body' => $this->replacePlaceholders($this->template['body'] ?? ''),
            ],
        );
    }

    private function replacePlaceholders(string $text): string
    {
        $event = $this->registration->event;

        return strtr($text, [
            '{attendee_name}' => $this->registration->name,
            '{event_title}' => $event->title,
            '{event_date}' => $event->date->format('l, F d, Y h:i A'),
            '{event_location}' => $event->location,
            '{amount}' => number_format((float) $this->registration->amount),
        ]);
    }
}
