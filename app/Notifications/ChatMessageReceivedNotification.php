<?php

namespace App\Notifications;

use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Notification;

class ChatMessageReceivedNotification extends Notification
{
    public function __construct(
        private readonly string $senderName,
        private readonly string $roomName,
        private readonly string $preview,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title('New message in '.$this->roomName)
            ->body($this->senderName.': '.$this->preview)
            ->actions([
                Action::make('view')
                    ->label('Open chat')
                    ->url('/learn/community'),
            ])
            ->getDatabaseMessage();
    }
}
