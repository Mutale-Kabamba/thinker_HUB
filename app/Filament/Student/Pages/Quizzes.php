<?php

namespace App\Filament\Student\Pages;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use Filament\Pages\Page;

class Quizzes extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static string|\UnitEnum|null $navigationGroup = 'EVALUATIONS';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.student.pages.quizzes';

    public array $quizzes = [];

    public function mount(): void
    {
        $this->refreshQuizzes();
    }

    protected function refreshQuizzes(): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        if ($user->isAdmin()) {
            $enrolledCourseIds = \App\Models\Course::query()->pluck('id')->all();
        } elseif ($user->isInstructor()) {
            $enrolledCourseIds = \App\Models\Course::query()
                ->where('course_by', (string) $user->id)
                ->orWhere('course_by', (string) $user->name)
                ->orWhereHas('instructors', fn ($q) => $q->where('users.id', $user->id))
                ->pluck('id')
                ->merge($user->courses()->pluck('courses.id'))
                ->unique()
                ->all();
        } else {
            $enrolledCourseIds = $user->courses()->pluck('courses.id')->all();
        }

        $attempts = QuizAttempt::query()
            ->where('user_id', $user->id)
            ->get()
            ->groupBy('quiz_id');

        $rawQuizzes = Quiz::query()
            ->with(['course', 'questions'])
            ->whereIn('course_id', $enrolledCourseIds)
            ->where(function ($query) {
                $query->where('is_active', true)
                    ->orWhereNotNull('publish_at');
            })
            ->orderByRaw('COALESCE(publish_at, created_at) ASC')
            ->get();

        $visibleQuizzes = collect();

        foreach ($rawQuizzes->groupBy('course_id') as $courseQuizzes) {
            $releasedOrAttempted = $courseQuizzes->filter(fn (Quiz $q) => $q->isReleased() || $attempts->has($q->id));
            $futureScheduled = $courseQuizzes->filter(fn (Quiz $q) => ! $q->isReleased() && ! $attempts->has($q->id) && $q->publish_at !== null && $q->publish_at->isFuture());

            $nextUpcoming = $futureScheduled->first();

            $courseVisible = $releasedOrAttempted;
            if ($nextUpcoming) {
                $courseVisible = $courseVisible->push($nextUpcoming);
            }

            $visibleQuizzes = $visibleQuizzes->concat($courseVisible);
        }

        $this->quizzes = $visibleQuizzes
            ->map(function (Quiz $quiz) use ($attempts) {
                $quizAttempts = $attempts->get($quiz->id);
                $latestCompletedAttempt = $quizAttempts?->filter(fn (QuizAttempt $a) => $a->completed_at !== null)->sortByDesc('id')->first();
                $inProgress = $quizAttempts?->first(fn (QuizAttempt $a) => $a->completed_at === null);
                $isReleased = $quiz->isReleased();
                $retakeAllowed = (bool) ($latestCompletedAttempt?->retake_allowed);

                if ($inProgress) {
                    $status = 'in_progress';
                    $statusLabel = 'In Progress';
                } elseif ($retakeAllowed) {
                    $status = 'retake_allowed';
                    $statusLabel = '2nd Try Available';
                } elseif ($latestCompletedAttempt) {
                    $status = 'completed';
                    $statusLabel = 'Completed';
                } elseif (! $isReleased) {
                    $status = 'scheduled';
                    $statusLabel = 'Available ' . ($quiz->publish_at ? $quiz->publish_at->format('M j, g:i A') : 'Soon');
                } else {
                    $status = 'not_started';
                    $statusLabel = 'Available';
                }

                return [
                    'id' => $quiz->id,
                    'title' => $quiz->title,
                    'description' => $quiz->description ?? '',
                    'course' => $quiz->course?->title ?? 'Unassigned course',
                    'question_count' => $quiz->questions->count(),
                    'time_limit' => $quiz->time_limit_minutes,
                    'pass_percentage' => $quiz->pass_percentage,
                    'status' => $status,
                    'status_label' => $statusLabel,
                    'is_released' => $isReleased,
                    'retake_allowed' => $retakeAllowed,
                    'score' => $latestCompletedAttempt?->percentage,
                    'passed' => $latestCompletedAttempt?->passed,
                    'is_retake' => (bool) $latestCompletedAttempt?->is_retake,
                    'completed_at' => $latestCompletedAttempt?->completed_at?->format('M d, Y H:i'),
                ];
            })
            ->values()
            ->all();
    }
}
