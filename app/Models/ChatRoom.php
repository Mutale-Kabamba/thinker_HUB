<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class ChatRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'course_id',
        'created_by',
    ];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'chat_room_user')
            ->withPivot('last_read_at')
            ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function latestMessage(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ChatMessage::class)->latestOfMany();
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Find (or create) the 1-on-1 direct room shared by exactly two users.
     */
    public static function findOrCreateDirect(int $userAId, int $userBId): self
    {
        $room = self::query()
            ->where('type', 'direct')
            ->whereHas('members', fn ($q) => $q->where('users.id', $userAId))
            ->whereHas('members', fn ($q) => $q->where('users.id', $userBId))
            ->whereDoesntHave('members', fn ($q) => $q->whereNotIn('users.id', [$userAId, $userBId]))
            ->first();

        if ($room) {
            return $room;
        }

        $room = self::create(['type' => 'direct']);
        $room->members()->syncWithoutDetaching([$userAId, $userBId]);

        return $room;
    }

    /**
     * Display name for a room relative to the given viewer.
     */
    public function displayNameFor(User $viewer): string
    {
        if ($this->type === 'course') {
            return $this->name ?? ($this->course?->title ? $this->course->title . ' — Group' : 'Course Group');
        }

        $other = $this->otherMemberFor($viewer);

        return $other?->name ?? 'Direct chat';
    }

    public function otherMemberFor(User $viewer): ?User
    {
        return $this->members->firstWhere('id', '!=', $viewer->id);
    }

    public function avatarUrlFor(User $viewer): ?string
    {
        if ($this->type === 'course') {
            return $this->course?->image_path
                ? Storage::disk('public')->url($this->course->image_path)
                : null;
        }

        return $this->otherMemberFor($viewer)?->getFilamentAvatarUrl();
    }
}
