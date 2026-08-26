<?php

namespace App\Notifications;

use App\Models\Badge;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Notification;

class BadgeEarnedNotification extends Notification
{
    public function __construct(
        private readonly Badge $badge,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title('New Badge Earned!')
            ->body('You earned the '.$this->badge->name.' badge.')
            ->actions([
                Action::make('view')
                    ->label('View badges')
                    ->url('/learn/overview'),
            ])
            ->getDatabaseMessage();
    }
}
