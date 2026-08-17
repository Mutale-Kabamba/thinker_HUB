<?php

namespace App\Livewire\Reviews;

use App\Models\Course;
use App\Models\Review;
use App\Models\User;
use App\Services\GamificationService;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class SubmitReviewModal extends Component
{
    public ?string $targetType = null; // 'course', 'instructor', or null / 'platform'

    public ?int $targetId = null;

    public ?string $targetTitle = null;

    public int $rating = 5;

    public string $title = '';

    public string $comment = '';

    public bool $isAnonymous = false;

    public bool $isOpen = false;

    protected function rules(): array
    {
        return [
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:120',
            'comment' => 'required|string|min:5|max:1500',
            'isAnonymous' => 'boolean',
        ];
    }

    #[On('openSubmitReviewModal')]
    public function openModal(?string $targetType = null, ?int $targetId = null, ?string $targetTitle = null): void
    {
        if (! Auth::check()) {
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => 'Please sign in to submit a review.',
            ]);

            return;
        }

        $this->targetType = $targetType;
        $this->targetId = $targetId;
        $this->targetTitle = $targetTitle;

        $reviewableType = match ($this->targetType) {
            'course' => Course::class,
            'instructor' => User::class,
            default => null,
        };

        // Load existing review if user previously reviewed this target
        $existing = Review::query()
            ->where('user_id', Auth::id())
            ->where('reviewable_type', $reviewableType)
            ->where('reviewable_id', $this->targetId)
            ->first();

        if ($existing) {
            $this->rating = $existing->rating;
            $this->title = $existing->title ?? '';
            $this->comment = $existing->comment ?? '';
            $this->isAnonymous = (bool) $existing->is_anonymous;
        } else {
            $this->rating = 5;
            $this->title = '';
            $this->comment = '';
            $this->isAnonymous = false;
        }

        $this->isOpen = true;
    }

    public function closeModal(): void
    {
        $this->isOpen = false;
    }

    public function setRating(int $value): void
    {
        $this->rating = max(1, min(5, $value));
    }

    public function submitReview(): void
    {
        if (! Auth::check()) {
            return;
        }

        $this->validate();

        $user = Auth::user();

        // Resolve Polymorphic model
        $reviewableType = match ($this->targetType) {
            'course' => Course::class,
            'instructor' => User::class,
            default => null,
        };

        $reviewableId = $this->targetId;

        // Verification check
        $isVerified = false;
        if ($this->targetType === 'course' && $this->targetId) {
            $isVerified = $user->enrollments()->where('course_id', $this->targetId)->exists();
        } elseif ($this->targetType === 'instructor' && $this->targetId) {
            $isVerified = $user->enrollments()->whereHas('course', function ($q) {
                $q->where('instructor_id', $this->targetId);
            })->exists();
        }

        $review = Review::updateOrCreate(
            [
                'user_id' => $user->id,
                'reviewable_type' => $reviewableType,
                'reviewable_id' => $reviewableId,
            ],
            [
                'rating' => $this->rating,
                'title' => trim($this->title) ?: null,
                'comment' => trim($this->comment),
                'is_anonymous' => $this->isAnonymous,
                'is_verified' => $isVerified,
                'is_approved' => true,
            ]
        );

        $this->isOpen = false;
        $this->dispatch('reviewSubmitted');

        try {
            Notification::make()
                ->title('Review Submitted!')
                ->body('Thank you! Your review and rating have been posted.')
                ->success()
                ->send();
        } catch (\Throwable) {
            // Non-filament fallback
        }
    }

    public function render()
    {
        return view('livewire.reviews.submit-review-modal');
    }
}
