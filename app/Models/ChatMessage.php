<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_room_id',
        'user_id',
        'reply_to_id',
        'body',
        'attachment_path',
        'attachment_name',
        'attachment_type',
    ];

    public function getAttachmentUrlAttribute(): ?string
    {
        return $this->attachment_path
            ? route('file.view', ['type' => 'chat-message', 'id' => $this->id], false)
            : null;
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(ChatRoom::class, 'chat_room_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reply_to_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'reply_to_id');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(ChatMessageReaction::class, 'chat_message_id');
    }

    /**
     * Group reactions by emoji with counts, reacted_by_me flag, and user names.
     *
     * @return array<int, array{emoji: string, count: int, reacted_by_me: bool, names: array<int, string>}>
     */
    public function getGroupedReactions(?int $currentUserId = null): array
    {
        $userId = $currentUserId ?? auth()->id();
        $reactions = $this->relationLoaded('reactions')
            ? $this->reactions
            : $this->reactions()->with('user')->get();

        if ($reactions->isEmpty()) {
            return [];
        }

        return $reactions
            ->groupBy('emoji')
            ->map(function ($group, string $emoji) use ($userId): array {
                $names = $group->map(fn (ChatMessageReaction $r) => $r->user?->name ?? 'Student')->filter()->values()->all();
                $reactedByMe = $userId ? $group->contains('user_id', $userId) : false;

                return [
                    'emoji' => $emoji,
                    'count' => $group->count(),
                    'reacted_by_me' => $reactedByMe,
                    'names' => $names,
                ];
            })
            ->values()
            ->all();
    }
}
