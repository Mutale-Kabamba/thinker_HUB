<?php

namespace App\Filament\Student\Pages;

use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Models\Opportunity;
use App\Models\OpportunityReaction;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

class Opportunities extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';

    protected static string|\UnitEnum|null $navigationGroup = 'GROWTH & SOCIAL';

    protected static ?string $navigationLabel = 'Opportunities';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.student.pages.opportunities';

    /** @var array<int, array<string, mixed>> */
    public array $opportunities = [];

    /** @var array<int, string> */
    public array $types = [];

    #[Url(as: 'type')]
    public string $filterType = '';

    public ?int $openComments = null;

    /** @var array<int, array{id:int,name:string,avatar:?string}> */
    public array $shareFriends = [];

    public function mount(): void
    {
        $this->types = Opportunity::TYPES;
        $this->loadShareFriends();
        $this->loadOpportunities();
    }

    public function toggleComments(int $id): void
    {
        $this->openComments = $this->openComments === $id ? null : $id;
    }

    public function updatedFilterType(): void
    {
        $this->loadOpportunities();
    }

    public function toggleReaction(int $id, string $emoji): void
    {
        $user = auth()->user();
        $emoji = trim($emoji);

        if (! $user || $emoji === '' || mb_strlen($emoji) > 16) {
            return;
        }

        $existing = OpportunityReaction::query()
            ->where('opportunity_id', $id)
            ->where('user_id', $user->id)
            ->first();

        // One reaction per user per opportunity: same emoji toggles off,
        // different emoji replaces the previous one.
        if ($existing) {
            if ($existing->emoji === $emoji) {
                $existing->delete();
            } else {
                $existing->update(['emoji' => $emoji]);
            }
        } else {
            OpportunityReaction::create([
                'opportunity_id' => $id,
                'user_id' => $user->id,
                'emoji' => $emoji,
            ]);
        }

        $this->loadOpportunities();
    }

    public function shareToFriend(int $opportunityId, int $friendId): void
    {
        $user = auth()->user();

        if (! $user || ! $user->isFriendsWith($friendId)) {
            return;
        }

        $opportunity = Opportunity::query()->find($opportunityId);

        if (! $opportunity) {
            return;
        }

        $room = ChatRoom::findOrCreateDirect($user->id, $friendId);

        $messageBody = "Opportunity: {$opportunity->title}";

        if ($opportunity->link_url) {
            $messageBody .= "\n{$opportunity->link_url}";
        }

        ChatMessage::create([
            'chat_room_id' => $room->id,
            'user_id' => $user->id,
            'body' => $messageBody,
        ]);

        Notification::make()
            ->title('Shared with friend')
            ->success()
            ->send();
    }

    private function loadShareFriends(): void
    {
        $user = auth()->user();

        if (! $user) {
            $this->shareFriends = [];

            return;
        }

        $this->shareFriends = $user->friends()
            ->map(fn ($friend): array => [
                'id' => (int) $friend->id,
                'name' => (string) $friend->name,
                'avatar' => $friend->getFilamentAvatarUrl(),
            ])
            ->values()
            ->all();
    }

    private function loadOpportunities(): void
    {
        $query = Opportunity::query()->active();

        if ($this->filterType !== '') {
            $query->where('type', $this->filterType);
        }

        $this->opportunities = $query
            ->with('reactions.user:id,name')
            ->orderByRaw('expires_at IS NULL')
            ->orderBy('expires_at')
            ->latest()
            ->get()
            ->map(function (Opportunity $item): array {
                $extra = is_array($item->extra) ? $item->extra : [];
                $reactionSummary = $item->reactions
                    ->groupBy('emoji')
                    ->map(fn ($group, $emoji): array => [
                        'emoji' => $emoji,
                        'count' => $group->count(),
                        'mine' => (bool) $group->firstWhere('user_id', auth()->id()),
                        'users' => $group->pluck('user.name')->filter()->values()->all(),
                    ])
                    ->values()
                    ->all();

                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'type' => $item->type,
                    'description' => $item->description,
                    'link_url' => $item->link_url,
                    'promo_code' => $item->promo_code,
                    'provider' => $item->provider,
                    'expires_at' => $item->expires_at?->format('M d, Y'),
                    'extra' => $extra,
                    'reactions' => $reactionSummary,
                ];
            })
            ->values()
            ->all();
    }
}
