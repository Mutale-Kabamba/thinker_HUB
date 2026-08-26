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

    public function __construct(
        private readonly string $studentName,
        private readonly string $itemTitle,
        private readonly string $courseName,
        private readonly string $itemType = 'assignment',
    ) {}

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
            ->subject('New Submission: '.$this->itemTitle)
            ->markdown('emails.student-submission', [
                'studentName' => $this->studentName,
                'itemTitle' => $this->itemTitle,
                'courseName' => $this->courseName,
                'itemType' => $this->itemType,
                'notifiable' => $notifiable,
                'recipientName' => $this->resolveRecipientName($notifiable),
                'signerName' => $this->resolveSignerName(),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $url = match ($notifiable->role ?? null) {
            'admin' => $this->itemType === 'assessment' ? '/manage/assessment-submissions' : '/manage/assignment-submissions',
            'instructor' => $this->itemType === 'assessment' ? '/teach/assessment-submissions' : '/teach/assignment-submissions',
            default => '/learn/overview',
        };

        return FilamentNotification::make()
            ->title('New '.$this->itemType.' submission')
            ->body($this->studentName.' submitted '.$this->itemTitle.' for '.$this->courseName)
            ->actions([
                Action::make('view')
                    ->label('View submissions')
                    ->url($url),
            ])
            ->getDatabaseMessage();
    }
}
