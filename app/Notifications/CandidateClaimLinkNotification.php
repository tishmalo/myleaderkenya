<?php

namespace App\Notifications;

use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class CandidateClaimLinkNotification extends Notification implements ShouldQueue
{
    use Queueable, UsesEmailTemplate;

    public int $tries = 3;

    public function __construct(
        private string $candidateName,
        private string $claimUrl,
        private Carbon $expiresAt
    ) {}

    public function via(object $notifiable): array
    {
        return $this->template('candidate-claim-link') ? ['mail'] : [];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $template = $this->template('candidate-claim-link');

        $map = [
            '{candidate_name}' => $this->candidateName,
            '{claim_url}' => $this->claimUrl,
            '{expires_at}' => $this->expiresAt->format('M j, Y H:i'),
        ];

        return (new MailMessage)
            ->subject($this->fill($template['subject'], $map))
            ->view('emails.notification', [
                'body' => $this->fill($template['body'], $map),
            ]);
    }
}
