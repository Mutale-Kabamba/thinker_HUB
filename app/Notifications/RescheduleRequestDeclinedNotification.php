<?php

namespace App\Notifications;

use App\Models\CourseSession;
use App\Notifications\Concerns\ResolvesMailPersonalization;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RescheduleRequestDeclinedNotification extends Notification
{
    use ResolvesMailPersonalization;

    public function __construct(
        private readonly CourseSession $session,
        private readonly string $courseName,
        private readonly ?string $reason = null,
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (filled($notifiable->email ?? null)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Reschedule Request Declined: '.$this->courseName)
            ->markdown('emails.reschedule-request-declined', [
                'session' => $this->session,
                'courseName' => $this->courseName,
                'reason' => $this->reason,
                'notifiable' => $notifiable,
                'recipientName' => $this->resolveRecipientName($notifiable),
                'signerName' => $this->resolveSignerName(),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $body = 'Your reschedule request for '.$this->courseName.' was declined.';

        if (filled($this->reason)) {
            $body .= ' Reason: '.$this->reason;
        }

        return FilamentNotification::make()
            ->title('Reschedule request declined')
            ->body($body)
            ->actions([
                Action::make('view')
                    ->label('View schedule')
                    ->url('/learn/schedule'),
            ])
            ->getDatabaseMessage();
    }
}
