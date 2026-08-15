<?php

namespace App\Livewire\ClaimHub;

use App\Models\ClaimItem;
use App\Models\ClaimRequest;
use App\Models\Course;
use App\Models\CourseGamificationRule;
use App\Models\User;
use App\Models\XpTransaction;
use App\Services\GamificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Storefront extends Component
{
    use WithPagination;

    public string $activeTab = 'rewards'; // 'rewards' | 'matrix'

    public string $selectedCategory = 'all';

    public string $selectedCourse = 'all';

    public ?int $matrixCourseId = null;

    public string $matrixCategory = 'all';

    public string $search = '';

    public ?ClaimItem $selectedItem = null;

    public string $phoneNumber = '';

    public string $deliveryNotes = '';

    public bool $showModal = false;

    public bool $showHistoryModal = false;

    public ?string $successMessage = null;

    public ?string $errorMessage = null;

    protected $queryString = [
        'activeTab' => ['except' => 'rewards'],
        'selectedCategory' => ['except' => 'all'],
        'selectedCourse' => ['except' => 'all'],
        'matrixCourseId' => ['except' => null],
        'matrixCategory' => ['except' => 'all'],
        'search' => ['except' => ''],
    ];

    public function mount(): void
    {
        /** @var User|null $user */
        $user = Auth::user();
        if ($user) {
            $enrolled = $user->courses()->pluck('courses.id')->all();
            if (! empty($enrolled) && ! $this->matrixCourseId) {
                $this->matrixCourseId = $enrolled[0];
            }
        }
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetErrorBag();
        $this->errorMessage = null;
        $this->successMessage = null;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedCategory(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedCourse(): void
    {
        $this->resetPage();
        if (is_numeric($this->selectedCourse)) {
            $this->matrixCourseId = (int) $this->selectedCourse;
        }
    }

    public function openRedeemModal(int $itemId): void
    {
        $this->resetErrorBag();
        $this->successMessage = null;
        $this->errorMessage = null;

        $item = ClaimItem::query()->where('is_active', true)->find($itemId);

        if (! $item) {
            $this->errorMessage = 'Selected reward item is not available.';

            return;
        }

        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            $this->errorMessage = 'Please sign in to redeem rewards.';

            return;
        }

        if ($user->spendable_coins < $item->coin_cost) {
            $this->errorMessage = "You need {$item->coin_cost} Thinker Coins to claim this item. (Your balance: {$user->spendable_coins} TC)";

            return;
        }

        if (! $item->isInStock()) {
            $this->errorMessage = 'Sorry, this item is out of stock.';

            return;
        }

        $this->selectedItem = $item;
        $this->phoneNumber = (string) ($user->whatsapp ?? '');
        $this->deliveryNotes = '';
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->selectedItem = null;
        $this->phoneNumber = '';
        $this->deliveryNotes = '';
    }

    public function toggleHistoryModal(): void
    {
        $this->showHistoryModal = ! $this->showHistoryModal;
    }

    public function redeemItem(?int $itemId = null): void
    {
        $this->resetErrorBag();
        $this->errorMessage = null;
        $this->successMessage = null;

        $targetId = $itemId ?: $this->selectedItem?->id;

        if (! $targetId) {
            $this->errorMessage = 'Please select a reward item to claim.';

            return;
        }

        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            $this->errorMessage = 'You must be logged in to claim rewards.';

            return;
        }

        $this->validate([
            'phoneNumber' => ['nullable', 'string', 'max:50'],
            'deliveryNotes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            DB::transaction(function () use ($user, $targetId) {
                /** @var User|null $lockedUser */
                $lockedUser = User::query()->where('id', $user->id)->lockForUpdate()->first();

                /** @var ClaimItem|null $lockedItem */
                $lockedItem = ClaimItem::query()->where('id', $targetId)->lockForUpdate()->first();

                if (! $lockedItem || ! $lockedItem->is_active) {
                    throw new \RuntimeException('This reward is currently not available.');
                }

                if (! $lockedItem->isInStock()) {
                    throw new \RuntimeException('Sorry, this item is currently out of stock.');
                }

                if ($lockedUser->spendable_coins < $lockedItem->coin_cost) {
                    throw new \RuntimeException("Insufficient Thinker Coins. You need {$lockedItem->coin_cost} TC (Current balance: {$lockedUser->spendable_coins} TC).");
                }

                // Deduct spendable coins
                $lockedUser->decrement('spendable_coins', $lockedItem->coin_cost);

                // Decrement stock if finite
                if ($lockedItem->stock_quantity > 0) {
                    $lockedItem->decrement('stock_quantity');
                }

                // Create claim request
                ClaimRequest::create([
                    'user_id' => $lockedUser->id,
                    'claim_item_id' => $lockedItem->id,
                    'coins_spent' => $lockedItem->coin_cost,
                    'status' => ClaimRequest::STATUS_PENDING,
                    'phone_number' => $this->phoneNumber ?: $lockedUser->whatsapp ?: null,
                    'delivery_notes' => $this->deliveryNotes ?: null,
                ]);
            });

            // Refresh auth user balance in session
            $user->refresh();

            $this->successMessage = '🎉 Reward claim submitted successfully! Your instructor/admin team will process and fulfill it shortly.';
            $this->closeModal();
        } catch (\Throwable $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function render(): View
    {
        /** @var User|null $user */
        $user = Auth::user();
        $gamification = app(GamificationService::class);

        $enrolledCourseIds = $user ? $user->courses()->pluck('courses.id')->all() : [];
        $enrolledCourses = $user ? $user->courses()->orderBy('title')->get() : collect();

        $itemsQuery = ClaimItem::query()
            ->where('is_active', true)
            ->with('course');

        // Course filter: strictly show rewards set by instructor/admin for enrolled courses
        if ($this->selectedCourse === 'general') {
            $itemsQuery->whereNull('course_id');
        } elseif ($this->selectedCourse !== 'all' && is_numeric($this->selectedCourse)) {
            $itemsQuery->where('course_id', (int) $this->selectedCourse);
        } else {
            // Show rewards for enrolled courses + platform-wide rewards
            $itemsQuery->forCourses($enrolledCourseIds);
        }

        if ($this->selectedCategory !== 'all') {
            $itemsQuery->where('category', $this->selectedCategory);
        }

        if (trim($this->search) !== '') {
            $itemsQuery->where(function ($q) {
                $q->where('title', 'like', '%'.trim($this->search).'%')
                    ->orWhere('description', 'like', '%'.trim($this->search).'%');
            });
        }

        $items = $itemsQuery->orderByDesc('is_active')
            ->orderBy('coin_cost')
            ->paginate(12);

        $userRank = $user ? $gamification->calculateUserRank((int) $user->lifetime_xp) : ['rank_name' => 'Novice', 'multiplier' => 1.0];

        $todayCoins = $user ? (int) XpTransaction::query()
            ->where('user_id', $user->id)
            ->whereDate('created_at', now()->toDateString())
            ->where('amount_coins', '>', 0)
            ->sum('amount_coins') : 0;

        $claimRequests = $user ? ClaimRequest::query()
            ->where('user_id', $user->id)
            ->with(['claimItem.course'])
            ->latest()
            ->get() : collect();

        // Point Earning Matrix resolution
        $targetCourse = null;
        if ($this->matrixCourseId) {
            $targetCourse = Course::find($this->matrixCourseId);
        } elseif ($this->selectedCourse !== 'all' && is_numeric($this->selectedCourse)) {
            $targetCourse = Course::find((int) $this->selectedCourse);
        } elseif ($enrolledCourses->isNotEmpty()) {
            $targetCourse = $enrolledCourses->first();
        }

        $effectiveMatrix = CourseGamificationRule::getEffectiveMatrixForCourse($targetCourse);

        if ($this->matrixCategory !== 'all') {
            $effectiveMatrix = array_values(array_filter($effectiveMatrix, fn ($r) => ($r['category'] ?? '') === $this->matrixCategory));
        }

        return view('livewire.claim-hub.storefront', [
            'items' => $items,
            'user' => $user,
            'userRank' => $userRank,
            'todayCoins' => $todayCoins,
            'dailyCap' => GamificationService::DAILY_COIN_CAP,
            'claimRequests' => $claimRequests,
            'enrolledCourses' => $enrolledCourses,
            'targetCourse' => $targetCourse,
            'effectiveMatrix' => $effectiveMatrix,
            'categories' => [
                'all' => '🌟 All Rewards',
                ClaimItem::CATEGORY_DATA => '📶 Data & Airtime',
                ClaimItem::CATEGORY_MERCH => '👕 Swag & Merch',
                ClaimItem::CATEGORY_VOUCHER => '🎟️ Vouchers',
                ClaimItem::CATEGORY_PERK => '🚀 Special Perks',
            ],
            'matrixCategories' => [
                'all' => 'All Categories',
                'Daily Login & Streak' => 'Daily Login & Streak',
                'Course & Learning Material' => 'Course & Learning Material',
                'Quizzes & Assessments' => 'Quizzes & Assessments',
                'Assignments' => 'Assignments',
                'Community & Peer Engagement' => 'Community & Peer Engagement',
                'Feedback & Platform Support' => 'Feedback & Platform Support',
                'Custom Actions' => 'Custom Actions',
            ],
        ]);
    }
}
