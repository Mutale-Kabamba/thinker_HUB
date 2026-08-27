<?php

namespace App\Notifications;

use App\Notifications\Concerns\ResolvesMailPersonalization;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StudentSubmissionNotification extends Notification
{
    use Queueable, ResolvesMailPersonalization;

    public string $studentName;
    public string $submissionType;
    public string $itemTitle;
    public int|string|null $itemId;
    public ?string $courseName;

    public function __construct(
        string $studentName,
        string $submissionType = 'assignment',
        string $itemTitle = 'Submission',
        int|string|null $itemId = null,
        ?string $courseName = null,
    ) {
        $this->studentName = $studentName;
        $this->submissionType = $submissionType;
        $this->itemTitle = $itemTitle;
        $this->itemId = $itemId;
        $this->courseName = $courseName;
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
            ->subject('New Submission from '.$this->studentName)
            ->markdown('emails.student-submission', [
                'studentName' => $this->studentName,
                'itemTitle' => $this->itemTitle,
                'courseName' => $this->courseName ?? 'Course',
                'submissionType' => $this->submissionType,
                'itemType' => $this->submissionType,
                'notifiable' => $notifiable,
                'recipientName' => $this->resolveRecipientName($notifiable),
                'signerName' => $this->resolveSignerName(),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $url = match ($notifiable->role ?? null) {
            'admin' => $this->submissionType === 'assessment' ? '/manage/assessment-submissions' : '/manage/assignment-submissions',
            'instructor' => $this->submissionType === 'assessment' ? '/teach/assessment-submissions' : '/teach/assignment-submissions',
            default => '/learn/overview',
        };

        return FilamentNotification::make()
            ->title('New '.$this->submissionType.' submission')
            ->body($this->studentName.' submitted '.$this->itemTitle)
            ->actions([
                Action::make('view')
                    ->label('View submissions')
                    ->url($url),
            ])
            ->getDatabaseMessage();
    }
}
