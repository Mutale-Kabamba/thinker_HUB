<?php

namespace App\Livewire;

use App\Models\Broadcast;
use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Models\Course;
use App\Models\CourseRating;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Services\ProgressionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

class CoursePlayer extends Component
{
    public Course $course;
    public ?Lesson $activeLesson = null;
    public array $progressMap = [];
    public int $overallProgress = 0;
    public int $userRating = 0;
    public string $userReview = '';
    public bool $ratingSaved = false;
    public string $newQuestionTitle = '';
    public string $newQuestionBody = '';
    public bool $questionPosted = false;

    protected $listeners = [
        'videoEnded' => 'handleVideoCompletion',
        'quizPassed' => 'handleQuizPassed',
        'taskSubmitted' => 'handleTaskSubmitted',
    ];

    public function mount(Course $course, ?int $lessonId = null, ?ProgressionService $progressionService = null)
    {
        $progressionService = $progressionService ?? app(ProgressionService::class);

        // Auto-sync any SCORM/xAPI packages attached to this course that don't yet have a lesson row
        if (\Illuminate\Support\Facades\Schema::hasTable('scorm_packages')) {
            $orphanPackages = \App\Models\ScormPackage::query()
                ->where('course_id', $course->id)
                ->where('is_active', true)
                ->whereNull('lesson_id')
                ->get();

            foreach ($orphanPackages as $pkg) {
                $nextOrder = ((int) Lesson::where('course_id', $course->id)->max('order_column')) + 1;
                $newLesson = Lesson::create([
                    'course_id' => $course->id,
                    'title' => $pkg->title,
                    'type' => 'scorm',
                    'scorm_package_id' => $pkg->id,
                    'order_column' => $nextOrder,
                    'sort_order' => $nextOrder,
                    'is_published' => true,
                ]);
                $pkg->update(['lesson_id' => $newLesson->id]);
            }
        }

        $this->course = $course->load(['sections.lessons', 'lessons.scormPackage'])->loadCount(['enrollments', 'ratings']);
        $user = Auth::user();

        if ($user) {
            // Ensure user has initial progression records
            $progressionService->initializeCourseProgress($user, $this->course);
            $this->refreshProgressData();

            $existingRating = CourseRating::query()
                ->where('course_id', $this->course->id)
                ->where('user_id', $user->id)
                ->first();

            if ($existingRating) {
                $this->userRating = (int) $existingRating->rating;
                $this->userReview = (string) ($existingRating->review ?? '');
            }
        }

        // Determine target active lesson
        if ($lessonId && $user) {
            $target = Lesson::where('course_id', $this->course->id)->find($lessonId);
            if ($target && $progressionService->isLessonUnlocked($user, $target)) {
                $this->activeLesson = $target;
            }
        }

        if (! $this->activeLesson) {
            // Find current furthest unlocked and uncompleted lesson, or fall back to first lesson
            $nextPending = null;
            if ($user) {
                $nextPending = $this->course->lessons()
                    ->whereHas('progressForUser', fn ($q) => $q->where('is_unlocked', true)->where('is_completed', false))
                    ->orderBy('order_column', 'asc')
                    ->first();
            }

            $this->activeLesson = $nextPending ?? $this->course->lessons()->orderBy('order_column', 'asc')->first();
        }
    }

