<?php

namespace App\Notifications;

use App\Models\CourseSession;
use App\Notifications\Concerns\ResolvesMailPersonalization;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SessionRescheduledNotification extends Notification
{
    use ResolvesMailPersonalization;

    public function __construct(
        private readonly CourseSession $session,
        private readonly string $courseName,
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
            ->subject('Session Rescheduled: '.$this->courseName)
            ->markdown('emails.session-rescheduled', [
                'session' => $this->session,
                'courseName' => $this->courseName,
                'notifiable' => $notifiable,
                'recipientName' => $this->resolveRecipientName($notifiable),
                'signerName' => $this->resolveSignerName(),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title('Session rescheduled')
            ->body($this->courseName.': moved to '.$this->session->rescheduled_date->format('D, M j')
                .' at '.$this->session->rescheduled_start_time)
            ->actions([
                Action::make('view')
                    ->label('View schedule')
                    ->url('/learn/schedule'),
            ])
            ->getDatabaseMessage();
    }
}
