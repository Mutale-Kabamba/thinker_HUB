<?php

namespace App\Notifications;

use App\Models\Assignment;
use App\Notifications\Concerns\ResolvesMailPersonalization;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssignmentAssignedNotification extends Notification
{
    use ResolvesMailPersonalization;

    public function __construct(private readonly Assignment $assignment) {}

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
                'notifiable' => $notifiable,
                'recipientName' => $this->resolveRecipientName($notifiable),
                'signerName' => $this->resolveSignerName(),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $body = $this->assignment->name;

        if ($this->assignment->due_date) {
            $body .= ' — due '.$this->assignment->due_date->format('M j, Y');
        }

        return FilamentNotification::make()
            ->title('New assignment assigned')
            ->body($body)
            ->actions([
                Action::make('view')
                    ->label('View assignments')
                    ->url('/learn/assignments'),
            ])
            ->getDatabaseMessage();
    }
}
