<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
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
        $channels = ['database'];

        if (filled($notifiable->email ?? null)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Submission Was Reviewed')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Your '.$this->submissionType.' was reviewed: '.$this->itemTitle)
            ->line('Score/Grade: '.($this->scoreOrGrade !== null ? (string) $this->scoreOrGrade : 'N/A'))
            ->line('Feedback: '.($this->feedback !== '' ? $this->feedback : 'No feedback provided.'))
            ->action('Open Dashboard', route('dashboard'));
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
