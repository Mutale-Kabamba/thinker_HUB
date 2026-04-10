<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SubmissionGradedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $submissionType,
        private readonly string $itemTitle,
        private readonly ?int $scoreOrGrade,
        private readonly string $feedback,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'submission_graded',
            'title' => 'Your '.$this->submissionType.' was reviewed',
            'message' => $this->itemTitle,
            'submission_type' => $this->submissionType,
            'score_or_grade' => $this->scoreOrGrade,
            'feedback' => $this->feedback,
        ];
    }
}
