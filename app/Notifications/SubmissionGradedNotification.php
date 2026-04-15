<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubmissionGradedNotification extends Notification
{

    public function __construct(
        private readonly string $submissionType,
        private readonly string $itemTitle,
        private readonly ?int $scoreOrGrade,
        private readonly string $feedback,
    ) {
    }

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
            ->subject('✅ Submission Reviewed: '.$this->itemTitle)
            ->markdown('emails.submission-graded', [
                'submissionType' => $this->submissionType,
                'itemTitle' => $this->itemTitle,
                'scoreOrGrade' => $this->scoreOrGrade,
                'feedback' => $this->feedback,
                'notifiable' => $notifiable,
            ]);
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
            'url' => '/learn/assessments',
        ];
    }
}
