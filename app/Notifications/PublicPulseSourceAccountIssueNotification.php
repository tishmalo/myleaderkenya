<?php

namespace App\Notifications;

use App\Models\PublicPulseSourceAccount;
use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PublicPulseSourceAccountIssueNotification extends Notification implements ShouldQueue
{
    use Queueable, UsesEmailTemplate;

    public int $tries = 3;

    public function __construct(
        private PublicPulseSourceAccount $account
    ) {}

    public function via(object $notifiable): array
    {
        return $this->template('public-pulse-issue') ? ['mail'] : [];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $template = $this->template('public-pulse-issue');

        $map = [
            '{session_label}' => $this->account->label,
            '{provider}' => $this->account->provider,
            '{status}' => str_replace('_', ' ', $this->account->status),
            '{issue}' => $this->account->last_error_message ?: 'No detailed message was provided.',
            '{sessions_url}' => route('public-pulse.x-sessions.index'),
        ];

        return (new MailMessage)
            ->subject($this->fill($template['subject'], $map))
            ->view('emails.notification', [
                'body' => $this->fill($template['body'], $map),
            ]);
    }
}
