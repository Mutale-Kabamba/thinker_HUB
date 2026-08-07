<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class QueuedVerifyEmail extends VerifyEmail
{
    public function __construct(private readonly ?string $signerName = null) {}

    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);
        $rawName = trim((string) ($notifiable->name ?? ''));
        $firstName = $rawName !== '' ? (explode(' ', $rawName)[0] ?? $rawName) : '';
        $greeting = $firstName !== '' ? "Hello {$firstName}!" : "Hello!";
        $signerName = trim((string) ($this->signerName ?: config('app.name', 'Thinker HUB')));

        return (new MailMessage)
            ->subject(__('Verify Email Address'))
            ->greeting($greeting)
            ->line(__('Please click the button below to verify your email address.'))
            ->action(__('Verify Email Address'), $verificationUrl)
            ->line(__('If you did not create an account, no further action is required.'))
            ->salutation(__('Regards,')."\n".$signerName);
    }
}
