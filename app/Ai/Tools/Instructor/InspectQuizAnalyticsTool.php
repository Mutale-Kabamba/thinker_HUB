<?php

namespace App\Ai\Tools\Instructor;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class InspectQuizAnalyticsTool implements Tool
{
    public function __construct(protected User $instructor, protected array $allowedCourseIds)
    {
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Inspect quiz performance statistics, passing rates, score averages, and individual student attempts across authorized courses.';
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'course_id' => $schema->integer()->description('Optional course ID to filter quizzes.')->nullable(),
            'quiz_id' => $schema->integer()->description('Optional specific quiz ID for deep dive analytics.')->nullable(),
        ];
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $quizId = ! empty($request['quiz_id']) ? (int) $request['quiz_id'] : null;
        $courseId = ! empty($request['course_id']) ? (int) $request['course_id'] : null;

        $activeCourseIds = $this->allowedCourseIds;
        if ($courseId !== null) {
            if (! $this->isAuthorizedCourse($courseId)) {
                return "Error: Unauthorized. You cannot inspect quizzes for course ID #{$courseId}.";
            }
            $activeCourseIds = [$courseId];
        }

        if (empty($activeCourseIds) && ! $this->instructor->isAdmin()) {
            return 'Notice: You currently have no authorized courses assigned.';
        }

        $quizzesQuery = Quiz::query()
            ->where(function ($q) use ($activeCourseIds) {
                if (! $this->instructor->isAdmin()) {
                    $q->whereIn('course_id', $activeCourseIds);
                }
            })
            ->with(['course:id,title,code', 'attempts.user:id,name,email']);

        if ($quizId !== null) {
            $quizzesQuery->where('id', $quizId);
        }

        $quizzes = $quizzesQuery->orderByDesc('id')->limit(10)->get();

        if ($quizzes->isEmpty()) {
            return 'No quizzes found for the specified criteria in your authorized courses.';
        }

        $analytics = [];

        foreach ($quizzes as $quiz) {
            $cId = $quiz->course_id;
            if ($cId && ! $this->isAuthorizedCourse($cId)) {
                continue;
            }

            $enrolledStudents = User::query()
                ->whereHas('enrollments', fn ($q) => $q->where('course_id', $cId))
                ->get(['id', 'name', 'email']);

            $attempts = $quiz->attempts;
            $uniqueAttemptedUserIds = $attempts->pluck('user_id')->unique();
            $totalEnrolled = $enrolledStudents->count();
            $totalAttempts = $attempts->count();
            $uniqueStudentsAttempted = $uniqueAttemptedUserIds->count();

            $avgPercentage = $totalAttempts > 0 ? round($attempts->avg('percentage'), 1) : 0;
            $passedAttempts = $attempts->where('passed', true)->count();
            $passRate = $totalAttempts > 0 ? round(($passedAttempts / $totalAttempts) * 100, 1) : 0;

            // Missing students who have not attempted yet
            $unattemptedStudents = $enrolledStudents
                ->whereNotIn('id', $uniqueAttemptedUserIds)
                ->map(fn ($u) => ['id' => $u->id, 'name' => $u->name, 'email' => $u->email])
                ->values()
                ->all();

            // Failing attempts
            $failingAttempts = $attempts
                ->where('passed', false)
                ->map(fn ($att) => [
                    'student_name' => $att->user?->name ?? "Student #{$att->user_id}",
                    'score' => $att->score,
                    'total_points' => $att->total_points,
                    'percentage' => $att->percentage,
                    'completed_at' => $att->completed_at?->format('Y-m-d H:i'),
                ])
                ->values()
                ->all();

            $analytics[] = [
                'quiz_id' => $quiz->id,
                'quiz_title' => $quiz->title,
                'course' => $quiz->course?->title ?? "Course #{$cId}",
                'pass_percentage_required' => $quiz->pass_percentage ?? 50,
                'total_enrolled_cohort' => $totalEnrolled,
                'students_attempted_count' => $uniqueStudentsAttempted,
                'unattempted_students_count' => count($unattemptedStudents),
                'total_attempts_submitted' => $totalAttempts,
                'average_score_percentage' => $avgPercentage,
                'pass_rate_percentage' => $passRate,
                'failing_attempts_count' => count($failingAttempts),
                'unattempted_students_sample' => array_slice($unattemptedStudents, 0, 5),
                'failing_attempts_sample' => array_slice($failingAttempts, 0, 5),
            ];
        }

        return json_encode([
            'total_quizzes_analyzed' => count($analytics),
            'quizzes' => $analytics,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    protected function isAuthorizedCourse(int $courseId): bool
    {
        if ($this->instructor->isAdmin()) {
            return true;
        }

        return in_array($courseId, $this->allowedCourseIds, true);
    }
}

