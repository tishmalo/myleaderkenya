<?php

namespace App\Notifications;

use App\Notifications\Concerns\UsesEmailTemplate;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    use UsesEmailTemplate;

    public function via($notifiable): array
    {
        return $this->template('password-reset') ? ['mail'] : [];
    }

    public function toMail($notifiable): MailMessage
    {
        $template = $this->template('password-reset');

        $map = [
            '{reset_url}' => url(route('password.reset', [
                'token' => $this->token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false)),
            '{expires_in}' => (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60),
        ];

        return (new MailMessage)
            ->subject($this->fill($template['subject'], $map))
            ->view('emails.notification', [
                'body' => $this->fill($template['body'], $map),
            ]);
    }
}
