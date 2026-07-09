<?php

namespace App\Notifications;

use App\Models\CourseSession;
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

        return [
            'type' => 'reschedule_request_submitted',
            'title' => 'Reschedule request submitted',
            'message' => 'Request sent for '.$courseName.' on '.$this->session->session_date->format('M j, Y').'.',
            'session_id' => $this->session->id,
            'course_id' => $this->session->course_id,
            'reason' => $this->reason,
            'preferred_date' => $this->preferredDate,
            'preferred_time' => $this->preferredTime,
            'decision_status' => 'pending',
            'url' => '/learn/schedule',
        ];
    }
}
