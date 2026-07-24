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
            ->title('Badge earned: '.$this->badge->name)
            ->body(($this->badge->icon ? $this->badge->icon.' ' : '').$this->badge->description)
            ->actions([
                Action::make('view')
                    ->label('View leaderboard')
                    ->url('/learn/community?tab=leaderboard'),
            ])
            ->getDatabaseMessage();
    }
}
