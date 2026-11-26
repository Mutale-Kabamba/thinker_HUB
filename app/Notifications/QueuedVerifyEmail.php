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
        $firstName = $rawName !== '' ? (explode(' ', $rawName)[0] ?? $rawName) : 'Learner';
        $signerName = trim((string) ($this->signerName ?: config('app.name', 'think.er HUB')));

        return (new MailMessage)
            ->subject(__('Verify Email Address — think.er HUB'))
            ->view('emails.verify-email', [
                'verificationUrl' => $verificationUrl,
                'firstName' => $firstName,
                'signerName' => $signerName,
            ]);
    }
}
