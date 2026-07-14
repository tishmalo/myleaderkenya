<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class CandidateClaimLinkNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        private string $candidateName,
        private string $claimUrl,
        private Carbon $expiresAt
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Claim your Tuko Kadi aspirant account')
            ->greeting('Hello ' . $this->candidateName . ',')
            ->line('An admin has created an aspirant profile for you on Tuko Kadi.')
            ->line('Use the secure link below to set your password and claim your account.')
            ->action('Claim Aspirant Account', $this->claimUrl)
            ->line('This link expires on ' . $this->expiresAt->format('M j, Y H:i') . '.')
            ->line('If you did not expect this email, you can ignore it safely.');
    }
}
