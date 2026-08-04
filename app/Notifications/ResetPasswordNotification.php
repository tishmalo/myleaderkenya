<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Reset your My Leader Kenya password')
            ->view('emails.auth.password-reset', [
                'resetUrl' => $resetUrl,
                'expiresIn' => (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60),
            ]);
    }
}
