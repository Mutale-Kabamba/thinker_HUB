<?php

namespace App\Observers;

use App\Models\ChatMessage;
use App\Notifications\ChatMessageReceivedNotification;
use Illuminate\Support\Str;

class ChatMessageObserver
{
    public function created(ChatMessage $message): void
    {
        try {
            $room = $message->room()->with('members')->first();

            if (! $room) {
                return;
            }

            $senderName = (string) ($message->user?->name ?? 'Someone');

            $preview = filled($message->body)
                ? Str::limit(trim((string) preg_replace('/\s+/', ' ', strip_tags((string) $message->body))), 80)
                : 'Sent an attachment';

            foreach ($room->members as $member) {
                // Never notify the author of the message.
                if ($member->id === $message->user_id) {
                    continue;
                }

                try {
                    $member->notify(new ChatMessageReceivedNotification(
                        $senderName,
                        $room->displayNameFor($member),
                        $preview,
                    ));
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
