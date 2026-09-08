<?php

namespace App\Livewire;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Services\ProgressionService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

class CoursePlayer extends Component
{
    public Course $course;
    public ?Lesson $activeLesson = null;
    public array $progressMap = [];
    public int $overallProgress = 0;

    protected $listeners = [
        'videoEnded' => 'handleVideoCompletion',
        'quizPassed' => 'handleQuizPassed',
        'taskSubmitted' => 'handleTaskSubmitted',
    ];

    public function mount(Course $course, ?int $lessonId = null, ?ProgressionService $progressionService = null)
    {
        $progressionService = $progressionService ?? app(ProgressionService::class);
        $this->course = $course->load(['sections.lessons', 'lessons']);
        $user = Auth::user();

        if ($user) {
            // Ensure user has initial progression records
            $progressionService->initializeCourseProgress($user, $this->course);
            $this->refreshProgressData();
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
        return view('livewire.course-player')->layout('layouts.player');
    }
}
