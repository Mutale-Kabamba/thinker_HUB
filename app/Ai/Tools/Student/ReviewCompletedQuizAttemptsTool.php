<?php

namespace App\Ai\Tools\Student;

use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ReviewCompletedQuizAttemptsTool implements Tool
{
    public function __construct(protected User $student)
    {
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Review questions, feedback, points earned, and grading explanations for COMPLETED quizzes where results have been published.';
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'quiz_title' => $schema->string()->nullable(),
        ];
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $quizTitle = trim((string) ($request['quiz_title'] ?? ''));

        $query = QuizAttempt::query()
            ->where('user_id', $this->student->id)
            ->whereNotNull('completed_at')
            ->whereHas('quiz', function ($q) use ($quizTitle) {
                $q->where('show_results', true);

                if ($quizTitle !== '') {
                    $q->where('title', 'like', "%{$quizTitle}%");
                }
            })
            ->with(['quiz.course', 'answers.question.options', 'answers.option'])
            ->latest('completed_at');

        $attempts = $query->limit(5)->get();

        if ($attempts->isEmpty()) {
            return 'No completed quiz attempts with published results were found. Only quizzes with completed attempts and show_results enabled can be reviewed.';
        }

        $results = [];

        foreach ($attempts as $attempt) {
            $quiz = $attempt->quiz;

            $answersData = [];
            foreach ($attempt->answers as $answer) {
                $question = $answer->question;
                if (! $question) {
                    continue;
                }

                $studentResponse = $answer->quiz_option_id
                    ? ($answer->option?->option_text ?? "Option ID: {$answer->quiz_option_id}")
                    : ($answer->text_answer ?? 'No answer provided');

                $answersData[] = [
                    'question_type' => $question->type,
                    'question' => $question->question,
                    'student_answer' => $studentResponse,
                    'is_correct' => (bool) $answer->is_correct,
                    'points_earned' => (int) $answer->points_earned,
                    'points_max' => (int) $question->points,
                    'feedback' => $answer->feedback ?: 'No feedback provided.',
                    'explanation_rubric' => $question->explanation ?: 'No official explanation recorded.',
                ];
            }

            $results[] = [
                'attempt_id' => $attempt->id,
                'quiz_title' => $quiz?->title,
                'course' => $quiz?->course?->title,
                'score' => $attempt->score,
                'total_points' => $attempt->total_points,
                'percentage' => $attempt->percentage,
                'passed' => (bool) $attempt->passed,
                'completed_at' => $attempt->completed_at?->format('M d, Y H:i'),
                'questions_breakdown' => $answersData,
            ];
        }

        return json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}

