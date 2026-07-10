<?php

namespace App\Notifications\Concerns;

trait ResolvesMailPersonalization
{
    protected function resolveRecipientName(object $notifiable): string
    {
        $name = trim((string) ($notifiable->name ?? ''));

        return $name !== '' ? $name : 'there';
    }

    protected function resolveSignerName(): string
    {
        $sender = auth()->user();

        if (
            is_object($sender)
            && in_array((string) ($sender->role ?? ''), ['admin', 'instructor'], true)
            && filled($sender->name ?? null)
        ) {
            return (string) $sender->name;
        }

        return (string) config('app.name', 'Thinker HUB');
    }
}
