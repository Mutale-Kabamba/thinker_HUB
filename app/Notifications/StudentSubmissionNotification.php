<?php

namespace App\Notifications;

use App\Notifications\Concerns\ResolvesMailPersonalization;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class StudentSubmissionNotification extends Notification
{
    use Queueable, ResolvesMailPersonalization;

    public function __construct(
        private readonly string $studentName,
        private readonly string $submissionType,
        private readonly string $itemTitle,
        private readonly int $itemId,
    ) {}

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
            'admin' => $this->submissionType === 'assessment' ? '/manage/assessment-submissions' : '/manage/assignment-submissions',
            'instructor' => $this->submissionType === 'assessment' ? '/teach/assessment-submissions' : '/teach/assignment-submissions',
            default => '/learn/overview',
        };

        return FilamentNotification::make()
            ->title('Student submission received')
            ->body($this->studentName.' submitted '.$this->submissionType.': '.$this->itemTitle)
            ->actions([
                Action::make('view')
                    ->label('View submissions')
                    ->url($url)
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }
}
