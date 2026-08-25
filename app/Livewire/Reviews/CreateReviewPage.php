<?php

namespace App\Livewire\Reviews;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Review;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('Reviews & Ratings | think.er HUB')]
class CreateReviewPage extends Component
{
    #[Url(as: 'type')]
    public string $targetType = 'platform'; // 'platform', 'course', 'instructor'

    #[Url(as: 'id')]
    public ?int $targetId = null;

    public ?int $selectedCourseId = null;

    public ?int $selectedInstructorId = null;

    public ?int $rating = null;

    public string $title = '';

    public string $comment = '';

    public bool $isAnonymous = false;

    public bool $submitted = false;

    public ?string $submittedMessage = null;

    public ?string $redirectUrl = null;

    public function mount(?string $type = null, ?int $id = null): void
    {
        if (! Auth::check()) {
            redirect()->guest(route('login'));
            return;
        }

        $type = $type ?: request('type', $this->targetType);
        $id = $id ?: request('id', $this->targetId);

        if (in_array($type, ['platform', 'course', 'instructor'], true)) {
            $this->targetType = $type;
        } else {
            $this->targetType = 'platform';
        }

        if ($this->targetType === 'course' && $id) {
            $this->selectedCourseId = (int) $id;
        } elseif ($this->targetType === 'instructor' && $id) {
            $this->selectedInstructorId = (int) $id;
        }
    }

    public function setTargetType(string $type): void
    {
        if (in_array($type, ['platform', 'course', 'instructor'], true)) {
            $this->targetType = $type;
            $this->resetValidation();
        }
    }

    public function setRating(?int $rating): void
    {
        $this->rating = $rating !== null ? max(1, min(5, $rating)) : null;
    }

    public function clearRating(): void
    {
        $this->rating = null;
    }

    protected function rules(): array
    {
        return [
            'targetType' => 'required|in:platform,course,instructor',
            'selectedCourseId' => 'required_if:targetType,course|nullable|exists:courses,id',
            'selectedInstructorId' => 'required_if:targetType,instructor|nullable|exists:users,id',
            'rating' => 'nullable|integer|min:1|max:5',
            'title' => 'nullable|string|max:120',
            'comment' => 'nullable|string|max:2000',
        ];
    }

    protected function messages(): array
    {
        return [
            'selectedCourseId.required_if' => 'Please select the course you are reviewing.',
            'selectedInstructorId.required_if' => 'Please select the instructor you are reviewing.',
        ];
    }

    public function submitReview()
    {
        if (! Auth::check()) {
            return $this->redirect(route('login'));
        }

        $this->validate();

        if ($this->rating === null && empty(trim($this->comment))) {
            $this->addError('comment', 'Please provide a star rating, a written review, or both.');
            return;
        }

        $user = Auth::user();

        // Resolve Polymorphic Target
        $reviewableType = match ($this->targetType) {
            'course' => Course::class,
            'instructor' => User::class,
            default => null,
        };

        $reviewableId = match ($this->targetType) {
            'course' => $this->selectedCourseId,
            'instructor' => $this->selectedInstructorId,
            default => null,
        };

        // Determine verification
        $isVerified = false;
        if ($this->targetType === 'course' && $reviewableId) {
            $isVerified = Enrollment::query()
                ->where('user_id', $user->id)
                ->where('course_id', $reviewableId)
                ->exists();
        } elseif ($this->targetType === 'instructor' && $reviewableId) {
            $instructorCourseIds = Course::query()
                ->where('created_by', $reviewableId)
                ->orWhereHas('instructors', fn ($q) => $q->where('users.id', $reviewableId))
                ->pluck('id');

            $isVerified = Enrollment::query()
                ->where('user_id', $user->id)
                ->whereIn('course_id', $instructorCourseIds)
                ->exists();
        } else {
            $isVerified = true;
        }

        $reviewRating = ($this->rating !== null && $this->rating !== '') ? (int) $this->rating : null;

        $review = Review::create([
            'user_id' => $user->id,
            'reviewable_type' => $reviewableType,
            'reviewable_id' => $reviewableId,
            'rating' => $reviewRating,
            'title' => $this->title ? trim($this->title) : null,
            'comment' => ! empty(trim($this->comment)) ? trim($this->comment) : null,
            'is_anonymous' => $this->isAnonymous,
            'is_approved' => true,
            'is_verified' => $isVerified,
        ]);

        $this->submitted = true;
        $this->title = '';
        $this->comment = '';
        $this->rating = null;

        session()->flash('success', 'Thank you! Your review and rating have been published (+10 XP awarded).');

        try {
            Notification::make()
                ->title('Review Published (+10 XP)')
                ->body('Thank you for contributing to community ratings.')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            // Safe fallback if not in Filament request context
        }
    }

    public function deleteReview(int $id): void
    {
        $review = Review::where('id', $id)->where('user_id', Auth::id())->first();
        if ($review) {
            $review->delete();
            session()->flash('success', 'Review deleted successfully.');

            try {
                Notification::make()
                    ->title('Review Deleted')
                    ->success()
                    ->send();
            } catch (\Throwable $e) {
                // Ignore
            }
        }
    }

    public function render()
    {
        $courses = Course::query()
            ->where('is_active', true)
            ->orderBy('title')
            ->get(['id', 'title', 'code']);

        $instructors = User::query()
            ->where(fn ($q) => $q->where('role', 'instructor')->orWhere(fn ($sub) => $sub->where('role', 'admin')->whereHas('instructorCourses')))
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'occupation']);

        $myReviews = Auth::check()
            ? Review::where('user_id', Auth::id())
                ->with('reviewable')
                ->latest()
                ->get()
            : collect();

        return view('livewire.reviews.create-review-page', [
            'courses' => $courses,
            'instructors' => $instructors,
            'myReviews' => $myReviews,
        ]);
    }
}
