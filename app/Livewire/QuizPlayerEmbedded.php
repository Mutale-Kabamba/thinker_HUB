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

        foreach ($questions as $question) {
            $userAnswer = $this->answers[$question->id] ?? null;

            if ($question->isMultipleChoice()) {
                $correctOption = $question->options->firstWhere('is_correct', true);
                if ($correctOption && (int) $userAnswer === (int) $correctOption->id) {
                    $earnedPoints += ($question->points ?: 1);
                }
            } else {
                // Non-MCQ question: give full points if an answer was provided
                if (! empty($userAnswer)) {
                    $earnedPoints += ($question->points ?: 1);
                }
            }
        }

        $this->scorePercentage = $totalPoints > 0 ? (int) round(($earnedPoints / $totalPoints) * 100) : 100;
        $passingScore = $this->quiz->pass_percentage ?? 70;
        $this->hasPassed = $this->scorePercentage >= $passingScore;
        $this->submitted = true;

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
