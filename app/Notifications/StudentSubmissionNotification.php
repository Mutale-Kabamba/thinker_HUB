<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StudentSubmissionNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $studentName,
        private readonly string $submissionType,
        private readonly string $itemTitle,
        private readonly int $itemId,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'student_submission',
            'title' => 'Student submission received',
            'message' => $this->studentName.' submitted '.$this->submissionType.': '.$this->itemTitle,
            'submission_type' => $this->submissionType,
            'item_id' => $this->itemId,
        ];
    }
}
