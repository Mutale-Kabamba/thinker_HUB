<?php

namespace App\Filament\Student\Pages;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use Filament\Pages\Page;

class Quizzes extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static ?int $navigationSort = 5;

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

        $enrolledCourseIds = $user->courses()->pluck('courses.id')->all();

        $attempts = QuizAttempt::query()
            ->where('user_id', $user->id)
            ->get()
            ->groupBy('quiz_id');

        $this->quizzes = Quiz::query()
            ->with(['course', 'questions'])
            ->whereIn('course_id', $enrolledCourseIds)
            ->where('is_active', true)
            ->orderByDesc('created_at')
            ->get()
            ->map(function (Quiz $quiz) use ($attempts) {
                $quizAttempts = $attempts->get($quiz->id);
                $completedAttempt = $quizAttempts?->first(fn (QuizAttempt $a) => $a->completed_at !== null);
                $inProgress = $quizAttempts?->first(fn (QuizAttempt $a) => $a->completed_at === null);

                if ($completedAttempt) {
                    $status = 'completed';
                    $statusLabel = 'Completed';
                } elseif ($inProgress) {
                    $status = 'in_progress';
                    $statusLabel = 'In Progress';
                } else {
                    $status = 'not_started';
                    $statusLabel = 'Not Started';
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
                    'score' => $completedAttempt?->percentage,
                    'passed' => $completedAttempt?->passed,
                    'completed_at' => $completedAttempt?->completed_at?->format('M d, Y H:i'),
                ];
            })
            ->values()
            ->all();
    }
}
