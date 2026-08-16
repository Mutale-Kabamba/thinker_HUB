<?php

namespace App\Notifications;

use App\Models\Assessment;
use App\Notifications\Concerns\ResolvesMailPersonalization;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssessmentAssignedNotification extends Notification
{
    use Queueable, ResolvesMailPersonalization;

    public function __construct(private readonly Assessment $assessment) {}

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
            ->subject('New Assessment: '.$this->assessment->name)
            ->markdown('emails.assessment-assigned', [
                'assessment' => $this->assessment,
                'notifiable' => $notifiable,
                'recipientName' => $this->resolveRecipientName($notifiable),
                'signerName' => $this->resolveSignerName(),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $body = $this->assessment->name;

        if ($this->assessment->due_date) {
            $body .= ' — due '.$this->assessment->due_date->format('M j, Y');
        }

        return FilamentNotification::make()
            ->title('New assessment assigned')
            ->body($body)
            ->actions([
                Action::make('view')
                    ->label('View assessments')
                    ->url('/learn/assessments')
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }
}
