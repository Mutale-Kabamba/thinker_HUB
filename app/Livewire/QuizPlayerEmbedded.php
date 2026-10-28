<?php

namespace App\Livewire;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizOption;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class QuizPlayerEmbedded extends Component
{
    public int $quizId;
    public ?Quiz $quiz = null;
    public array $answers = []; // question_id => option_id or text
    public ?int $scorePercentage = null;
    public ?bool $hasPassed = null;
    public bool $submitted = false;

    public function mount(int $quizId)
    {
        $this->quizId = $quizId;
        $this->quiz = Quiz::with(['questions.options'])->find($quizId);
    }

    public function submitQuiz()
    {
        if (! $this->quiz) {
            return;
        }

        $questions = $this->quiz->questions;
        $totalQuestions = $questions->count();
        $totalPoints = $questions->sum('points') ?: $totalQuestions;
        $earnedPoints = 0;
        $autoGradingService = app(\App\Services\QuizAutoGradingService::class);
        $user = Auth::user();
        $attempt = null;

        if ($user) {
            $attempt = QuizAttempt::create([
                'quiz_id' => $this->quiz->id,
                'user_id' => $user->id,
                'started_at' => now(),
            ]);
        }

        foreach ($questions as $question) {
            $userAnswer = $this->answers[$question->id] ?? null;
            $qPoints = $question->points ?: 1;
            $pointsAwarded = 0;
            $isCorrect = false;
            $optionId = null;
            $textAnswer = null;
            $feedback = null;

            if ($question->isMultipleChoice()) {
                $optionId = $userAnswer ? (int) $userAnswer : null;
                $correctOption = $question->options->firstWhere('is_correct', true);
                $isCorrect = $correctOption && $optionId === (int) $correctOption->id;
                $pointsAwarded = $isCorrect ? $qPoints : 0;
                $earnedPoints += $pointsAwarded;
            } else {
                $textAnswer = is_string($userAnswer) ? trim($userAnswer) : '';
                if ($autoGradingService->isConfigured()) {
                    $evaluation = $autoGradingService->grade($question, $textAnswer);
                    $pointsAwarded = $evaluation['points_earned'];
                    $isCorrect = $evaluation['is_correct'];
                    $feedback = $evaluation['feedback'];
                } else {
                    $isCorrect = $textAnswer !== '';
                    $pointsAwarded = $isCorrect ? $qPoints : 0;
                    $feedback = $isCorrect ? 'Response recorded.' : 'No response provided.';
                }
                $earnedPoints += $pointsAwarded;
            }

            if ($attempt) {
                \App\Models\QuizAnswer::updateOrCreate(
                    [
                        'quiz_attempt_id' => $attempt->id,
                        'quiz_question_id' => $question->id,
                    ],
                    [
                        'quiz_option_id' => $optionId,
                        'text_answer' => $textAnswer,
                        'is_correct' => $isCorrect,
                        'points_earned' => $pointsAwarded,
                        'feedback' => $feedback,
                    ]
                );
            }
        }

        $this->scorePercentage = $totalPoints > 0 ? (int) round(($earnedPoints / $totalPoints) * 100) : 100;
        $passingScore = $this->quiz->pass_percentage ?? 70;
        $this->hasPassed = $this->scorePercentage >= $passingScore;
        $this->submitted = true;

        if ($attempt) {
            $attempt->update([
                'completed_at' => now(),
                'score' => $earnedPoints,
                'total_points' => $totalPoints,
                'percentage' => $this->scorePercentage,
                'passed' => $this->hasPassed,
                'raw_score' => $earnedPoints,
            ]);
        }

        if ($this->hasPassed) {
            $this->dispatch('quizPassed');
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Congratulations! You scored {$this->scorePercentage}% and passed the quiz.",
            ]);
        } else {
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => "You scored {$this->scorePercentage}%. Passing score is {$passingScore}%. You may retake the quiz.",
            ]);
        }
    }

    public function retake()
    {
        $this->answers = [];
        $this->scorePercentage = null;
        $this->hasPassed = null;
        $this->submitted = false;
    }

    public function render()
    {
        return view('livewire.quiz-player-embedded');
    }
}
