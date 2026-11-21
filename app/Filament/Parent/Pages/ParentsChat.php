<?php

namespace App\Filament\Parent\Pages;

use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Models\Course;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;

class ParentsChat extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = 'Community Chat & Forums';

    protected static ?string $title = 'Parents Community Chat';

    protected static ?int $navigationSort = 4;

    protected static string|\UnitEnum|null $navigationGroup = 'PARENT COMMUNITY';

    protected string $view = 'filament.parent.pages.parents-chat';

    #[Url(as: 'room')]
    public ?int $selectedRoomId = null;

    #[Url(as: 'direct')]
    public ?int $directPeerParentId = null;

    public string $search = '';

    public string $filter = 'all'; // all | forums | direct

    public string $newMessage = '';

    public array $forumRooms = [];

    public array $directRooms = [];

    public array $mutualParents = [];

    public bool $showNewChatModal = false;

    public function mount(): void
    {
        $parent = auth()->user();

        if (! $parent) {
            return;
        }

        $this->ensureForumRoomsExist($parent);
        $this->handleDirectQueryParam($parent);
        $this->loadRoomsAndPeers();

        // If no room is selected yet, select the first available forum
        if (! $this->selectedRoomId && ! empty($this->forumRooms)) {
            $this->selectedRoomId = $this->forumRooms[0]['id'];
        }
    }

    /**
     * Ensure course forum rooms exist for all courses enrolled by the parent's children.
     */
    protected function ensureForumRoomsExist(User $parent): void
    {
        $kidIds = $parent->kids()->pluck('users.id')->all();

        if (empty($kidIds)) {
            return;
        }

        $kidCourses = Course::query()
            ->whereHas('enrollments', fn ($q) => $q->whereIn('user_id', $kidIds))
            ->get();

        foreach ($kidCourses as $course) {
            $room = ChatRoom::firstOrCreate(
                [
                    'type' => 'course_parents',
                    'course_id' => $course->id,
                ],
                [
                    'name' => $course->title . ' — Parents Forum',
                    'created_by' => $parent->id,
                ]
            );

            // Ensure current parent is a member
            $room->members()->syncWithoutDetaching([$parent->id]);
        }
    }

    /**
     * Handle direct message query parameter from Course Parents Circle.
     */
    protected function handleDirectQueryParam(User $parent): void
    {
        if (! $this->directPeerParentId) {
            return;
        }

        $isMutual = $parent->getCoursePeerParents()->contains('id', $this->directPeerParentId);

        if (! $isMutual && ! $parent->isAdmin()) {
            Notification::make()
                ->title('Direct Messaging Restricted')
                ->body('You can only message mutual parents whose children share a course with your child.')
                ->warning()
                ->send();

            $this->directPeerParentId = null;
            return;
        }

        $room = ChatRoom::findOrCreateDirect($parent->id, $this->directPeerParentId);
        $this->selectedRoomId = $room->id;
    }

    public function loadRoomsAndPeers(): void
    {
        $parent = auth()->user();

        if (! $parent) {
            return;
        }

        // 1. Fetch Course Parent Forums
        $this->forumRooms = $parent->chatRooms()
            ->where('chat_rooms.type', 'course_parents')
            ->with(['course', 'latestMessage.user'])
            ->get()
            ->map(fn (ChatRoom $r): array => [
                'id' => $r->id,
                'name' => $r->displayNameFor($parent),
                'course_title' => $r->course?->title,
                'course_code' => $r->course?->code,
                'avatar' => $r->avatarUrlFor($parent),
                'type' => 'forum',
                'latest_message' => $r->latestMessage?->body,
                'latest_time' => $r->latestMessage?->created_at?->diffForHumans(short: true),
                'unread' => $r->latestMessage && ($r->pivot->last_read_at === null || $r->latestMessage->created_at->gt($r->pivot->last_read_at)),
            ])
            ->all();

        // 2. Fetch Direct Message Rooms (Mutual Parents Only)
        $mutualParentIds = $parent->getCoursePeerParents()->pluck('id')->all();

        $this->directRooms = $parent->chatRooms()
            ->where('chat_rooms.type', 'direct')
            ->whereHas('members', fn ($q) => $q->whereIn('users.id', $mutualParentIds))
            ->with(['members', 'latestMessage.user'])
            ->get()
            ->map(function (ChatRoom $r) use ($parent): array {
                $other = $r->otherMemberFor($parent);

                return [
                    'id' => $r->id,
                    'name' => $other?->name ?? 'Parent',
                    'peer_id' => $other?->id,
                    'email' => $other?->email,
                    'avatar' => $other?->getFilamentAvatarUrl(),
                    'type' => 'direct',
                    'latest_message' => $r->latestMessage?->body,
                    'latest_time' => $r->latestMessage?->created_at?->diffForHumans(short: true),
                    'unread' => $r->latestMessage && ($r->pivot->last_read_at === null || $r->latestMessage->created_at->gt($r->pivot->last_read_at)),
                ];
            })
            ->all();

        // 3. Fetch Mutual Parents List for New Chat Dialog
        $this->mutualParents = $parent->getCoursePeerParents()
            ->map(function (User $p): array {
                $kidNames = $p->kids->pluck('name')->map(fn ($n) => explode(' ', trim($n))[0])->join(', ');

                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'email' => $p->email,
                    'avatar' => $p->getFilamentAvatarUrl(),
                    'children' => $kidNames ? "Parent of {$kidNames}" : 'Parent',
                ];
            })
            ->all();
    }

    public function selectRoom(int $roomId): void
    {
        $parent = auth()->user();

        if (! $parent) {
            return;
        }

        $room = $parent->chatRooms()->where('chat_rooms.id', $roomId)->first();

        if (! $room) {
            Notification::make()
                ->title('Unauthorized Room')
                ->body('You do not have permission to view this chat forum.')
                ->danger()
                ->send();

            return;
        }

        // If direct room, verify mutual parent condition
        if ($room->type === 'direct') {
            $other = $room->otherMemberFor($parent);
            if ($other && ! $parent->isAdmin() && ! $parent->getCoursePeerParents()->contains('id', $other->id)) {
                Notification::make()
                    ->title('Restricted Access')
                    ->body('You can only message mutual parents enrolled in the same course.')
                    ->danger()
                    ->send();

                return;
            }
        }

        $this->selectedRoomId = $roomId;

        // Mark as read
        $room->members()->updateExistingPivot($parent->id, [
            'last_read_at' => now(),
        ]);

        $this->loadRoomsAndPeers();
        $this->dispatch('chat-room-selected');
    }

    public function startDirectChat(int $peerParentId): void
    {
        $parent = auth()->user();

        if (! $parent) {
            return;
        }

        $isMutual = $parent->getCoursePeerParents()->contains('id', $peerParentId);

        if (! $isMutual && ! $parent->isAdmin()) {
            Notification::make()
                ->title('Access Denied')
                ->body('You can only message mutual parents whose children share a course with your child.')
                ->danger()
                ->send();

            return;
        }

        $room = ChatRoom::findOrCreateDirect($parent->id, $peerParentId);
        $this->showNewChatModal = false;
        $this->selectRoom($room->id);
    }

    public function sendMessage(): void
    {
        $parent = auth()->user();

        if (! $parent || ! $this->selectedRoomId) {
            return;
        }

        $body = trim($this->newMessage);

        if ($body === '') {
            return;
        }

        $room = $parent->chatRooms()->where('chat_rooms.id', $this->selectedRoomId)->first();

        if (! $room) {
            Notification::make()
                ->title('Error')
                ->body('Unable to post to this chat room.')
                ->danger()
                ->send();

            return;
        }

        ChatMessage::create([
            'chat_room_id' => $room->id,
            'user_id' => $parent->id,
            'body' => $body,
        ]);

        // Touch room and update last read for sender
        $room->touch();
        $room->members()->updateExistingPivot($parent->id, [
            'last_read_at' => now(),
        ]);

        $this->newMessage = '';
        $this->loadRoomsAndPeers();
        $this->dispatch('chat-message-sent');
    }

    public function getActiveRoomProperty(): ?ChatRoom
    {
        if (! $this->selectedRoomId) {
            return null;
        }

        $parent = auth()->user();

        return $parent?->chatRooms()
            ->where('chat_rooms.id', $this->selectedRoomId)
            ->with(['course', 'members'])
            ->first();
    }

    public function getMessagesProperty(): Collection
    {
        if (! $this->selectedRoomId) {
            return collect();
        }

        return ChatMessage::query()
            ->where('chat_room_id', $this->selectedRoomId)
            ->with(['user.kids'])
            ->orderBy('created_at', 'asc')
            ->limit(100)
            ->get();
    }
}
