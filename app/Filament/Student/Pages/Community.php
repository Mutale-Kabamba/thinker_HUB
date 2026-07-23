<?php

namespace App\Filament\Student\Pages;

use App\Events\ChatMessageSent;
use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Models\Friendship;
use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use Livewire\WithFileUploads;

class Community extends Page
{
    use WithFileUploads;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static string|\UnitEnum|null $navigationGroup = 'GROWTH & SOCIAL';

    protected static ?string $navigationLabel = 'Community';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.student.pages.community';

    #[Url(as: 'tab')]
    public string $tab = 'chats'; // chats | friends

    public string $peopleSearch = '';

    public ?int $selectedRoomId = null;

    public int $messagesLimit = 30;

    public bool $hasMoreMessages = false;

    public string $messageBody = '';

    public $attachment = null;

    public function mount(): void
    {
        $this->ensureCourseRooms();
    }

    /**
     * Ensure a group room exists for each course the student is enrolled in,
     * and that the student is a member of it.
     */
    private function ensureCourseRooms(): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        foreach ($user->courses()->get() as $course) {
            $groupName = $course->code ?: ($course->title . ' — Group');

            $room = ChatRoom::firstOrCreate(
                ['type' => 'course', 'course_id' => $course->id],
                ['name' => $groupName],
            );

            if ($room->name !== $groupName) {
                $room->update(['name' => $groupName]);
            }

            $room->members()->syncWithoutDetaching([$user->id]);
        }
    }

    // ---------------------------------------------------------------- Friends

    public function getFriendsProperty(): Collection
    {
        return auth()->user()?->friends() ?? collect();
    }

    public function getPendingRequestsProperty(): Collection
    {
        $user = auth()->user();

        if (! $user) {
            return collect();
        }

        return Friendship::query()
            ->with('requester')
            ->where('friend_id', $user->id)
            ->where('status', 'pending')
            ->latest()
            ->get();
    }

    public function getSentRequestsProperty(): Collection
    {
        $user = auth()->user();

        if (! $user) {
            return collect();
        }

        return Friendship::query()
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->pluck('friend_id');
    }

    public function getPeopleResultsProperty(): Collection
    {
        $user = auth()->user();
        $term = trim($this->peopleSearch);

        if (! $user || mb_strlen($term) < 2) {
            return collect();
        }

        return User::query()
            ->where('id', '!=', $user->id)
            ->where(function ($q): void {
                $q->whereNull('role')->orWhereNotIn('role', ['admin', 'instructor']);
            })
            ->where('name', 'like', "%{$term}%")
            ->orderBy('name')
            ->limit(15)
            ->get();
    }

    public function sendRequest(int $userId): void
    {
        $user = auth()->user();

        if (! $user || $userId === $user->id) {
            return;
        }

        // Already friends or a request already exists in either direction.
        $exists = Friendship::query()
            ->where(function ($q) use ($user, $userId): void {
                $q->where('user_id', $user->id)->where('friend_id', $userId);
            })
            ->orWhere(function ($q) use ($user, $userId): void {
                $q->where('user_id', $userId)->where('friend_id', $user->id);
            })
            ->exists();

        if ($exists) {
            return;
        }

        Friendship::create([
            'user_id' => $user->id,
            'friend_id' => $userId,
            'status' => 'pending',
        ]);
    }

    public function acceptRequest(int $friendshipId): void
    {
        $user = auth()->user();

        $friendship = Friendship::query()
            ->where('id', $friendshipId)
            ->where('friend_id', $user?->id)
            ->where('status', 'pending')
            ->first();

        $friendship?->update(['status' => 'accepted']);
    }

    public function declineRequest(int $friendshipId): void
    {
        $user = auth()->user();

        Friendship::query()
            ->where('id', $friendshipId)
            ->where('friend_id', $user?->id)
            ->where('status', 'pending')
            ->delete();
    }

    public function removeFriend(int $userId): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        Friendship::query()
            ->where(function ($q) use ($user, $userId): void {
                $q->where('user_id', $user->id)->where('friend_id', $userId);
            })
            ->orWhere(function ($q) use ($user, $userId): void {
                $q->where('user_id', $userId)->where('friend_id', $user->id);
            })
            ->delete();
    }

    // ------------------------------------------------------------------ Chats

    public function getRoomsProperty(): Collection
    {
        $user = auth()->user();

        if (! $user) {
            return collect();
        }

        return $user->chatRooms()
            ->with(['members', 'latestMessage', 'course'])
            ->get()
            ->sortByDesc(fn (ChatRoom $room) => $room->latestMessage?->created_at ?? $room->created_at)
            ->values();
    }

    public function openDirect(int $userId): void
    {
        $user = auth()->user();

        if (! $user || ! $user->isFriendsWith($userId)) {
            return;
        }

        $room = ChatRoom::findOrCreateDirect($user->id, $userId);
        $this->selectedRoomId = $room->id;
        $this->messagesLimit = 30;
        $this->hasMoreMessages = false;
        $this->tab = 'chats';
    }

    public function openRoom(int $roomId): void
    {
        $user = auth()->user();

        // Only open rooms the user belongs to.
        if ($user && $user->chatRooms()->where('chat_rooms.id', $roomId)->exists()) {
            $this->selectedRoomId = $roomId;
            $this->messagesLimit = 30;
            $this->hasMoreMessages = false;
        }
    }

    public function loadMoreMessages(): void
    {
        if (! $this->activeRoom) {
            return;
        }

        $totalMessages = ChatMessage::query()
            ->where('chat_room_id', $this->activeRoom->id)
            ->count();

        $this->messagesLimit += 30;
        $this->hasMoreMessages = $this->messagesLimit < $totalMessages;
    }

    public function getActiveRoomProperty(): ?ChatRoom
    {
        if (! $this->selectedRoomId) {
            return null;
        }

        $user = auth()->user();

        return $user?->chatRooms()
            ->with(['members', 'course'])
            ->where('chat_rooms.id', $this->selectedRoomId)
            ->first();
    }

    public function getMessagesProperty(): Collection
    {
        if (! $this->activeRoom) {
            return collect();
        }

        $totalMessages = ChatMessage::query()
            ->where('chat_room_id', $this->activeRoom->id)
            ->count();

        $messages = ChatMessage::query()
            ->with('user')
            ->where('chat_room_id', $this->activeRoom->id)
            ->latest()
            ->limit($this->messagesLimit)
            ->get()
            ->reverse()
            ->values();

        $this->hasMoreMessages = $totalMessages > $this->messagesLimit;

        return $messages;
    }

    public function sendMessage(): void
    {
        $user = auth()->user();
        $body = trim($this->messageBody);

        if (! $user || ! $this->activeRoom) {
            return;
        }

        // Nothing to send.
        if ($body === '' && ! $this->attachment) {
            return;
        }

        if (mb_strlen($body) > 2000) {
            $body = mb_substr($body, 0, 2000);
        }

        $attachmentPath = null;
        $attachmentName = null;
        $attachmentType = null;

        if ($this->attachment) {
            $this->validate([
                'attachment' => [
                    'file',
                    'max:10240', // 10 MB
                    'mimes:jpg,jpeg,png,gif,webp,svg,pdf,doc,docx,ppt,pptx,xls,xlsx,txt,csv,zip',
                ],
            ]);

            $attachmentName = $this->attachment->getClientOriginalName();
            $attachmentPath = $this->attachment->store('chat-attachments', 'public');

            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
            $ext = strtolower($this->attachment->getClientOriginalExtension());
            $attachmentType = in_array($ext, $imageExtensions, true) ? 'image' : 'file';
        }

        $message = ChatMessage::create([
            'chat_room_id' => $this->activeRoom->id,
            'user_id' => $user->id,
            'body' => $body !== '' ? $body : null,
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'attachment_type' => $attachmentType,
        ]);

        $this->messageBody = '';
        $this->reset('attachment');

        if (class_exists(ChatMessageSent::class)) {
            ChatMessageSent::dispatch($message);
        }
    }

    /**
     * Livewire echo listeners: refresh when a message lands in the open room.
     * Only active once a real broadcaster (e.g. Reverb) is configured; until
     * then the message pane falls back to short polling.
     *
     * @return array<string, string>
     */
    public function getListeners(): array
    {
        if (! $this->selectedRoomId || ! in_array(config('broadcasting.default'), ['reverb', 'pusher', 'ably'], true)) {
            return [];
        }

        return [
            "echo-private:chat-room.{$this->selectedRoomId},ChatMessageSent" => '$refresh',
        ];
    }
}
