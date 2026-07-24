<?php

namespace App\Notifications;

use App\Models\CourseSession;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Notification;

class RescheduleRequestSubmittedNotification extends Notification
{
    public function __construct(
        private readonly CourseSession $session,
        private readonly string $reason,
        private readonly ?string $preferredDate,
        private readonly ?string $preferredTime,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $courseName = $this->session->course->title ?? 'Course';
        $message = 'Request sent for '.$courseName.' on '.$this->session->session_date->format('M j, Y').'.';

        // Filament bell payload merged with the legacy keys the reschedule
        // decision workflow reads from stored notification data.
        return array_merge(
            FilamentNotification::make()
                ->title('Reschedule request submitted')
                ->body($message)
                ->actions([
                    Action::make('view')
                        ->label('View schedule')
                        ->url('/learn/schedule'),
                ])
                ->getDatabaseMessage(),
            [
                'type' => 'reschedule_request_submitted',
                'message' => $message,
                'session_id' => $this->session->id,
                'course_id' => $this->session->course_id,
                'reason' => $this->reason,
                'preferred_date' => $this->preferredDate,
                'preferred_time' => $this->preferredTime,
                'decision_status' => 'pending',
                'url' => '/learn/schedule',
            ],
        );
    }
}
