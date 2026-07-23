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
        $recipientName = trim((string) ($notifiable->name ?? ''));
        $signerName = trim((string) ($this->signerName ?: config('app.name')));

        return (new MailMessage)
            ->subject(__('Verify Email Address'))
            ->greeting(__('Hello, :name', [
                'name' => $recipientName !== '' ? $recipientName : __('there'),
            ]))
            ->line(__('Please click the button below to verify your email address.'))
            ->action(__('Verify Email Address'), $verificationUrl)
            ->line(__('If you did not create an account, no further action is required.'))
            ->salutation(__('Regards,')."\n".$signerName);
    }
}
