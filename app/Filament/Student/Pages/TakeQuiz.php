<?php

namespace App\Filament\Student\Pages;

use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

class TakeQuiz extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static ?int $navigationSort = 5;

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.student.pages.take-quiz';

    public ?int $quizId = null;

    public array $quiz = [];

    public array $questions = [];

    public array $answers = [];

    public ?int $attemptId = null;

    public bool $submitted = false;

    public bool $isRetake = false;

    public array $results = [];

    public function mount(): void
    {
        $this->quizId = (int) request()->query('quiz');

        if (! $this->quizId) {
            $this->redirect(route('filament.student.pages.quizzes'));

            return;
        }

        $user = auth()->user();
        if (! $user) {
            return;
        }

        $quiz = Quiz::query()
            ->visibleTo($user)
            ->whereKey($this->quizId)
            ->first();

        if (! $quiz || (! $quiz->is_active && ! $quiz->publish_at)) {
            Notification::make()->title('Quiz not available.')->danger()->send();
            $this->redirect(route('filament.student.pages.quizzes'));

            return;
        }

        if (! $quiz->isReleased()) {
            Notification::make()
                ->title('This quiz is scheduled to release on ' . ($quiz->publish_at ? $quiz->publish_at->format('M j, Y \a\t g:i A') : 'a later date') . '.')
                ->warning()
                ->send();
            $this->redirect(route('filament.student.pages.quizzes'));

            return;
        }

        // Verify the student is enrolled in the quiz's course BEFORE loading quiz data
        if (! $user->isEnrolledInCourse($quiz->course_id)) {
            Notification::make()->title('You are not authorized to take this quiz.')->danger()->send();
            $this->redirect(route('filament.student.pages.quizzes'));

            return;
        }

        $quiz->load(['questions.options', 'course']);

        // Check if user has an existing completed attempt
        $latestCompletedAttempt = QuizAttempt::query()
            ->where('quiz_id', $quiz->id)
            ->where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->latest('id')
            ->first();

        // Check for in-progress attempt
        $inProgress = QuizAttempt::query()
            ->where('quiz_id', $quiz->id)
            ->where('user_id', $user->id)
            ->whereNull('completed_at')
            ->first();

        // If user already has a completed attempt and retake is NOT allowed and no in-progress retake attempt exists:
        if ($latestCompletedAttempt && ! $latestCompletedAttempt->retake_allowed && ! $inProgress) {
            $this->submitted = true;
            $this->loadResults($latestCompletedAttempt, $quiz);

            return;
        }

        // Determine if this attempt is an instructor-granted retake
        $isRetake = ($latestCompletedAttempt && $latestCompletedAttempt->retake_allowed) || ($inProgress && $inProgress->is_retake);
        $this->isRetake = (bool) $isRetake;

        if ($inProgress) {
            $this->attemptId = $inProgress->id;
            // Restore previous answers
            $savedAnswers = QuizAnswer::query()
                ->where('quiz_attempt_id', $inProgress->id)
                ->get();
            foreach ($savedAnswers as $answer) {
                $this->answers[$answer->quiz_question_id] = $answer->quiz_option_id
                    ? (string) $answer->quiz_option_id
                    : ($answer->text_answer ?? '');
            }
        } else {
            $attempt = QuizAttempt::query()->create([
                'quiz_id' => $quiz->id,
                'user_id' => $user->id,
                'started_at' => Carbon::now(),
                'is_retake' => $isRetake,
                'retake_granted_by' => $latestCompletedAttempt?->retake_granted_by,
                'retake_granted_at' => $latestCompletedAttempt?->retake_granted_at,
            ]);

            // Clear retake_allowed on previous attempt once new retake session starts
            if ($latestCompletedAttempt && $latestCompletedAttempt->retake_allowed) {
                $latestCompletedAttempt->update(['retake_allowed' => false]);
            }

            $this->attemptId = $attempt->id;
        }

        $this->quiz = [
            'id' => $quiz->id,
            'title' => $quiz->title,
            'description' => $quiz->description,
            'time_limit' => $quiz->time_limit_minutes,
            'pass_percentage' => $quiz->pass_percentage,
            'show_results' => $quiz->show_results,
            'course' => $quiz->course?->title ?? '',
        ];

        $questions = $quiz->shuffle_questions ? $quiz->questions->shuffle() : $quiz->questions;
        $this->questions = $questions->map(fn ($q) => [
            'id' => $q->id,
            'type' => $q->type,
            'question' => $q->question,
            'points' => $q->points,
            'options' => $q->options->map(fn ($o) => [
                'id' => $o->id,
                'text' => $o->option_text,
            ])->toArray(),
        ])->toArray();
    }

    public function submitQuiz(): void
    {
        $user = auth()->user();
        if (! $user || ! $this->attemptId) {
            return;
        }

        $attempt = QuizAttempt::query()->find($this->attemptId);
        if (! $attempt || $attempt->user_id !== $user->id) {
            return;
        }

        $quiz = Quiz::with('questions.options')->find($this->quizId);
        if (! $quiz) {
            return;
        }

        $totalPoints = 0;
        $earnedPoints = 0;

        foreach ($quiz->questions as $question) {
            $totalPoints += $question->points;
            $answer = $this->answers[$question->id] ?? null;

            $isCorrect = null;
            $pointsEarned = 0;
            $optionId = null;
            $textAnswer = null;

            if ($question->type === 'multiple_choice') {
                $optionId = $answer ? (int) $answer : null;
                $correctOption = $question->options->where('is_correct', true)->first();
                $isCorrect = $correctOption && $optionId === $correctOption->id;
                $pointsEarned = $isCorrect ? $question->points : 0;
                $earnedPoints += $pointsEarned;
            } else {
                // Theory and practical - store text, admin will grade
                $textAnswer = is_string($answer) ? $answer : null;
            }

            QuizAnswer::query()->updateOrCreate(
                [
                    'quiz_attempt_id' => $attempt->id,
                    'quiz_question_id' => $question->id,
                ],
                [
                    'quiz_option_id' => $optionId,
                    'text_answer' => $textAnswer,
                    'is_correct' => $isCorrect,
                    'points_earned' => $pointsEarned,
                ]
            );
        }

        $rawPercentage = $totalPoints > 0 ? (int) round(($earnedPoints / $totalPoints) * 100) : 0;
        $passPercentage = (int) ($quiz->pass_percentage ?? 50);
        $isPassed = $rawPercentage >= $passPercentage;

        $finalPercentage = $rawPercentage;
        $finalScore = $earnedPoints;

        // Academic Policy: If it's a retake, even if someone gets 100, the recorded marks = the passing mark.
        if ($attempt->is_retake && $isPassed) {
            $finalPercentage = $passPercentage;
            $finalScore = (int) round(($totalPoints * $passPercentage) / 100);
        }

        $attempt->update([
            'completed_at' => Carbon::now(),
            'score' => $finalScore,
            'total_points' => $totalPoints,
            'percentage' => $finalPercentage,
            'passed' => $isPassed,
            'raw_score' => $earnedPoints,
        ]);

        $this->submitted = true;
        $this->loadResults($attempt->fresh(), $quiz);

        Notification::make()->title('Quiz submitted successfully!')->success()->send();
    }

    protected function loadResults(QuizAttempt $attempt, Quiz $quiz): void
    {
        $this->quiz = [
            'id' => $quiz->id,
            'title' => $quiz->title,
            'description' => $quiz->description,
            'time_limit' => $quiz->time_limit_minutes,
            'pass_percentage' => $quiz->pass_percentage,
            'show_results' => $quiz->show_results,
            'course' => $quiz->course?->title ?? '',
        ];

        $this->results = [
            'score' => $attempt->score,
            'total' => $attempt->total_points,
            'percentage' => $attempt->percentage,
            'passed' => $attempt->passed,
            'is_retake' => (bool) $attempt->is_retake,
            'raw_score' => $attempt->raw_score,
            'completed_at' => $attempt->completed_at?->format('M d, Y H:i'),
        ];

        if ($quiz->show_results) {
            $answers = QuizAnswer::query()
                ->where('quiz_attempt_id', $attempt->id)
                ->get()
                ->keyBy('quiz_question_id');

            $this->questions = $quiz->questions->map(function ($q) use ($answers) {
                $answer = $answers->get($q->id);

                return [
                    'id' => $q->id,
                    'type' => $q->type,
                    'question' => $q->question,
                    'points' => $q->points,
                    'explanation' => $q->explanation,
                    'options' => $q->options->map(fn ($o) => [
                        'id' => $o->id,
                        'text' => $o->option_text,
                        'is_correct' => $o->is_correct,
                    ])->toArray(),
                    'user_answer' => $answer ? [
                        'option_id' => $answer->quiz_option_id,
                        'text' => $answer->text_answer,
                        'is_correct' => $answer->is_correct,
                        'points_earned' => $answer->points_earned,
                    ] : null,
                ];
            })->toArray();
        }
    }

    public function getTitle(): string
    {
        return $this->quiz['title'] ?? 'Take Quiz';
    }
}
