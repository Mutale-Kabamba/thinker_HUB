<?php

namespace App\Notifications;

use App\Notifications\Concerns\ResolvesMailPersonalization;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubmissionGradedNotification extends Notification
{
    use ResolvesMailPersonalization;

    public function __construct(
        private readonly string $submissionType,
        private readonly string $itemTitle,
        private readonly ?int $scoreOrGrade,
        private readonly string $feedback,
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
            ->subject('Submission Reviewed: '.$this->itemTitle)
            ->markdown('emails.submission-graded', [
                'submissionType' => $this->submissionType,
                'itemTitle' => $this->itemTitle,
                'scoreOrGrade' => $this->scoreOrGrade,
                'feedback' => $this->feedback,
                'notifiable' => $notifiable,
                'recipientName' => $this->resolveRecipientName($notifiable),
                'signerName' => $this->resolveSignerName(),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $body = $this->itemTitle.'.';

        if ($this->scoreOrGrade !== null) {
            $body .= ' Score: '.$this->scoreOrGrade.'.';
        }

        if (filled($this->feedback)) {
            $body .= ' '.$this->feedback;
        }

        return FilamentNotification::make()
            ->title('Your '.$this->submissionType.' was reviewed')
            ->body($body)
            ->actions([
                Action::make('view')
                    ->label('View '.$this->submissionType)
                    ->url($this->submissionType === 'assignment' ? '/learn/assignments' : '/learn/assessments'),
            ])
            ->getDatabaseMessage();
    }
}
