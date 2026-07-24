<?php

namespace App\Filament\Student\Pages;

use App\Events\ChatMessageSent;
use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Friendship;
use App\Models\User;
use App\Models\XpTransaction;
use App\Services\GamificationService;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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

    public const DIRECTORY_LIMIT = 50;

    #[Url(as: 'tab')]
    public string $tab = 'chats'; // chats | friends | leaderboard

    public string $directorySearch = '';

    /** @var array<string, mixed>|null */
    public ?array $profileUser = null;

    public ?int $selectedRoomId = null;

    public int $messagesLimit = 30;

    public bool $hasMoreMessages = false;

    public string $messageBody = '';

    public $attachment = null;

    public function mount(): void
    {
        $this->ensureCourseRooms();

        // Evaluate-on-read: the streak badge is checked lazily for the
        // viewing student (in addition to quiz-pass/certificate events) so
        // chat/submission/attendance activity can complete a streak without
        // observing four more models. Idempotent and cheap.
        $user = auth()->user();

        if ($user) {
            try {
                app(GamificationService::class)->evaluateStreak($user);
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }

    // ------------------------------------------------------------- Gamification

    /**
     * Top 20 students by XP. When the viewer falls outside the top 20
     * (including students with no XP yet), their row is appended with their
     * overall rank so they always see where they stand.
     *
     * @return array{rows: Collection<int, array<string, mixed>>, viewer: array<string, mixed>|null}
     */
    public function getLeaderboardProperty(): array
    {
        $ranked = app(GamificationService::class)->leaderboard();
        $top = $ranked->take(20)->values();
        $viewer = null;
        $user = auth()->user();

        if ($user) {
            $mine = $ranked->firstWhere('user_id', $user->id);

            if ($mine && $mine['rank'] > 20) {
                $viewer = $mine;
            } elseif (! $mine) {
                $viewer = [
                    'rank' => $ranked->count() + 1,
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'xp' => 0,
                    'badge_count' => $user->badges()->count(),
                    'badge_icons' => $user->badges()->orderBy('user_badge.earned_at')->limit(5)->pluck('icon')->filter()->values()->all(),
                ];
            }
        }

        return ['rows' => $top, 'viewer' => $viewer];
    }

    /**
     * Compact XP/badge summary chip for the viewing student.
     *
     * @return array{xp: int, badge_count: int, badge_icons: array<int, string>}
     */
    public function getMyXpProperty(): array
    {
        $user = auth()->user();

        if (! $user) {
            return ['xp' => 0, 'badge_count' => 0, 'badge_icons' => []];
        }

        return [
            'xp' => $user->xpTotal(),
            'badge_count' => $user->badges()->count(),
            'badge_icons' => $user->badges()->orderBy('user_badge.earned_at')->limit(5)->pluck('icon')->filter()->values()->all(),
        ];
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
            $groupName = $course->code ?: ($course->title.' — Group');

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

    /**
     * Browsable student directory (replaces the old search-first flow):
     * every student except the viewer, classmates first — ordered by shared
     * course count DESC then name — then everyone else alphabetically.
     * Six grouped queries total, no N+1: enrollments-by-course, students,
     * XP sums, badge icons, and the viewer's friendships.
     *
     * @return array{rows: Collection<int, array<string, mixed>>, total: int, shown: int}
     */
    public function getDirectoryProperty(): array
    {
        $user = auth()->user();

        if (! $user) {
            return ['rows' => collect(), 'total' => 0, 'shown' => 0];
        }

        // Classmates: students sharing >=1 enrolled course, with course names.
        $viewerCourseIds = Enrollment::query()->where('user_id', $user->id)->pluck('course_id');

        $sharedByStudent = collect();

        if ($viewerCourseIds->isNotEmpty()) {
            $sharedByStudent = Enrollment::query()
                ->join('courses', 'courses.id', '=', 'enrollments.course_id')
                ->whereIn('enrollments.course_id', $viewerCourseIds)
                ->where('enrollments.user_id', '!=', $user->id)
                ->get(['enrollments.user_id', 'courses.title'])
                ->groupBy('user_id')
                ->map(fn ($rows): array => [
                    'count' => $rows->pluck('title')->unique()->count(),
                    'courses' => $rows->pluck('title')->unique()->sort()->values()->all(),
                ]);
        }

        $students = User::query()
            ->where('role', 'student')
            ->where('id', '!=', $user->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        $studentIds = $students->pluck('id')->all();

        $xpByUser = XpTransaction::query()
            ->whereIn('user_id', $studentIds)
            ->groupBy('user_id')
            ->selectRaw('user_id, SUM(points) as xp')
            ->pluck('xp', 'user_id');

        $badgeIcons = DB::table('user_badge')
            ->join('badges', 'badges.id', '=', 'user_badge.badge_id')
            ->whereIn('user_badge.user_id', $studentIds)
            ->orderBy('user_badge.earned_at')
            ->get(['user_badge.user_id', 'badges.icon'])
            ->groupBy('user_id')
            ->map(fn ($rows) => $rows->pluck('icon')->filter()->take(3)->values()->all());

        $friendshipByUser = Friendship::query()
            ->where(fn ($q) => $q->where('user_id', $user->id)->orWhere('friend_id', $user->id))
            ->get(['id', 'user_id', 'friend_id', 'status'])
            ->mapWithKeys(fn (Friendship $f): array => [
                ($f->user_id === $user->id ? $f->friend_id : $f->user_id) => $this->friendshipState($f, $user->id),
            ]);

        // Collection is name-sorted; sortByDesc is stable, so classmates
        // come first (shared DESC) and ties stay alphabetical.
        $rows = $students
            ->map(fn (User $s): array => [
                'id' => $s->id,
                'name' => $s->name,
                'shared_count' => $sharedByStudent[$s->id]['count'] ?? 0,
                'shared_courses' => $sharedByStudent[$s->id]['courses'] ?? [],
                'xp' => (int) ($xpByUser[$s->id] ?? 0),
                'badge_icons' => $badgeIcons[$s->id] ?? [],
                'friendship' => $friendshipByUser[$s->id] ?? ['state' => 'none', 'friendship_id' => null],
            ])
            ->sortByDesc('shared_count')
            ->values();

        $term = mb_strtolower(trim($this->directorySearch));

        if ($term !== '') {
            $rows = $rows->filter(fn (array $row): bool => str_contains(mb_strtolower($row['name']), $term))->values();
        }

        $total = $rows->count();
        $shown = min($total, self::DIRECTORY_LIMIT);

        return [
            'rows' => $rows->take(self::DIRECTORY_LIMIT)->values(),
            'total' => $total,
            'shown' => $shown,
        ];
    }

    /**
     * Open the student profile modal. Students only — any other id
     * (or a missing user) aborts quietly.
     */
    public function showProfile(int $userId): void
    {
        $viewer = auth()->user();

        if (! $viewer) {
            return;
        }

        $target = User::query()->where('role', 'student')->find($userId);

        if (! $target) {
            return;
        }

        $viewerCourseIds = Enrollment::query()->where('user_id', $viewer->id)->pluck('course_id');

        $sharedCourses = $viewerCourseIds->isEmpty()
            ? collect()
            : Course::query()
                ->whereIn('id', $viewerCourseIds)
                ->whereHas('enrollments', fn ($q) => $q->where('user_id', $target->id))
                ->orderBy('title')
                ->pluck('title');

        $friendship = Friendship::query()
            ->where(fn ($q) => $q->where('user_id', $viewer->id)->where('friend_id', $target->id))
            ->orWhere(fn ($q) => $q->where('user_id', $target->id)->where('friend_id', $viewer->id))
            ->first();

        $this->profileUser = [
            'id' => $target->id,
            'name' => $target->name,
            'role_label' => 'Student',
            'bio' => $target->bio,
            'avatar' => $target->getFilamentAvatarUrl(),
            'xp' => $target->xpTotal(),
            'badges' => $target->badges()->orderBy('user_badge.earned_at')->get()
                ->map(fn ($b): array => ['icon' => $b->icon, 'name' => $b->name, 'description' => $b->description])
                ->all(),
            'badge_count' => $target->badges()->count(),
            'courses_count' => $target->courses()->count(),
            'shared_courses' => $sharedCourses->all(),
            'friendship' => $friendship ? $this->friendshipState($friendship, $viewer->id) : ['state' => 'none', 'friendship_id' => null],
            'is_self' => $target->id === $viewer->id,
        ];
    }

    public function closeProfile(): void
    {
        $this->profileUser = null;
    }

    /**
     * Friendship state between the viewer and the other party of a
     * friendship row: friends | sent (viewer requested) | incoming.
     *
     * @return array{state: string, friendship_id: int}
     */
    private function friendshipState(Friendship $friendship, int $viewerId): array
    {
        $state = match (true) {
            $friendship->status === 'accepted' => 'friends',
            $friendship->user_id === $viewerId => 'sent',
            default => 'incoming',
        };

        return ['state' => $state, 'friendship_id' => $friendship->id];
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

        $this->refreshOpenProfile();
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

        $this->refreshOpenProfile();
    }

    public function declineRequest(int $friendshipId): void
    {
        $user = auth()->user();

        Friendship::query()
            ->where('id', $friendshipId)
            ->where('friend_id', $user?->id)
            ->where('status', 'pending')
            ->delete();

        $this->refreshOpenProfile();
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

        $this->refreshOpenProfile();
    }

    /**
     * Re-resolve the open profile modal after a friendship action so its
     * action button reflects the new state (no-op when no modal is open).
     */
    private function refreshOpenProfile(): void
    {
        if ($this->profileUser) {
            $this->showProfile($this->profileUser['id']);
        }
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
