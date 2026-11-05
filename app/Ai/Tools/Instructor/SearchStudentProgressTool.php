<?php

namespace App\Ai\Tools\Instructor;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\CourseSession;
use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SearchStudentProgressTool implements Tool
{
    public function __construct(protected User $instructor, protected array $allowedCourseIds)
    {
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Search for enrolled students by name, email, or student ID across your authorized courses. Returns academic profile, attendance rate, quiz performances, assignment submission counts, and at-risk assessment.';
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()->description('Student name, email, or username to search for.')->required(),
            'course_id' => $schema->integer()->description('Optional course ID to scope the student search to.')->nullable(),
        ];
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $search = trim((string) ($request['query'] ?? ''));
        $courseId = ! empty($request['course_id']) ? (int) $request['course_id'] : null;

        if ($search === '') {
            return 'Error: Please provide a student name, email, or keyword in the "query" parameter.';
        }

        $activeCourseIds = $this->allowedCourseIds;
        if ($courseId !== null) {
            if (! $this->isAuthorizedCourse($courseId)) {
                return "Error: Unauthorized. You cannot inspect students for course ID #{$courseId}.";
            }
            $activeCourseIds = [$courseId];
        }

        if (empty($activeCourseIds) && ! $this->instructor->isAdmin()) {
            return 'Notice: You currently have no authorized courses assigned.';
        }

        $studentsQuery = User::query()
            ->whereHas('enrollments', function ($q) use ($activeCourseIds) {
                if (! $this->instructor->isAdmin()) {
                    $q->whereIn('course_id', $activeCourseIds);
                }
            })
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            })
            ->with(['enrollments' => function ($q) use ($activeCourseIds) {
                if (! $this->instructor->isAdmin()) {
                    $q->whereIn('course_id', $activeCourseIds);
                }
                $q->with('course:id,title,code');
            }])
            ->limit(10)
            ->get();

        if ($studentsQuery->isEmpty()) {
            return "No enrolled students found matching '{$search}' in your authorized courses.";
        }

        $results = [];

        foreach ($studentsQuery as $student) {
            $enrollmentsData = [];

            foreach ($student->enrollments as $enrollment) {
                $cId = $enrollment->course_id;
                $courseName = $enrollment->course?->title ?? "Course #{$cId}";

                // 1. Attendance Analytics for this course
                $sessionIds = CourseSession::query()
                    ->where('course_id', $cId)
                    ->pluck('id');

                $attendances = Attendance::query()
                    ->whereIn('course_session_id', $sessionIds)
                    ->where('user_id', $student->id)
                    ->get();

                $totalSessions = $sessionIds->count();
                $presentCount = $attendances->where('status', Attendance::STATUS_PRESENT)->count();
                $lateCount = $attendances->where('status', Attendance::STATUS_LATE)->count();
                $absentCount = $attendances->where('status', Attendance::STATUS_ABSENT)->count();
                $excusedCount = $attendances->where('status', Attendance::STATUS_APOLOGY)->count();
                
                $attendedRate = $totalSessions > 0
                    ? round((($presentCount + ($lateCount * 0.75)) / $totalSessions) * 100, 1)
                    : 100.0;

                // 2. Quiz Performance
                $quizIds = Quiz::query()->where('course_id', $cId)->pluck('id');
                $attempts = QuizAttempt::query()
                    ->whereIn('quiz_id', $quizIds)
                    ->where('user_id', $student->id)
                    ->get();

                $totalAttempts = $attempts->count();
                $avgQuizScore = $totalAttempts > 0 ? round($attempts->avg('percentage'), 1) : null;
                $passedQuizzes = $attempts->where('passed', true)->count();

                // 3. Assignment Submissions
                $assignmentIds = Assignment::query()->where('course_id', $cId)->pluck('id');
                $submissions = AssignmentSubmission::query()
                    ->whereIn('assignment_id', $assignmentIds)
                    ->where('user_id', $student->id)
                    ->get();

                $totalAssignments = $assignmentIds->count();
                $submittedCount = $submissions->count();
                $gradedSubmissions = $submissions->whereNotNull('grade');
                $avgGrade = $gradedSubmissions->count() > 0 ? round($gradedSubmissions->avg('grade'), 1) : null;

                // 4. At-Risk Assessment
                $isAtRisk = false;
                $riskFlags = [];

                if ($totalSessions >= 3 && $attendedRate < 75.0) {
                    $isAtRisk = true;
                    $riskFlags[] = "Low attendance rate ({$attendedRate}% across {$totalSessions} sessions)";
                }

                if ($avgQuizScore !== null && $avgQuizScore < 50.0) {
                    $isAtRisk = true;
                    $riskFlags[] = "Failing quiz average ({$avgQuizScore}%)";
                }

                if ($totalAssignments >= 2 && $submittedCount < ($totalAssignments / 2)) {
                    $isAtRisk = true;
                    $riskFlags[] = "Missing assignments ({$submittedCount} of {$totalAssignments} submitted)";
                }

                $enrollmentsData[] = [
                    'course_id' => $cId,
                    'course_title' => $courseName,
                    'enrolled_at' => $enrollment->created_at?->format('Y-m-d'),
                    'attendance' => [
                        'total_sessions' => $totalSessions,
                        'present' => $presentCount,
                        'late' => $lateCount,
                        'absent' => $absentCount,
                        'excused' => $excusedCount,
                        'rate_percentage' => $attendedRate,
                    ],
                    'quizzes' => [
                        'total_course_quizzes' => $quizIds->count(),
                        'quizzes_attempted' => $totalAttempts,
                        'quizzes_passed' => $passedQuizzes,
                        'average_score_percentage' => $avgQuizScore,
                    ],
                    'assignments' => [
                        'total_course_assignments' => $totalAssignments,
                        'submitted_count' => $submittedCount,
                        'average_grade' => $avgGrade,
                    ],
                    'at_risk' => $isAtRisk,
                    'risk_flags' => $riskFlags,
                ];
            }

            $results[] = [
                'student_id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'username' => $student->username,
                'enrollments' => $enrollmentsData,
            ];
        }

        return json_encode([
            'total_students_found' => count($results),
            'students' => $results,
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
