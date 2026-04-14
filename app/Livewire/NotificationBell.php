<?php

namespace App\Livewire;

use Illuminate\Notifications\DatabaseNotification;
use Livewire\Component;

class NotificationBell extends Component
{
    public bool $open = false;

    public function getUnreadCountProperty(): int
    {
        $user = auth()->user();

        return $user ? $user->unreadNotifications()->count() : 0;
    }

    public function getNotificationsProperty(): \Illuminate\Support\Collection
    {
        $user = auth()->user();

        if (! $user) {
            return collect();
        }

        return $user->notifications()->latest()->limit(20)->get();
    }

    public function togglePanel(): void
    {
        $this->open = ! $this->open;
    }

    public function markAsRead(string $notificationId): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $notification = $user->notifications()->where('id', $notificationId)->first();

        if ($notification && $notification->unread()) {
            $notification->markAsRead();
        }
    }

    public function markAllAsRead(): void
    {
        $user = auth()->user();

        if ($user) {
            $user->unreadNotifications->markAsRead();
        }
    }

    public function clearAll(): void
    {
        $user = auth()->user();

        if ($user) {
            $user->notifications()->delete();
        }
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.notification-bell');
    }
}