    public function selectLesson(int $lessonId, ?ProgressionService $progressionService = null)
    {
        $progressionService = $progressionService ?? app(ProgressionService::class);
        $target = Lesson::where('course_id', $this->course->id)->findOrFail($lessonId);

        $user = Auth::user();
        if (! $user || ! $progressionService->isLessonUnlocked($user, $target)) {
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => 'Complete the previous lesson to unlock this step.',
            ]);
            return;
        }

        $this->activeLesson = $target;
    }

    public function goToAdjacentLesson(string $direction, ?ProgressionService $progressionService = null): void
    {
        if (! $this->activeLesson) {
            return;
        }

        $ordered = $this->course->lessons->sortBy('order_column')->values();
        $currentIndex = $ordered->search(fn (Lesson $l): bool => $l->id === $this->activeLesson->id);

        if ($currentIndex === false) {
            return;
        }

        $targetIndex = $direction === 'prev' ? $currentIndex - 1 : $currentIndex + 1;
        $target = $ordered->get($targetIndex);

        if ($target) {
            $this->selectLesson($target->id, $progressionService);
        }
    }

    public function markReadingComplete(?ProgressionService $progressionService = null)
    {
        if ($this->activeLesson && $this->activeLesson->type === 'reading') {
            $this->advance($progressionService ?? app(ProgressionService::class));
        }
    }

    #[On('videoEnded')]
    public function handleVideoCompletion(?ProgressionService $progressionService = null)
    {
        if ($this->activeLesson && $this->activeLesson->type === 'video') {
            $this->advance($progressionService ?? app(ProgressionService::class));
        }
    }

    #[On('quizPassed')]
    public function handleQuizPassed(?ProgressionService $progressionService = null)
    {
        if ($this->activeLesson && $this->activeLesson->type === 'quiz') {
            $this->advance($progressionService ?? app(ProgressionService::class));
        }
    }

    #[On('taskSubmitted')]
    public function handleTaskSubmitted(?ProgressionService $progressionService = null)
    {
        if ($this->activeLesson && $this->activeLesson->type === 'assignment') {
            $this->advance($progressionService ?? app(ProgressionService::class));
        }
    }

    #[On('scormCompleted')]
    public function handleScormCompleted(): void
    {
        $this->refreshProgressData();
    }

    protected function advance(ProgressionService $progressionService)
    {
        $user = Auth::user();
        if (! $user || ! $this->activeLesson) {
            return;
        }

        $next = $progressionService->completeAndAdvance($user, $this->activeLesson);
        $this->refreshProgressData();

        if ($next) {
            $this->activeLesson = $next;
            $this->dispatch('lesson-changed', ['lessonId' => $next->id]);
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Lesson completed! Next lesson unlocked.',
            ]);
        } else {
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Course completed! Congratulations!',
            ]);
        }
    }

    public function submitRating(int $rating, ?string $review = null): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $rating = max(1, min(5, $rating));
        $cleanReview = trim((string) ($review ?? $this->userReview));

        CourseRating::updateOrCreate(
            [
                'course_id' => $this->course->id,
                'user_id' => $user->id,
            ],
            [
                'rating' => $rating,
                'review' => $cleanReview !== '' ? $cleanReview : null,
            ]
        );

        $this->userRating = $rating;
        $this->userReview = $cleanReview;
        $this->ratingSaved = true;
        $this->course->loadCount(['enrollments', 'ratings']);

        $this->dispatch('rating-saved');
    }

    public function postQuestion(): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $title = trim($this->newQuestionTitle);
        $body = trim($this->newQuestionBody);

        if ($title === '' && $body === '') {
            return;
        }

        $room = ChatRoom::firstOrCreate(
            ['course_id' => $this->course->id, 'type' => 'course'],
            ['name' => $this->course->title . ' — Q&A', 'created_by' => $user->id]
        );

        $room->members()->syncWithoutDetaching([$user->id]);

        $formatted = $title !== '' && $body !== ''
            ? $title . "\n" . $body
            : ($title !== '' ? $title : $body);

        ChatMessage::create([
            'chat_room_id' => $room->id,
            'user_id' => $user->id,
            'body' => $formatted,
        ]);

        $this->newQuestionTitle = '';
        $this->newQuestionBody = '';
        $this->questionPosted = true;
    }

    public function syncVideoTime(int $second)
    {
        $user = Auth::user();
        if ($user && $this->activeLesson) {
            LessonProgress::where('user_id', $user->id)
                ->where('lesson_id', $this->activeLesson->id)
                ->update(['last_watched_second' => $second]);
        }
    }

    protected function refreshProgressData()
    {
        $userId = Auth::id();
        if (! $userId) {
            return;
        }

        $records = LessonProgress::where('user_id', $userId)
            ->where('course_id', $this->course->id)
            ->get();

        $this->progressMap = $records->keyBy('lesson_id')->toArray();

        $total = $this->course->lessons->count();
        $completed = $records->where('is_completed', true)->count();
        $this->overallProgress = $total > 0 ? (int) round(($completed / $total) * 100) : 0;
    }

    #[Layout('layouts.player')]
    public function render()
    {
        $announcements = Schema::hasTable('broadcasts')
            ? Broadcast::query()
                ->where('course_id', $this->course->id)
                ->with('sender')
                ->orderByDesc('sent_at')
                ->orderByDesc('created_at')
                ->limit(25)
                ->get()
            : collect();

        $reviews = Schema::hasTable('course_ratings')
            ? CourseRating::query()
                ->where('course_id', $this->course->id)
                ->with('user')
                ->latest()
                ->get()
            : collect();

        $totalReviews = $reviews->count();
        $averageRating = $totalReviews > 0
            ? round((float) $reviews->avg('rating'), 1)
            : ($this->course->average_rating ? round((float) $this->course->average_rating, 1) : 5.0);

        $ratingBreakdown = [];
        for ($star = 5; $star >= 1; $star--) {
            $count = $reviews->where('rating', $star)->count();
            $ratingBreakdown[$star] = [
                'count' => $count,
                'percent' => $totalReviews > 0 ? (int) round(($count / $totalReviews) * 100) : 0,
            ];
        }

        $questions = collect();
        if (Schema::hasTable('chat_rooms') && Schema::hasTable('chat_messages')) {
            $roomIds = ChatRoom::query()
                ->where('course_id', $this->course->id)
                ->where('type', 'course')
                ->pluck('id');

            if ($roomIds->isNotEmpty()) {
                $questions = ChatMessage::query()
                    ->whereIn('chat_room_id', $roomIds)
                    ->whereNotNull('body')
                    ->with('user')
                    ->latest()
                    ->limit(30)
                    ->get();
            }
        }

        return view('livewire.course-player', [
            'announcements' => $announcements,
            'reviews' => $reviews,
            'totalReviews' => $totalReviews,
            'averageRating' => $averageRating,
            'ratingBreakdown' => $ratingBreakdown,
            'questions' => $questions,
        ])->layout('layouts.player');
    }
}
