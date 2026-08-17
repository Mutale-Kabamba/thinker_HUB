<?php

namespace App\Livewire\Reviews;

use App\Models\Course;
use App\Models\Review;
use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class ReviewList extends Component
{
    use WithPagination;

    public ?string $targetType = null; // 'course', 'instructor', or null / 'platform'

    public ?int $targetId = null;

    public ?string $targetTitle = null;

    public ?int $filterRating = null;

    public int $perPage = 6;

    public function mount(?string $targetType = null, ?int $targetId = null, ?string $targetTitle = null): void
    {
        $this->targetType = $targetType;
        $this->targetId = $targetId;
        $this->targetTitle = $targetTitle;
    }

    #[On('reviewSubmitted')]
    public function refreshReviews(): void
    {
        $this->resetPage();
    }

    public function setFilterRating(?int $rating): void
    {
        $this->filterRating = $this->filterRating === $rating ? null : $rating;
        $this->resetPage();
    }

    public function openSubmitModal(): void
    {
        $this->dispatch('openSubmitReviewModal',
            targetType: $this->targetType,
            targetId: $this->targetId,
            targetTitle: $this->targetTitle
        );
    }

    public function render()
    {
        $reviewableType = match ($this->targetType) {
            'course' => Course::class,
            'instructor' => User::class,
            default => null,
        };

        $baseQuery = Review::query()->approved();

        if ($reviewableType) {
            $baseQuery->where('reviewable_type', $reviewableType);
            if ($this->targetId) {
                $baseQuery->where('reviewable_id', $this->targetId);
            }
        } elseif ($this->targetType === 'platform') {
            $baseQuery->platformOnly();
        }

        // Stats across all ratings for this target
        $allReviews = (clone $baseQuery)->get();
        $totalCount = $allReviews->count();
        $avgRating = $totalCount > 0 ? round((float) $allReviews->avg('rating'), 2) : 0.00;

        $starCounts = [
            5 => $allReviews->where('rating', 5)->count(),
            4 => $allReviews->where('rating', 4)->count(),
            3 => $allReviews->where('rating', 3)->count(),
            2 => $allReviews->where('rating', 2)->count(),
            1 => $allReviews->where('rating', 1)->count(),
        ];

        // Filtered list
        $query = (clone $baseQuery)->with('user')->latest();

        if ($this->filterRating) {
            $query->where('rating', $this->filterRating);
        }

        $reviews = $query->paginate($this->perPage);

        return view('livewire.reviews.review-list', [
            'reviews' => $reviews,
            'totalCount' => $totalCount,
            'avgRating' => $avgRating,
            'starCounts' => $starCounts,
        ]);
    }
}
