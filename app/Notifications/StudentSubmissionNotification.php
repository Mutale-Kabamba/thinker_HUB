<?php

namespace App\Notifications;

use App\Notifications\Concerns\ResolvesMailPersonalization;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class StudentSubmissionNotification extends Notification
{
    use ResolvesMailPersonalization;

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

        $email = strtolower((string) ($notifiable->email ?? ''));

        if ($email !== '' && ! Str::endsWith($email, '@example.com')) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Submission from '.$this->studentName)
            ->markdown('emails.student-submission', [
                'studentName' => $this->studentName,
                'submissionType' => $this->submissionType,
                'itemTitle' => $this->itemTitle,
                'notifiable' => $notifiable,
                'recipientName' => $this->resolveRecipientName($notifiable),
                'signerName' => $this->resolveSignerName(),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $url = match ($notifiable->role ?? null) {
            'admin' => '/manage',
            'instructor' => '/teach/instructor-overview',
            default => '/learn/overview',
        };

        return [
            'type' => 'student_submission',
            'title' => 'Student submission received',
            'message' => $this->studentName.' submitted '.$this->submissionType.': '.$this->itemTitle,
            'submission_type' => $this->submissionType,
            'item_id' => $this->itemId,
            'url' => $url,
        ];
    }
}
