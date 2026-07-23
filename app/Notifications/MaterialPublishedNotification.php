<?php

namespace App\Notifications;

use App\Models\LearningMaterial;
use App\Notifications\Concerns\ResolvesMailPersonalization;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaterialPublishedNotification extends Notification
{
    use ResolvesMailPersonalization;

    public function __construct(private readonly LearningMaterial $material) {}

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
            ->subject('New Material: '.$this->material->title)
            ->markdown('emails.material-published', [
                'material' => $this->material,
                'notifiable' => $notifiable,
                'recipientName' => $this->resolveRecipientName($notifiable),
                'signerName' => $this->resolveSignerName(),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title('New material available')
            ->body($this->material->title)
            ->actions([
                Action::make('view')
                    ->label('View materials')
                    ->url('/learn/materials'),
            ])
            ->getDatabaseMessage();
    }
}
