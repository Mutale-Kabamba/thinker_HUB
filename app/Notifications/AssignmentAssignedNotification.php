<?php

namespace App\Notifications;

use App\Models\Assignment;
use App\Notifications\Concerns\ResolvesMailPersonalization;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssignmentAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable, ResolvesMailPersonalization;

    public function __construct(
        private readonly Assignment $assignment,
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
            ->subject('New Assignment: '.$this->assignment->name)
            ->markdown('emails.assignment-assigned', [
                'assignment' => $this->assignment,
                'courseName' => $this->courseName,
                'notifiable' => $notifiable,
                'recipientName' => $this->resolveRecipientName($notifiable),
                'signerName' => $this->resolveSignerName(),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title('New assignment: '.$this->assignment->name)
            ->body('A new assignment has been assigned for '.$this->courseName)
            ->actions([
                Action::make('view')
                    ->label('View assignment')
                    ->url('/learn/assignments'),
            ])
            ->getDatabaseMessage();
    }
}
