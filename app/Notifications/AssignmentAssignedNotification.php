<?php

namespace App\Notifications;

use App\Models\Assignment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AssignmentAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Assignment $assignment)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'assignment_assigned',
            'title' => 'New assignment assigned',
            'message' => $this->assignment->name,
            'assignment_id' => $this->assignment->id,
            'course_id' => $this->assignment->course_id,
            'due_date' => $this->assignment->due_date?->format('Y-m-d'),
        ];
    }
}
