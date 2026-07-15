<?php

use App\Models\ChatRoom;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
| Only members of a chat room may listen on its private channel.
*/
Broadcast::channel('chat-room.{roomId}', function (User $user, int $roomId): bool {
    return ChatRoom::query()
        ->where('id', $roomId)
        ->whereHas('members', fn ($q) => $q->where('users.id', $user->id))
        ->exists();
});
