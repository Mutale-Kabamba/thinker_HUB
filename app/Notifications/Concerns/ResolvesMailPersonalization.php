<?php

namespace App\Notifications\Concerns;

trait ResolvesMailPersonalization
{
    protected function resolveRecipientName(object $notifiable): string
    {
        $name = trim((string) ($notifiable->name ?? ''));

        if ($name === '') {
            return 'there';
        }

        $parts = preg_split('/\s+/', $name);

        return $parts[0] ?? $name;
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
