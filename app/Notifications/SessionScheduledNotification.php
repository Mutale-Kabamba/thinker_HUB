<?php

namespace App\Notifications;

use App\Models\CourseSession;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Notification;

class SessionScheduledNotification extends Notification
{
    public function __construct(
        private readonly CourseSession $session,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $courseName = $this->session->course?->title ?? 'Your course';
        $sessionTitle = filled($this->session->title) ? (string) $this->session->title : 'New session';

        $body = $courseName.' — '.$sessionTitle;

        if ($this->session->session_date) {
            $body .= ' on '.$this->session->session_date->format('D, M j, Y');
        }

        $time = trim((string) $this->session->start_time.' - '.(string) $this->session->end_time, ' -');

        if ($time !== '') {
            $body .= ' at '.$time;
        }

        return FilamentNotification::make()
            ->title('New session scheduled')
            ->body($body)
            ->actions([
                Action::make('view')
                    ->label('View schedule')
                    ->url('/learn/schedule'),
            ])
            ->getDatabaseMessage();
    }
}
