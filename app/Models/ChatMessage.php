<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_room_id',
        'user_id',
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
}
