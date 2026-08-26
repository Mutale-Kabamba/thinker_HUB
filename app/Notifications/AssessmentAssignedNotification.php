<?php

namespace App\Notifications;

use App\Models\Assessment;
use App\Notifications\Concerns\ResolvesMailPersonalization;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssessmentAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable, ResolvesMailPersonalization;

    public function __construct(
        private readonly Assessment $assessment,
        private readonly string $courseName,
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
            ->subject('New Assessment: '.$this->assessment->title)
            ->markdown('emails.assessment-assigned', [
                'assessment' => $this->assessment,
                'courseName' => $this->courseName,
                'notifiable' => $notifiable,
                'recipientName' => $this->resolveRecipientName($notifiable),
                'signerName' => $this->resolveSignerName(),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title('New assessment: '.$this->assessment->title)
            ->body('A new assessment has been assigned for '.$this->courseName)
            ->actions([
                Action::make('view')
                    ->label('View assessment')
                    ->url('/learn/assessments'),
            ])
            ->getDatabaseMessage();
    }
}
