<?php

namespace App\Notifications;

use App\Models\PublicPulseSourceAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PublicPulseSourceAccountIssueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        private PublicPulseSourceAccount $account
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Public Pulse X session needs attention')
            ->greeting('Hello,')
            ->line('A Public Pulse X session needs replacement or review.')
            ->line('Session: '.$this->account->label)
            ->line('Provider: '.$this->account->provider)
            ->line('Status: '.str_replace('_', ' ', $this->account->status))
            ->line('Issue: '.($this->account->last_error_message ?: 'No detailed message was provided.'))
            ->action('Review Public Pulse Sessions', route('public-pulse.x-sessions.index'))
            ->line('The session has been removed from the active scraper pool until it is healthy again.');
    }
}
