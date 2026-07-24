<?php

namespace App\Filament\Instructor\Pages;

use App\Models\AssessmentSubmission;
use App\Models\AssignmentSubmission;
use App\Models\Attendance;
use App\Models\ChatMessage;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class Analytics extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static string|\UnitEnum|null $navigationGroup = null;

    protected static ?string $navigationLabel = 'Analytics';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Course Analytics';

    protected string $view = 'filament.instructor.pages.analytics';

    /** @var array<int, array<string, mixed>> */
    public array $completionRows = [];

    /** @var array<int, array<string, mixed>> */
    public array $quizScoreRows = [];

    /** @var array<int, array<string, mixed>> */
    public array $courseScoreRows = [];

    public ?float $overallAvgScore = null;

    /** @var array<string, array<string, mixed>> */
    public array $turnaround = [];

    /** @var array<int, array<string, mixed>> */
    public array $atRiskStudents = [];

    public function mount(): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $courseIds = $user->instructorCourses()->pluck('courses.id')->all();

        if (empty($courseIds)) {
            return;
        }

        $courses = Course::query()->whereIn('id', $courseIds)->orderBy('title')->get(['id', 'title', 'code']);

        $this->loadCompletion($courses, $courseIds);
        $this->loadQuizScores($courses, $courseIds);
        $this->loadTurnaround($courseIds);
        $this->loadAtRisk($courseIds);
    }

    /**
     * % of enrolled students who completed each course, using the same rule
     * as User::hasCompletedCourse(): a passed attempt for every active quiz
     * (courses without active quizzes count as complete on enrollment).
     */
    protected function loadCompletion(Collection $courses, array $courseIds): void
    {
        $enrolledCounts = Enrollment::query()
            ->whereIn('course_id', $courseIds)
            ->selectRaw('course_id, count(distinct user_id) as aggregate')
            ->groupBy('course_id')
            ->pluck('aggregate', 'course_id');

        $activeQuizCounts = Quiz::query()
            ->whereIn('course_id', $courseIds)
            ->where('is_active', true)
            ->selectRaw('course_id, count(*) as aggregate')
            ->groupBy('course_id')
            ->pluck('aggregate', 'course_id');

        $quizCourseMap = Quiz::query()
            ->whereIn('course_id', $courseIds)
            ->where('is_active', true)
            ->pluck('course_id', 'id');

        // One query: every distinct (student, quiz) pass in scope, grouped in PHP.
        $passes = QuizAttempt::query()
            ->whereIn('quiz_id', $quizCourseMap->keys())
            ->where('passed', true)
            ->distinct()
            ->get(['user_id', 'quiz_id']);

        $passCounts = []; // [course_id][user_id] => passed quiz count
        foreach ($passes as $pass) {
            $passCourseId = $quizCourseMap[$pass->quiz_id] ?? null;

            if ($passCourseId) {
                $passCounts[$passCourseId][$pass->user_id] = ($passCounts[$passCourseId][$pass->user_id] ?? 0) + 1;
            }
        }

        $this->completionRows = $courses->map(function (Course $course) use ($enrolledCounts, $activeQuizCounts, $passCounts): array {
            $enrolled = (int) ($enrolledCounts[$course->id] ?? 0);
            $quizCount = (int) ($activeQuizCounts[$course->id] ?? 0);

            $completed = $quizCount === 0
                ? $enrolled
                : collect($passCounts[$course->id] ?? [])->filter(fn (int $count): bool => $count >= $quizCount)->count();

            return [
                'course' => $course->title,
                'code' => $course->code,
                'enrolled' => $enrolled,
                'completed' => $completed,
                'active_quizzes' => $quizCount,
                'percentage' => $enrolled > 0 ? (int) round(($completed / $enrolled) * 100) : 0,
            ];
        })->all();
    }

    protected function loadQuizScores(Collection $courses, array $courseIds): void
    {
        $rows = QuizAttempt::query()
            ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
            ->whereIn('quizzes.course_id', $courseIds)
            ->whereNotNull('quiz_attempts.completed_at')
            ->groupBy('quiz_attempts.quiz_id', 'quizzes.title', 'quizzes.course_id')
            ->selectRaw('quiz_attempts.quiz_id, quizzes.title, quizzes.course_id, count(*) as attempts, avg(quiz_attempts.percentage) as avg_pct, sum(case when quiz_attempts.passed = 1 then 1 else 0 end) as passes')
            ->get();

        $this->overallAvgScore = QuizAttempt::query()
            ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
            ->whereIn('quizzes.course_id', $courseIds)
            ->whereNotNull('quiz_attempts.completed_at')
            ->avg('quiz_attempts.percentage');

        $this->overallAvgScore = $this->overallAvgScore !== null ? round((float) $this->overallAvgScore, 1) : null;

        $this->quizScoreRows = $rows->map(fn ($row): array => [
            'quiz' => $row->title,
            'course' => $courses->firstWhere('id', $row->course_id)?->title ?? '—',
            'attempts' => (int) $row->attempts,
            'avg_percentage' => round((float) $row->avg_pct, 1),
            'pass_rate' => $row->attempts > 0 ? (int) round(($row->passes / $row->attempts) * 100) : 0,
        ])->sortByDesc('attempts')->values()->all();

        // Per-course weighted average from the per-quiz rows.
        $this->courseScoreRows = $rows->groupBy('course_id')->map(function (Collection $courseRows, $courseId) use ($courses): array {
            $attempts = $courseRows->sum('attempts');
            $weighted = $courseRows->sum(fn ($row): float => $row->avg_pct * $row->attempts);

            return [
                'course' => $courses->firstWhere('id', $courseId)?->title ?? '—',
                'attempts' => (int) $attempts,
                'avg_percentage' => $attempts > 0 ? round($weighted / $attempts, 1) : 0,
            ];
        })->values()->all();
    }

    /**
     * Average submission-to-grading time. The submissions tables have no
     * graded_at column, so grading time is approximated by updated_at on
     * rows whose status is a graded state — accurate as long as graded
     * submissions are not edited again afterwards.
     */
    protected function loadTurnaround(array $courseIds): void
    {
        $gradedStatuses = ['Graded', 'Checked', 'Returned'];

        $assessments = AssessmentSubmission::query()
            ->join('assessments', 'assessment_submissions.assessment_id', '=', 'assessments.id')
            ->whereIn('assessments.course_id', $courseIds)
            ->whereIn('assessment_submissions.status', $gradedStatuses)
            ->whereNotNull('assessment_submissions.submitted_at')
            ->get(['assessment_submissions.submitted_at', 'assessment_submissions.updated_at']);

        $assignments = AssignmentSubmission::query()
            ->join('assignments', 'assignment_submissions.assignment_id', '=', 'assignments.id')
            ->whereIn('assignments.course_id', $courseIds)
            ->whereIn('assignment_submissions.status', $gradedStatuses)
            ->whereNotNull('assignment_submissions.submitted_at')
            ->get(['assignment_submissions.submitted_at', 'assignment_submissions.updated_at']);

        $this->turnaround = [
            'assessments' => $this->summarizeTurnaround($assessments),
            'assignments' => $this->summarizeTurnaround($assignments),
        ];
    }

    protected function summarizeTurnaround(Collection $submissions): array
    {
        $hours = $submissions
            ->map(fn ($submission): float => max(0, $submission->submitted_at->diffInSeconds($submission->updated_at) / 3600));

        $count = $hours->count();
        $avgHours = $count > 0 ? $hours->avg() : null;

        return [
            'count' => $count,
            'avg_hours' => $avgHours !== null ? round($avgHours, 1) : null,
            'label' => $avgHours === null
                ? '—'
                : ($avgHours < 48 ? round($avgHours, 1).' hours' : round($avgHours / 24, 1).' days'),
        ];
    }

    /**
     * Students with no activity in the last 14 days. Activity = latest of
     * quiz attempt, assessment/assignment submission, chat message (global,
     * chat is not course-scoped), or attendance marking.
     */
    protected function loadAtRisk(array $courseIds): void
    {
        $enrollments = Enrollment::query()
            ->with(['user:id,name', 'course:id,title'])
            ->whereIn('course_id', $courseIds)
            ->get();

        $studentIds = $enrollments->pluck('user_id')->unique()->values()->all();

        if (empty($studentIds)) {
            $this->atRiskStudents = [];

            return;
        }

        $cutoff = Carbon::now()->subDays(14);

        $lastQuiz = QuizAttempt::query()
            ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
            ->whereIn('quizzes.course_id', $courseIds)
            ->groupBy('quiz_attempts.user_id')
            ->selectRaw('quiz_attempts.user_id, max(quiz_attempts.created_at) as last_at')
            ->pluck('last_at', 'user_id');

        $lastAssessment = AssessmentSubmission::query()
            ->join('assessments', 'assessment_submissions.assessment_id', '=', 'assessments.id')
            ->whereIn('assessments.course_id', $courseIds)
            ->groupBy('assessment_submissions.user_id')
            ->selectRaw('assessment_submissions.user_id, max(assessment_submissions.created_at) as last_at')
            ->pluck('last_at', 'user_id');

        $lastAssignment = AssignmentSubmission::query()
            ->join('assignments', 'assignment_submissions.assignment_id', '=', 'assignments.id')
            ->whereIn('assignments.course_id', $courseIds)
            ->groupBy('assignment_submissions.user_id')
            ->selectRaw('assignment_submissions.user_id, max(assignment_submissions.created_at) as last_at')
            ->pluck('last_at', 'user_id');

        $lastChat = ChatMessage::query()
            ->whereIn('user_id', $studentIds)
            ->groupBy('user_id')
            ->selectRaw('user_id, max(created_at) as last_at')
            ->pluck('last_at', 'user_id');

        $lastAttendance = Attendance::query()
            ->join('course_sessions', 'attendances.course_session_id', '=', 'course_sessions.id')
            ->whereIn('course_sessions.course_id', $courseIds)
            ->groupBy('attendances.user_id')
            ->selectRaw('attendances.user_id, max(attendances.updated_at) as last_at')
            ->pluck('last_at', 'user_id');

        $coursesByStudent = $enrollments->groupBy('user_id')
            ->map(fn (Collection $rows): string => $rows->pluck('course.title')->filter()->unique()->implode(', '));

        $this->atRiskStudents = collect($studentIds)
            ->map(function (int $studentId) use ($lastQuiz, $lastAssessment, $lastAssignment, $lastChat, $lastAttendance, $coursesByStudent, $enrollments, $cutoff): ?array {
                $lastAt = collect([
                    $lastQuiz[$studentId] ?? null,
                    $lastAssessment[$studentId] ?? null,
                    $lastAssignment[$studentId] ?? null,
                    $lastChat[$studentId] ?? null,
                    $lastAttendance[$studentId] ?? null,
                ])->filter()->max();

                $lastAtCarbon = $lastAt ? Carbon::parse($lastAt) : null;

                if ($lastAtCarbon && $lastAtCarbon->gte($cutoff)) {
                    return null; // recently active
                }

                return [
                    'name' => $enrollments->firstWhere('user_id', $studentId)?->user?->name ?? 'Student #'.$studentId,
                    'courses' => $coursesByStudent[$studentId] ?? '—',
                    'last_activity' => $lastAtCarbon?->format('M d, Y'),
                    'days_inactive' => $lastAtCarbon ? (int) $lastAtCarbon->diffInDays(Carbon::now()) : null,
                    'sort_ts' => $lastAtCarbon?->timestamp ?? 0,
                ];
            })
            ->filter()
            ->sortBy('sort_ts') // never-active (0) first, then longest inactive
            ->values()
            ->all();
    }
}
