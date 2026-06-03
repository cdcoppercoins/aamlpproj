<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable)
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Reset your MiniLicensePlates.com password')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('We received a request to reset the password for your member account.')
            ->line('Click the button below to choose a new password. This link expires in 60 minutes.')
            ->action('Reset password', $url)
            ->line('If you did not request a password reset, you can ignore this email. Your password will not change.');
    }
}
