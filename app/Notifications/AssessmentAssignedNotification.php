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

    public string $courseName;

    public function __construct(
        private readonly Assessment $assessment,
        ?string $courseName = null,
    ) {
        $this->courseName = $courseName ?? $assessment->course?->title ?? 'your course';
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
        $assessmentName = $this->assessment->name ?? $this->assessment->title ?? 'Assessment';

        return (new MailMessage)
            ->subject('New Assessment: '.$assessmentName)
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
        $assessmentName = $this->assessment->name ?? $this->assessment->title ?? 'Assessment';

        return FilamentNotification::make()
            ->title('New assessment: '.$assessmentName)
            ->body('A new assessment has been assigned for '.$this->courseName)
            ->actions([
                Action::make('view')
                    ->label('View assessment')
                    ->url('/learn/assessments')
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }
}
