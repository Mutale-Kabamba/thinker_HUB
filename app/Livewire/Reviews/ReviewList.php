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

    public int $perPage = 15;

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

    public function openSubmitModal()
    {
        if (! \Illuminate\Support\Facades\Auth::check()) {
            return $this->redirect(route('login'));
        }

        return $this->redirect(route('reviews.create', [
            'type' => $this->targetType ?: 'platform',
            'id' => $this->targetId,
        ]));
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
        $ratedReviews = $allReviews->whereNotNull('rating');
        $ratingCount = $ratedReviews->count();
        $avgRating = $ratingCount > 0 ? round((float) $ratedReviews->avg('rating'), 2) : 0.00;

        $starCounts = [
            5 => $ratedReviews->where('rating', 5)->count(),
            4 => $ratedReviews->where('rating', 4)->count(),
            3 => $ratedReviews->where('rating', 3)->count(),
            2 => $ratedReviews->where('rating', 2)->count(),
            1 => $ratedReviews->where('rating', 1)->count(),
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
            'ratingCount' => $ratingCount,
            'avgRating' => $avgRating,
            'starCounts' => $starCounts,
        ]);
    }
}
