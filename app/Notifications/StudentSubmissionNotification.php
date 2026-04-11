<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StudentSubmissionNotification extends Notification implements ShouldQueue
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
        $channels = ['database'];

        if (filled($notifiable->email ?? null)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Student Submission Received')
            ->greeting('Hello '.$notifiable->name.',')
            ->line($this->studentName.' submitted '.$this->submissionType.': '.$this->itemTitle)
            ->action('Open Dashboard', route('dashboard'));
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
