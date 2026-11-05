<?php

namespace App\Ai\Tools\Instructor;

use App\Models\AssessmentSubmission;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\CourseIntake;
use App\Models\CourseSession;
use App\Models\Enrollment;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Services\AttendanceService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetCohortPerformanceSummaryTool implements Tool
{
    public function __construct(protected User $instructor, protected array $allowedCourseIds)
    {
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Summarize cohort analytics for an authorized course: enrolled students, quiz scores, pending submissions awaiting review, and overall attendance rate.';
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'course_id' => $schema->integer()->required(),
            'course_intake_id' => $schema->integer()->nullable(),
        ];
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $courseId = (int) ($request['course_id'] ?? 0);
        $intakeId = ! empty($request['course_intake_id']) ? (int) $request['course_intake_id'] : null;

        if (! $this->isAuthorizedCourse($courseId)) {
            return "Error: Unauthorized. You cannot access performance analytics for course ID #{$courseId}.";
        }

        $course = Course::find($courseId);
        if (! $course) {
            return "Error: Course with ID #{$courseId} does not exist.";
        }

        // 1. Enrollment Count
        $enrollmentQuery = Enrollment::query()->where('course_id', $courseId);
        if ($intakeId) {
            $enrollmentQuery->where('course_intake_id', $intakeId);
        }
        $enrolledCount = $enrollmentQuery->count();

        // 2. Quiz Attempts & Averages
        $quizQuery = QuizAttempt::query()
            ->whereHas('quiz', function ($q) use ($courseId, $intakeId) {
                $q->where('course_id', $courseId);
                if ($intakeId) {
                    $q->where(fn ($sub) => $sub->where('course_intake_id', $intakeId)->orWhereNull('course_intake_id'));
                }
            })
            ->whereNotNull('completed_at');

        $totalQuizAttempts = $quizQuery->count();
        $avgScore = $totalQuizAttempts > 0 ? round($quizQuery->avg('percentage'), 1) : 0;
        $passedAttempts = $totalQuizAttempts > 0 ? (clone $quizQuery)->where('passed', true)->count() : 0;
        $passRate = $totalQuizAttempts > 0 ? round(($passedAttempts / $totalQuizAttempts) * 100, 1) : 0;

        // 3. Pending Submissions to Grade
        $pendingAssignments = AssignmentSubmission::query()
            ->whereHas('assignment', function ($q) use ($courseId, $intakeId) {
                $q->where('course_id', $courseId);
                if ($intakeId) {
                    $q->where('course_intake_id', $intakeId);
                }
            })
            ->whereNull('grade')
            ->count();

        $pendingAssessments = AssessmentSubmission::query()
            ->whereHas('assessment', function ($q) use ($courseId, $intakeId) {
                $q->where('course_id', $courseId);
                if ($intakeId) {
                    $q->where('course_intake_id', $intakeId);
                }
            })
            ->whereNull('score')
            ->count();

        // 4. Session & Attendance Summary
        $sessionQuery = CourseSession::query()->where('course_id', $courseId);
        if ($intakeId) {
            $sessionQuery->where(fn ($q) => $q->where('course_intake_id', $intakeId)->orWhereNull('course_intake_id'));
        }
        $sessions = $sessionQuery->get();
        $totalSessions = $sessions->count();
        $completedSessions = $sessions->where('status', 'completed')->count();

        $attendanceService = app(AttendanceService::class);
        $totalRates = [];
        foreach ($sessions as $session) {
            $summary = $attendanceService->getSessionAttendanceSummary($session);
            if ($summary['total'] > 0) {
                $totalRates[] = $summary['attendance_rate'];
            }
        }
        $avgAttendanceRate = count($totalRates) > 0 ? round(array_sum($totalRates) / count($totalRates), 1) : 0;

        $intakeName = $intakeId ? (CourseIntake::find($intakeId)?->name ?? "Intake #{$intakeId}") : 'All Cohorts';

        $output = [
            'course' => "{$course->title} ({$course->code})",
            'cohort' => $intakeName,
            'total_students_enrolled' => $enrolledCount,
            'quizzes' => [
                'total_completed_attempts' => $totalQuizAttempts,
                'average_score_percentage' => "{$avgScore}%",
                'pass_rate' => "{$passRate}%",
            ],
            'pending_grading' => [
                'assignments_awaiting_review' => $pendingAssignments,
                'assessments_awaiting_review' => $pendingAssessments,
                'total_pending' => $pendingAssignments + $pendingAssessments,
            ],
            'sessions_and_attendance' => [
                'total_sessions' => $totalSessions,
                'completed_sessions' => $completedSessions,
                'average_attendance_rate' => "{$avgAttendanceRate}%",
            ],
        ];

        return json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    protected function isAuthorizedCourse(int $courseId): bool
    {
        if ($this->instructor->isAdmin()) {
            return true;
        }

        return in_array($courseId, $this->allowedCourseIds, true);
    }
}

