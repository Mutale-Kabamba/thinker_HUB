<?php

namespace App\Notifications;

use App\Models\CourseSession;
use App\Notifications\Concerns\ResolvesMailPersonalization;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class SessionRescheduledNotification extends Notification
{
    use Queueable, ResolvesMailPersonalization;

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
            ->subject('Session Moved: '.$this->courseName)
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
        $dateFormatted = $this->session->getEffectiveDate()->format('D, M j');
        $startTime = $this->session->getEffectiveStartTime();
        $timeFormatted = filled($startTime) ? Carbon::parse($startTime)->format('g:i A') : '';

        return FilamentNotification::make()
            ->title('Session schedule updated')
            ->body($this->courseName.': moved to '.$dateFormatted.($timeFormatted ? ' at '.$timeFormatted : ''))
            ->actions([
                Action::make('view')
                    ->label('View schedule')
                    ->url('/learn/schedule'),
            ])
            ->getDatabaseMessage();
    }
}
