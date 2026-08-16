<?php

namespace App\Filament\Instructor\Pages;

use App\Filament\Instructor\Concerns\ScopedToInstructor;
use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Notifications\CertificateIssuedNotification;
use App\Services\CertificateService;
use App\Services\GamificationService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentResults extends Page
{
    use ScopedToInstructor;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static string|\UnitEnum|null $navigationGroup = 'GRADING & EVALUATIONS';

    protected static ?string $navigationLabel = 'Student Results';

    protected static ?int $navigationSort = 10;

    protected static ?string $title = 'Task & Student Results';

    protected static ?string $slug = 'student-results';

    protected string $view = 'filament.instructor.pages.student-results';

    public string $viewMode = 'tasks'; // 'tasks' or 'students'

    public string $activeCategory = 'quizzes'; // 'all', 'quizzes', 'assignments', 'assessments'

    public string $taskTypeFilter = 'all';

    public string $selectedTaskKey = '';

    public string $courseFilter = '';

    public string $trackFilter = '';

    public string $performanceFilter = '';

    public string $search = '';

    public string $sortBy = 'score_desc';

    public ?int $selectedQuizAttemptId = null;

    /** @var array<string, bool> */
    public array $expandedTasks = [];

    /** @var array<int, bool> */
    public array $expandedStudents = [];

    /** @var array<int, string> */
    public array $activeTabs = [];

    public function mount(): void
    {
        $this->expandedTasks = [];
        $this->expandedStudents = [];
        $this->activeTabs = [];
        $this->activeCategory = 'quizzes';
        $this->selectedTaskKey = '';
        $this->selectedQuizAttemptId = null;
    }

    public function selectCategory(string $category): void
    {
        if ($this->activeCategory === $category) {
            $this->activeCategory = 'all';
            $this->taskTypeFilter = 'all';
        } else {
            $this->activeCategory = $category;
            $this->taskTypeFilter = $category;
        }
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = $mode;
    }

    public function setTaskType(string $type): void
    {
        $this->taskTypeFilter = $type;
        $this->activeCategory = $type;
    }

    public function isTaskExpanded(string $taskKey): bool
    {
        if (! array_key_exists($taskKey, $this->expandedTasks)) {
            return true; // Default to open so instructor immediately views taken tasks
        }

        return (bool) $this->expandedTasks[$taskKey];
    }

    public function toggleTask(string $taskKey): void
    {
        $this->expandedTasks[$taskKey] = ! $this->isTaskExpanded($taskKey);
    }

    public function selectTask(string $taskKey): void
    {
        $this->toggleTask($taskKey);
        $this->selectedTaskKey = $this->isTaskExpanded($taskKey) ? $taskKey : '';
    }

    public function closeTask(): void
    {
        $this->selectedTaskKey = '';
    }

    public function expandAllTasks(): void
    {
        $tasks = $this->getTasksData();
        foreach (['quizzes', 'assignments', 'assessments'] as $type) {
            foreach ($tasks[$type] as $t) {
                $this->expandedTasks[$t['key']] = true;
            }
        }
    }

    public function collapseAllTasks(): void
    {
        $tasks = $this->getTasksData();
        foreach (['quizzes', 'assignments', 'assessments'] as $type) {
            foreach ($tasks[$type] as $t) {
                $this->expandedTasks[$t['key']] = false;
            }
        }
        $this->selectedTaskKey = '';
    }

    public function toggleExpand(int $studentId): void
    {
        if (! empty($this->expandedStudents[$studentId])) {
            unset($this->expandedStudents[$studentId]);
        } else {
            $this->expandedStudents[$studentId] = true;
            if (! isset($this->activeTabs[$studentId])) {
                $this->activeTabs[$studentId] = 'all';
            }
        }
    }

    public function setTab(int $studentId, string $tab): void
    {
        $this->activeTabs[$studentId] = $tab;
    }

    public function expandAll(): void
    {
        $students = $this->getStudentsData();
        foreach ($students as $row) {
            $this->expandedStudents[$row['id']] = true;
            if (! isset($this->activeTabs[$row['id']])) {
                $this->activeTabs[$row['id']] = 'all';
            }
        }
        $this->expandAllTasks();
    }

    public function collapseAll(): void
    {
        $this->expandedStudents = [];
        $this->collapseAllTasks();
    }

    public function resetFilters(): void
    {
        $this->courseFilter = '';
        $this->trackFilter = '';
        $this->performanceFilter = '';
        $this->taskTypeFilter = 'all';
        $this->activeCategory = 'all';
        $this->search = '';
        $this->sortBy = 'score_desc';
        $this->selectedTaskKey = '';
    }

    public function markCourseComplete(int $studentId, int $courseId): void
    {
        $scopedCourseIds = static::instructorCourseIds();
        if (! in_array($courseId, $scopedCourseIds, true)) {
            Notification::make()
                ->title('Unauthorized')
                ->danger()
                ->send();

            return;
        }

        $student = User::query()->find($studentId);
        $course = Course::query()->find($courseId);
        $enrollment = Enrollment::query()
            ->where('user_id', $studentId)
            ->where('course_id', $courseId)
            ->first();

        if (! $student || ! $course || ! $enrollment) {
            Notification::make()
                ->title('Student enrollment not found')
                ->warning()
                ->send();

            return;
        }

        $enrollment->markAsCompleted(auth()->user());

        // Automatically issue certificate and notify student
        $certificate = app(CertificateService::class)->issue($student, $course, force: true);

        if ($certificate && $certificate->wasRecentlyCreated) {
            try {
                $student->notify(new CertificateIssuedNotification($certificate));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        // Award course completion XP and badges (Graduate, Mastermind)
        try {
            app(GamificationService::class)->awardCourseCompleted($student, $course);
        } catch (\Throwable $e) {
            report($e);
        }

        Notification::make()
            ->title('Program Marked Complete!')
            ->body('Course completion recorded. Certificate and completion badges are now ready for ' . $student->name . '.')
            ->success()
            ->send();
    }

    public function unmarkCourseComplete(int $studentId, int $courseId): void
    {
        $scopedCourseIds = static::instructorCourseIds();
        if (! in_array($courseId, $scopedCourseIds, true)) {
            return;
        }

        $student = User::query()->find($studentId);
        $course = Course::query()->find($courseId);
        $enrollment = Enrollment::query()
            ->where('user_id', $studentId)
            ->where('course_id', $courseId)
            ->first();

        if ($enrollment) {
            $enrollment->markAsIncomplete();

            Certificate::query()
                ->where('user_id', $studentId)
                ->where('course_id', $courseId)
                ->delete();

            if ($student && $course) {
                try {
                    app(GamificationService::class)->revokeCourseCompleted($student, $course);
                } catch (\Throwable $e) {
                    report($e);
                }
            }

            Notification::make()
                ->title('Completion Status Reset')
                ->body('Course completion status, certificate, and completion badges were reset for ' . ($student?->name ?? 'student') . '.')
                ->info()
                ->send();
        }
    }

    public function grantQuizRetake(int $studentId, int $quizId): void
    {
        $scopedCourseIds = static::instructorCourseIds();
        $quiz = Quiz::query()->find($quizId);

        if (! $quiz || ! in_array($quiz->course_id, $scopedCourseIds, true)) {
            Notification::make()->title('Unauthorized')->danger()->send();

            return;
        }

        $attempt = QuizAttempt::query()
            ->where('user_id', $studentId)
            ->where('quiz_id', $quizId)
            ->whereNotNull('completed_at')
            ->latest('id')
            ->first();

        if (! $attempt) {
            Notification::make()->title('No completed attempt found to grant retake for.')->warning()->send();

            return;
        }

        $attempt->grantRetake(auth()->user());

        $student = User::query()->find($studentId);
        if ($student) {
            Notification::make()
                ->title('Second Chance Granted: ' . $quiz->title)
                ->body('Your instructor has granted you another chance to take this quiz. Your recorded score on the retake will be capped at the passing mark (' . $quiz->pass_percentage . '%).')
                ->success()
                ->sendToDatabase($student);
        }

        Notification::make()
            ->title('Second Chance Granted!')
            ->body('Student ' . ($student?->name ?? 'User #' . $studentId) . ' can now retake ' . $quiz->title . '.')
            ->success()
            ->send();
    }

    public function revokeQuizRetake(int $studentId, int $quizId): void
    {
        $scopedCourseIds = static::instructorCourseIds();
        $quiz = Quiz::query()->find($quizId);

        if (! $quiz || ! in_array($quiz->course_id, $scopedCourseIds, true)) {
            return;
        }

        $attempt = QuizAttempt::query()
            ->where('user_id', $studentId)
            ->where('quiz_id', $quizId)
            ->whereNotNull('completed_at')
            ->latest('id')
            ->first();

        if ($attempt) {
            $attempt->revokeRetake();
            Notification::make()->title('Quiz retake permission revoked.')->info()->send();
        }
    }

    public function grantAssignmentRetake(int $submissionId): void
    {
        $scopedCourseIds = static::instructorCourseIds();
        $submission = AssignmentSubmission::with('assignment')->find($submissionId);

        if (! $submission || ! in_array($submission->assignment?->course_id, $scopedCourseIds, true)) {
            Notification::make()->title('Unauthorized')->danger()->send();

            return;
        }

        $submission->grantRetake(auth()->user());

        $student = $submission->user;
        if ($student) {
            Notification::make()
                ->title('Second Chance Granted: ' . ($submission->assignment?->name ?? 'Assignment'))
                ->body('Your instructor has granted you another chance to resubmit this assignment. Recorded grade will be capped at the passing mark (50%).')
                ->success()
                ->sendToDatabase($student);
        }

        Notification::make()
            ->title('Second Chance Granted!')
            ->body('Student ' . ($student?->name ?? 'User #' . $submission->user_id) . ' can now resubmit ' . ($submission->assignment?->name ?? 'assignment') . '.')
            ->success()
            ->send();
    }

    public function revokeAssignmentRetake(int $submissionId): void
    {
        $scopedCourseIds = static::instructorCourseIds();
        $submission = AssignmentSubmission::with('assignment')->find($submissionId);

        if (! $submission || ! in_array($submission->assignment?->course_id, $scopedCourseIds, true)) {
            return;
        }

        $submission->revokeRetake();
        Notification::make()->title('Assignment resubmission permission revoked.')->info()->send();
    }

    public function grantAssessmentRetake(int $submissionId): void
    {
        $scopedCourseIds = static::instructorCourseIds();
        $submission = AssessmentSubmission::with('assessment')->find($submissionId);

        if (! $submission || ! in_array($submission->assessment?->course_id, $scopedCourseIds, true)) {
            Notification::make()->title('Unauthorized')->danger()->send();

            return;
        }

        $submission->grantRetake(auth()->user());

        $student = $submission->user;
        if ($student) {
            Notification::make()
                ->title('Second Chance Granted: ' . ($submission->assessment?->name ?? 'Assessment'))
                ->body('Your instructor has granted you another chance to resubmit this assessment. Recorded score will be capped at the passing mark (50%).')
                ->success()
                ->sendToDatabase($student);
        }

        Notification::make()
            ->title('Second Chance Granted!')
            ->body('Student ' . ($student?->name ?? 'User #' . $submission->user_id) . ' can now resubmit ' . ($submission->assessment?->name ?? 'assessment') . '.')
            ->success()
            ->send();
    }

    public function revokeAssessmentRetake(int $submissionId): void
    {
        $scopedCourseIds = static::instructorCourseIds();
        $submission = AssessmentSubmission::with('assessment')->find($submissionId);

        if (! $submission || ! in_array($submission->assessment?->course_id, $scopedCourseIds, true)) {
            return;
        }

        $submission->revokeRetake();
        Notification::make()->title('Assessment resubmission permission revoked.')->info()->send();
    }

    public function viewQuizAttempt(int $attemptId): void
    {
        $scopedCourseIds = static::instructorCourseIds();
        $attempt = QuizAttempt::with(['quiz.course', 'quiz.questions.options', 'answers.option', 'answers.question', 'user'])->find($attemptId);

        if (! $attempt || ! in_array($attempt->quiz?->course_id, $scopedCourseIds, true)) {
            Notification::make()->title('Quiz attempt not found or unauthorized.')->danger()->send();

            return;
        }

        $this->selectedQuizAttemptId = $attempt->id;
    }

    public function closeQuizAttemptModal(): void
    {
        $this->selectedQuizAttemptId = null;
    }

    public function getSelectedQuizAttemptProperty(): ?QuizAttempt
    {
        if (! $this->selectedQuizAttemptId) {
            return null;
        }

        return QuizAttempt::with(['quiz.course', 'quiz.questions.options', 'answers.option', 'answers.question', 'user'])->find($this->selectedQuizAttemptId);
    }

    /**
     * Options for the Course filter dropdown.
     *
     * @return array<string, string>
     */
    public function getCourseOptionsProperty(): array
    {
        return static::instructorCourseOptions();
    }

    /**
     * Get Tasks Data (Quizzes, Assignments, Assessments) with student results in DESC order.
     * ONLY returns tasks that have been TAKEN/SUBMITTED, ordered in FILO order (most recently taken first).
     *
     * @return array{quizzes: array<int, array<string, mixed>>, assignments: array<int, array<string, mixed>>, assessments: array<int, array<string, mixed>>, totals: array<string, int>, category_stats: array<string, array<string, mixed>>}
     */
    public function getTasksData(): array
    {
        $user = auth()->user();
        if (! $user) {
            return [
                'quizzes' => [],
                'assignments' => [],
                'assessments' => [],
                'totals' => ['quizzes' => 0, 'assignments' => 0, 'assessments' => 0, 'total' => 0],
                'category_stats' => [
                    'quizzes' => ['count' => 0, 'attempts' => 0, 'passed' => 0, 'avg_score' => null],
                    'assignments' => ['count' => 0, 'submissions' => 0, 'graded' => 0, 'avg_score' => null],
                    'assessments' => ['count' => 0, 'submissions' => 0, 'graded' => 0, 'avg_score' => null],
                ],
            ];
        }

        $scopedCourseIds = static::instructorCourseIds();
        if (empty($scopedCourseIds)) {
            return [
                'quizzes' => [],
                'assignments' => [],
                'assessments' => [],
                'totals' => ['quizzes' => 0, 'assignments' => 0, 'assessments' => 0, 'total' => 0],
                'category_stats' => [
                    'quizzes' => ['count' => 0, 'attempts' => 0, 'passed' => 0, 'avg_score' => null],
                    'assignments' => ['count' => 0, 'submissions' => 0, 'graded' => 0, 'avg_score' => null],
                    'assessments' => ['count' => 0, 'submissions' => 0, 'graded' => 0, 'avg_score' => null],
                ],
            ];
        }

        if (! empty($this->courseFilter) && in_array((int) $this->courseFilter, $scopedCourseIds, true)) {
            $effectiveCourseIds = [(int) $this->courseFilter];
        } else {
            $effectiveCourseIds = $scopedCourseIds;
        }

        $searchTerm = trim($this->search);

        // 1. QUIZZES (Only appear once taken; ordered in FILO order)
        $quizzesQuery = Quiz::query()
            ->whereIn('course_id', $effectiveCourseIds)
            ->where('is_active', true)
            ->whereHas('attempts', fn ($q) => $q->whereNotNull('completed_at'))
            ->with(['course:id,title,code', 'attempts' => function ($q) {
                $q->whereNotNull('completed_at')->with('user:id,name,email,track')->orderBy('score', 'desc');
            }]);

        if (! empty($searchTerm)) {
            $quizzesQuery->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                    ->orWhereHas('course', fn ($c) => $c->where('title', 'like', "%{$searchTerm}%"))
                    ->orWhereHas('attempts.user', fn ($u) => $u->where('name', 'like', "%{$searchTerm}%")->orWhere('email', 'like', "%{$searchTerm}%"));
            });
        }

        $quizzes = $quizzesQuery->get();
        $quizList = [];
        $allQuizScores = [];
        $totalQuizAttempts = 0;
        $totalQuizPassed = 0;

        foreach ($quizzes as $quiz) {
            $attempts = $quiz->attempts;
            if (! empty($this->trackFilter)) {
                $attempts = $attempts->filter(fn ($a) => strtolower((string) $a->user?->track) === strtolower($this->trackFilter));
            }
            if (! empty($searchTerm)) {
                $attempts = $attempts->filter(function ($a) use ($searchTerm) {
                    return str_contains(strtolower((string) $a->user?->name), strtolower($searchTerm))
                        || str_contains(strtolower((string) $a->user?->email), strtolower($searchTerm))
                        || str_contains(strtolower((string) $a->quiz?->title), strtolower($searchTerm));
                });
            }

            // Only appear once taken
            if ($attempts->isEmpty()) {
                continue;
            }

            // Most recent attempt timestamp for FILO ordering
            $latestTakenAt = $attempts->max(fn ($a) => $a->completed_at ? $a->completed_at->timestamp : ($a->created_at ? $a->created_at->timestamp : 0)) ?? 0;

            // Group by student and pick best attempt, then sort in DESCENDING order
            $studentResults = $attempts->groupBy('user_id')->map(function ($userAttempts) use ($quiz) {
                $best = $userAttempts->sortByDesc('percentage')->first();
                $latest = $userAttempts->sortByDesc('id')->first();
                $user = $best->user;
                $retakeAllowed = (bool) ($latest?->retake_allowed || $best?->retake_allowed);

                return [
                    'student_id' => $best->user_id,
                    'quiz_id' => $quiz->id,
                    'attempt_id' => $latest->id,
                    'student_name' => $user?->name ?? 'Unknown',
                    'student_email' => $user?->email ?? '',
                    'student_track' => $user?->track ?? 'Beginner',
                    'score' => $best->score,
                    'total_points' => $best->total_points,
                    'percentage' => $best->percentage,
                    'passed' => (bool) $best->passed,
                    'is_retake' => (bool) $best->is_retake,
                    'retake_allowed' => $retakeAllowed,
                    'raw_score' => $best->raw_score,
                    'attempts_count' => $userAttempts->count(),
                    'completed_at' => $best->completed_at?->format('M d, Y h:i A') ?? '—',
                ];
            })->sortByDesc('percentage')->values()->all();

            if (empty($studentResults)) {
                continue;
            }

            $scores = array_filter(array_column($studentResults, 'percentage'), fn ($v) => $v !== null);
            $avgScore = count($scores) > 0 ? round(array_sum($scores) / count($scores), 1) : null;
            $passedCount = count(array_filter($studentResults, fn ($r) => $r['passed']));
            $topScore = count($scores) > 0 ? max($scores) : null;
            $lowScore = count($scores) > 0 ? min($scores) : null;

            foreach ($scores as $s) {
                $allQuizScores[] = $s;
            }
            $totalQuizAttempts += count($studentResults);
            $totalQuizPassed += $passedCount;

            $quizList[] = [
                'key' => 'quiz_' . $quiz->id,
                'type' => 'Quiz',
                'id' => $quiz->id,
                'title' => $quiz->title,
                'course' => $quiz->course?->title ?? '—',
                'course_code' => $quiz->course?->code ?? '',
                'pass_percentage' => $quiz->pass_percentage ?? 50,
                'time_limit' => $quiz->time_limit_minutes,
                'results_count' => count($studentResults),
                'passed_count' => $passedCount,
                'avg_score' => $avgScore,
                'top_score' => $topScore,
                'low_score' => $lowScore,
                'latest_taken_at' => $latestTakenAt,
                'results' => $studentResults, // Ordered in DESC order
            ];
        }

        // Sort quizzes in FILO order (most recently taken at top)
        usort($quizList, fn ($a, $b) => $b['latest_taken_at'] <=> $a['latest_taken_at']);

        // 2. ASSIGNMENTS (Only appear once taken/submitted; ordered in FILO order)
        $assignmentsQuery = Assignment::query()
            ->whereIn('course_id', $effectiveCourseIds)
            ->released()
            ->whereHas('submissions')
            ->with(['course:id,title,code', 'submissions' => function ($q) {
                $q->with('user:id,name,email,track')->orderByRaw('grade IS NULL, grade DESC, submitted_at DESC');
            }]);

        if (! empty($searchTerm)) {
            $assignmentsQuery->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhereHas('course', fn ($c) => $c->where('title', 'like', "%{$searchTerm}%"))
                    ->orWhereHas('submissions.user', fn ($u) => $u->where('name', 'like', "%{$searchTerm}%")->orWhere('email', 'like', "%{$searchTerm}%"));
            });
        }

        $assignments = $assignmentsQuery->get();
        $assignmentList = [];
        $allAssignmentGrades = [];
        $totalAssignmentSubs = 0;
        $totalAssignmentGraded = 0;

        foreach ($assignments as $assignment) {
            $submissions = $assignment->submissions;
            if (! empty($this->trackFilter)) {
                $submissions = $submissions->filter(fn ($s) => strtolower((string) $s->user?->track) === strtolower($this->trackFilter));
            }
            if (! empty($searchTerm)) {
                $submissions = $submissions->filter(function ($s) use ($searchTerm) {
                    return str_contains(strtolower((string) $s->user?->name), strtolower($searchTerm))
                        || str_contains(strtolower((string) $s->user?->email), strtolower($searchTerm))
                        || str_contains(strtolower((string) $s->assignment?->name), strtolower($searchTerm));
                });
            }

            // Only appear once submitted
            if ($submissions->isEmpty()) {
                continue;
            }

            // Most recent submission timestamp for FILO ordering
            $latestTakenAt = $submissions->max(fn ($s) => $s->submitted_at ? $s->submitted_at->timestamp : ($s->created_at ? $s->created_at->timestamp : 0)) ?? 0;

            // Sort in DESCENDING order by grade (highest grade first, null/ungraded at bottom)
            $studentResults = $submissions->map(function ($sub) {
                $user = $sub->user;
                return [
                    'submission_id' => $sub->id,
                    'student_id' => $sub->user_id,
                    'student_name' => $user?->name ?? 'Unknown',
                    'student_email' => $user?->email ?? '',
                    'student_track' => $user?->track ?? 'Beginner',
                    'status' => $sub->status ?: 'Submitted',
                    'grade' => $sub->grade !== null ? (float) $sub->grade : null,
                    'is_retake' => (bool) $sub->is_retake,
                    'retake_allowed' => (bool) $sub->retake_allowed,
                    'raw_grade' => $sub->raw_grade,
                    'feedback' => $sub->feedback,
                    'submitted_at' => $sub->submitted_at?->format('M d, Y h:i A') ?? '—',
                ];
            })->sort(function ($a, $b) {
                if ($a['grade'] === null && $b['grade'] === null) {
                    return 0;
                }
                if ($a['grade'] === null) {
                    return 1;
                }
                if ($b['grade'] === null) {
                    return -1;
                }
                return $b['grade'] <=> $a['grade'];
            })->values()->all();

            if (empty($studentResults)) {
                continue;
            }

            $grades = array_filter(array_column($studentResults, 'grade'), fn ($v) => $v !== null);
            $avgGrade = count($grades) > 0 ? round(array_sum($grades) / count($grades), 1) : null;
            $gradedCount = count($grades);
            $topScore = count($grades) > 0 ? max($grades) : null;
            $lowScore = count($grades) > 0 ? min($grades) : null;

            foreach ($grades as $g) {
                $allAssignmentGrades[] = $g;
            }
            $totalAssignmentSubs += count($studentResults);
            $totalAssignmentGraded += $gradedCount;

            $assignmentList[] = [
                'key' => 'assignment_' . $assignment->id,
                'type' => 'Assignment',
                'id' => $assignment->id,
                'title' => $assignment->name,
                'course' => $assignment->course?->title ?? '—',
                'course_code' => $assignment->course?->code ?? '',
                'due_date' => $assignment->due_date?->format('M d, Y') ?? '—',
                'target_level' => $assignment->target_level,
                'results_count' => count($studentResults),
                'graded_count' => $gradedCount,
                'avg_score' => $avgGrade,
                'top_score' => $topScore,
                'low_score' => $lowScore,
                'latest_taken_at' => $latestTakenAt,
                'results' => $studentResults, // Ordered in DESC order
            ];
        }

        // Sort assignments in FILO order (most recently submitted at top)
        usort($assignmentList, fn ($a, $b) => $b['latest_taken_at'] <=> $a['latest_taken_at']);

        // 3. ASSESSMENTS (Only appear once taken/submitted; ordered in FILO order)
        $assessmentsQuery = Assessment::query()
            ->whereIn('course_id', $effectiveCourseIds)
            ->released()
            ->whereHas('submissions')
            ->with(['course:id,title,code', 'submissions' => function ($q) {
                $q->with('user:id,name,email,track')->orderByRaw('score IS NULL, score DESC, submitted_at DESC');
            }]);

        if (! empty($searchTerm)) {
            $assessmentsQuery->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhereHas('course', fn ($c) => $c->where('title', 'like', "%{$searchTerm}%"))
                    ->orWhereHas('submissions.user', fn ($u) => $u->where('name', 'like', "%{$searchTerm}%")->orWhere('email', 'like', "%{$searchTerm}%"));
            });
        }

        $assessments = $assessmentsQuery->get();
        $assessmentList = [];
        $allAssessmentScores = [];
        $totalAssessmentSubs = 0;
        $totalAssessmentGraded = 0;

        foreach ($assessments as $assessment) {
            $submissions = $assessment->submissions;
            if (! empty($this->trackFilter)) {
                $submissions = $submissions->filter(fn ($s) => strtolower((string) $s->user?->track) === strtolower($this->trackFilter));
            }
            if (! empty($searchTerm)) {
                $submissions = $submissions->filter(function ($s) use ($searchTerm) {
                    return str_contains(strtolower((string) $s->user?->name), strtolower($searchTerm))
                        || str_contains(strtolower((string) $s->user?->email), strtolower($searchTerm))
                        || str_contains(strtolower((string) $s->assessment?->name), strtolower($searchTerm));
                });
            }

            // Only appear once submitted
            if ($submissions->isEmpty()) {
                continue;
            }

            // Most recent submission timestamp for FILO ordering
            $latestTakenAt = $submissions->max(fn ($s) => $s->submitted_at ? $s->submitted_at->timestamp : ($s->created_at ? $s->created_at->timestamp : 0)) ?? 0;

            // Sort in DESCENDING order by score
            $studentResults = $submissions->map(function ($sub) {
                $user = $sub->user;
                return [
                    'submission_id' => $sub->id,
                    'student_id' => $sub->user_id,
                    'student_name' => $user?->name ?? 'Unknown',
                    'student_email' => $user?->email ?? '',
                    'student_track' => $user?->track ?? 'Beginner',
                    'status' => $sub->status ?: 'Submitted',
                    'score' => $sub->score !== null ? (float) $sub->score : null,
                    'is_retake' => (bool) $sub->is_retake,
                    'retake_allowed' => (bool) $sub->retake_allowed,
                    'raw_score' => $sub->raw_score,
                    'feedback' => $sub->feedback,
                    'submitted_at' => $sub->submitted_at?->format('M d, Y h:i A') ?? '—',
                ];
            })->sort(function ($a, $b) {
                if ($a['score'] === null && $b['score'] === null) {
                    return 0;
                }
                if ($a['score'] === null) {
                    return 1;
                }
                if ($b['score'] === null) {
                    return -1;
                }
                return $b['score'] <=> $a['score'];
            })->values()->all();

            if (empty($studentResults)) {
                continue;
            }

            $scores = array_filter(array_column($studentResults, 'score'), fn ($v) => $v !== null);
            $avgScore = count($scores) > 0 ? round(array_sum($scores) / count($scores), 1) : null;
            $gradedCount = count($scores);
            $topScore = count($scores) > 0 ? max($scores) : null;
            $lowScore = count($scores) > 0 ? min($scores) : null;

            foreach ($scores as $s) {
                $allAssessmentScores[] = $s;
            }
            $totalAssessmentSubs += count($studentResults);
            $totalAssessmentGraded += $gradedCount;

            $assessmentList[] = [
                'key' => 'assessment_' . $assessment->id,
                'type' => 'Assessment',
                'id' => $assessment->id,
                'title' => $assessment->name,
                'course' => $assessment->course?->title ?? '—',
                'course_code' => $assessment->course?->code ?? '',
                'due_date' => $assessment->due_date?->format('M d, Y') ?? '—',
                'target_level' => $assessment->target_level,
                'results_count' => count($studentResults),
                'graded_count' => $gradedCount,
                'avg_score' => $avgScore,
                'top_score' => $topScore,
                'low_score' => $lowScore,
                'latest_taken_at' => $latestTakenAt,
                'results' => $studentResults, // Ordered in DESC order
            ];
        }

        // Sort assessments in FILO order (most recently submitted at top)
        usort($assessmentList, fn ($a, $b) => $b['latest_taken_at'] <=> $a['latest_taken_at']);

        return [
            'quizzes' => $quizList,
            'assignments' => $assignmentList,
            'assessments' => $assessmentList,
            'totals' => [
                'quizzes' => count($quizList),
                'assignments' => count($assignmentList),
                'assessments' => count($assessmentList),
                'total' => count($quizList) + count($assignmentList) + count($assessmentList),
            ],
            'category_stats' => [
                'quizzes' => [
                    'count' => count($quizList),
                    'attempts' => $totalQuizAttempts,
                    'passed' => $totalQuizPassed,
                    'avg_score' => count($allQuizScores) > 0 ? round(array_sum($allQuizScores) / count($allQuizScores), 1) : null,
                ],
                'assignments' => [
                    'count' => count($assignmentList),
                    'submissions' => $totalAssignmentSubs,
                    'graded' => $totalAssignmentGraded,
                    'avg_score' => count($allAssignmentGrades) > 0 ? round(array_sum($allAssignmentGrades) / count($allAssignmentGrades), 1) : null,
                ],
                'assessments' => [
                    'count' => count($assessmentList),
                    'submissions' => $totalAssessmentSubs,
                    'graded' => $totalAssessmentGraded,
                    'avg_score' => count($allAssessmentScores) > 0 ? round(array_sum($allAssessmentScores) / count($allAssessmentScores), 1) : null,
                ],
            ],
        ];
    }

    /**
     * Get the consolidated students dataset filtered and sorted.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getStudentsData(): array
    {
        $user = auth()->user();
        if (! $user) {
            return [];
        }

        $scopedCourseIds = static::instructorCourseIds();
        if (empty($scopedCourseIds)) {
            return [];
        }

        // Apply single course filter if selected
        if (! empty($this->courseFilter) && in_array((int) $this->courseFilter, $scopedCourseIds, true)) {
            $effectiveCourseIds = [(int) $this->courseFilter];
        } else {
            $effectiveCourseIds = $scopedCourseIds;
        }

        // Query students enrolled in the effective courses
        $studentsQuery = User::query()
            ->where('role', 'student')
            ->whereHas('enrollments', fn (Builder $q) => $q->whereIn('course_id', $effectiveCourseIds))
            ->with([
                'courses' => fn ($q) => $q->whereIn('courses.id', $effectiveCourseIds),
            ]);

        if (! empty($this->trackFilter)) {
            $studentsQuery->where('track', $this->trackFilter);
        }

        if (! empty($this->search)) {
            $term = '%' . trim($this->search) . '%';
            $studentsQuery->where(function (Builder $q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term);
            });
        }

        $students = $studentsQuery->get();
        if ($students->isEmpty()) {
            return [];
        }

        $studentIds = $students->pluck('id')->all();

        // 1. Fetch Quizzes in scope & Attempts
        $quizzes = Quiz::query()
            ->whereIn('course_id', $effectiveCourseIds)
            ->where('is_active', true)
            ->with('course:id,title,code')
            ->get();

        $quizAttempts = QuizAttempt::query()
            ->whereIn('user_id', $studentIds)
            ->whereIn('quiz_id', $quizzes->pluck('id'))
            ->whereNotNull('completed_at')
            ->with('quiz:id,title,course_id,pass_percentage')
            ->orderBy('percentage', 'desc')
            ->get()
            ->groupBy('user_id');

        // 2. Fetch Assignments in scope & Submissions
        $assignments = Assignment::query()
            ->whereIn('course_id', $effectiveCourseIds)
            ->released()
            ->with('course:id,title,code')
            ->get();

        $assignmentSubmissions = AssignmentSubmission::query()
            ->whereIn('user_id', $studentIds)
            ->whereIn('assignment_id', $assignments->pluck('id'))
            ->with('assignment:id,name,course_id,due_date')
            ->orderByRaw('grade IS NULL, grade DESC, submitted_at DESC')
            ->get()
            ->groupBy('user_id');

        // 3. Fetch Assessments in scope & Submissions
        $assessments = Assessment::query()
            ->whereIn('course_id', $effectiveCourseIds)
            ->released()
            ->with('course:id,title,code')
            ->get();

        $assessmentSubmissions = AssessmentSubmission::query()
            ->whereIn('user_id', $studentIds)
            ->whereIn('assessment_id', $assessments->pluck('id'))
            ->with('assessment:id,name,course_id,due_date,score')
            ->orderByRaw('score IS NULL, score DESC, submitted_at DESC')
            ->get()
            ->groupBy('user_id');

        // 4. Fetch Enrollments and Certificates for completion & certificate status
        $enrollments = Enrollment::query()
            ->whereIn('user_id', $studentIds)
            ->whereIn('course_id', $effectiveCourseIds)
            ->get()
            ->groupBy('user_id');

        $certificates = Certificate::query()
            ->whereIn('user_id', $studentIds)
            ->whereIn('course_id', $effectiveCourseIds)
            ->get()
            ->groupBy('user_id');

        $rows = [];

        foreach ($students as $student) {
            $studentEnrolledCourseIds = $student->courses->pluck('id')->all();

            // --- QUIZZES ---
            $studentQuizzes = $quizzes->whereIn('course_id', $studentEnrolledCourseIds);
            $totalQuizzes = $studentQuizzes->count();
            $userAttempts = $quizAttempts->get($student->id, collect());

            // Group attempts by quiz
            $attemptsByQuiz = $userAttempts->groupBy('quiz_id');
            $quizzesAttemptedCount = $attemptsByQuiz->count();
            $quizPercentages = [];
            $quizDetails = [];

            foreach ($userAttempts as $attempt) {
                if ($attempt->percentage !== null) {
                    $quizPercentages[] = $attempt->percentage;
                }
                $quizCourse = $studentQuizzes->firstWhere('id', $attempt->quiz_id)?->course;

                $quizDetails[] = [
                    'id' => $attempt->id,
                    'quiz_id' => $attempt->quiz_id,
                    'student_id' => $student->id,
                    'title' => $attempt->quiz?->title ?? 'Quiz #' . $attempt->quiz_id,
                    'course' => $quizCourse?->title ?? '—',
                    'course_code' => $quizCourse?->code ?? '',
                    'score' => $attempt->score,
                    'total_points' => $attempt->total_points,
                    'percentage' => $attempt->percentage,
                    'passed' => (bool) $attempt->passed,
                    'is_retake' => (bool) $attempt->is_retake,
                    'retake_allowed' => (bool) $attempt->retake_allowed,
                    'raw_score' => $attempt->raw_score,
                    'pass_percentage' => $attempt->quiz?->pass_percentage ?? 50,
                    'date' => $attempt->completed_at?->format('M d, Y h:i A') ?? '—',
                ];
            }

            // Sort individual student quiz results in DESC order
            usort($quizDetails, fn ($a, $b) => ($b['percentage'] ?? -1) <=> ($a['percentage'] ?? -1));

            // Deduplicate passed quizzes count by quiz_id
            $distinctQuizzesPassed = $attemptsByQuiz->filter(fn ($group) => $group->contains('passed', true))->count();
            $avgQuizScore = count($quizPercentages) > 0 ? round(array_sum($quizPercentages) / count($quizPercentages), 1) : null;

            // --- ASSIGNMENTS ---
            $studentAssignments = $assignments->whereIn('course_id', $studentEnrolledCourseIds)->filter(function ($a) use ($student) {
                if ($a->target_user_id && (int) $a->target_user_id !== (int) $student->id) {
                    return false;
                }
                if ($a->target_level && strtolower((string) $a->target_level) !== strtolower((string) $student->track)) {
                    return false;
                }
                return true;
            });
            $totalAssignments = $studentAssignments->count();
            $userAssignments = $assignmentSubmissions->get($student->id, collect());
            $assignmentsSubmittedCount = $userAssignments->count();
            $assignmentGrades = [];
            $assignmentDetails = [];

            foreach ($userAssignments as $sub) {
                if ($sub->grade !== null) {
                    $assignmentGrades[] = (float) $sub->grade;
                }
                $assignCourse = $studentAssignments->firstWhere('id', $sub->assignment_id)?->course;

                $assignmentDetails[] = [
                    'id' => $sub->id,
                    'student_id' => $student->id,
                    'assignment_id' => $sub->assignment_id,
                    'title' => $sub->assignment?->name ?? 'Assignment #' . $sub->assignment_id,
                    'course' => $assignCourse?->title ?? '—',
                    'course_code' => $assignCourse?->code ?? '',
                    'status' => $sub->status ?: 'Submitted',
                    'grade' => $sub->grade !== null ? (float) $sub->grade : null,
                    'is_retake' => (bool) $sub->is_retake,
                    'retake_allowed' => (bool) $sub->retake_allowed,
                    'raw_grade' => $sub->raw_grade,
                    'due_date' => $sub->assignment?->due_date?->format('M d, Y') ?? '—',
                    'submitted_at' => $sub->submitted_at?->format('M d, Y h:i A') ?? '—',
                    'feedback' => $sub->feedback,
                    'file_path' => $sub->file_path,
                    'link' => $sub->link,
                ];
            }

            // Sort individual student assignment results in DESC order
            usort($assignmentDetails, fn ($a, $b) => ($b['grade'] ?? -1) <=> ($a['grade'] ?? -1));

            $avgAssignmentGrade = count($assignmentGrades) > 0 ? round(array_sum($assignmentGrades) / count($assignmentGrades), 1) : null;

            // --- ASSESSMENTS ---
            $studentAssessments = $assessments->whereIn('course_id', $studentEnrolledCourseIds)->filter(function ($a) use ($student) {
                if ($a->user_id && (int) $a->user_id !== (int) $student->id) {
                    return false;
                }
                if ($a->target_level && strtolower((string) $a->target_level) !== strtolower((string) $student->track)) {
                    return false;
                }
                return true;
            });
            $totalAssessments = $studentAssessments->count();
            $userAssessments = $assessmentSubmissions->get($student->id, collect());
            $assessmentsSubmittedCount = $userAssessments->count();
            $assessmentScores = [];
            $assessmentDetails = [];

            foreach ($userAssessments as $sub) {
                if ($sub->score !== null) {
                    $assessmentScores[] = (float) $sub->score;
                }
                $assessCourse = $studentAssessments->firstWhere('id', $sub->assessment_id)?->course;

                $assessmentDetails[] = [
                    'id' => $sub->id,
                    'student_id' => $student->id,
                    'assessment_id' => $sub->assessment_id,
                    'title' => $sub->assessment?->name ?? 'Assessment #' . $sub->assessment_id,
                    'course' => $assessCourse?->title ?? '—',
                    'course_code' => $assessCourse?->code ?? '',
                    'status' => $sub->status ?: 'Submitted',
                    'score' => $sub->score !== null ? (float) $sub->score : null,
                    'is_retake' => (bool) $sub->is_retake,
                    'retake_allowed' => (bool) $sub->retake_allowed,
                    'raw_score' => $sub->raw_score,
                    'due_date' => $sub->assessment?->due_date?->format('M d, Y') ?? '—',
                    'submitted_at' => $sub->submitted_at?->format('M d, Y h:i A') ?? '—',
                    'feedback' => $sub->feedback,
                    'file_path' => $sub->file_path,
                    'link' => $sub->link,
                ];
            }

            // Sort individual student assessment results in DESC order
            usort($assessmentDetails, fn ($a, $b) => ($b['score'] ?? -1) <=> ($a['score'] ?? -1));

            $avgAssessmentScore = count($assessmentScores) > 0 ? round(array_sum($assessmentScores) / count($assessmentScores), 1) : null;

            // --- CONSOLIDATED OVERALL SCORE ---
            $allScores = array_merge($quizPercentages, $assignmentGrades, $assessmentScores);
            $overallScore = count($allScores) > 0 ? round(array_sum($allScores) / count($allScores), 1) : null;

            // Items total & completion
            $itemsTotal = $totalQuizzes + $totalAssignments + $totalAssessments;
            $itemsDone = $distinctQuizzesPassed + $assignmentsSubmittedCount + $assessmentsSubmittedCount;
            $completionRate = $itemsTotal > 0 ? (int) round(($itemsDone / $itemsTotal) * 100) : 100;

            // Performance Tier
            if ($overallScore === null) {
                $tier = 'Ungraded';
                $tierKey = 'ungraded';
                $tierColor = 'gray';
            } elseif ($overallScore >= 80) {
                $tier = 'Distinction (80%+)';
                $tierKey = 'distinction';
                $tierColor = 'success';
            } elseif ($overallScore >= 65) {
                $tier = 'Merit (65-79%)';
                $tierKey = 'merit';
                $tierColor = 'info';
            } elseif ($overallScore >= 50) {
                $tier = 'Pass (50-64%)';
                $tierKey = 'pass';
                $tierColor = 'warning';
            } else {
                $tier = 'Needs Attention (<50%)';
                $tierKey = 'at_risk';
                $tierColor = 'danger';
            }

            // Filter by performance tier if requested
            if (! empty($this->performanceFilter)) {
                if ($this->performanceFilter === 'distinction' && $tierKey !== 'distinction') {
                    continue;
                }
                if ($this->performanceFilter === 'merit' && $tierKey !== 'merit') {
                    continue;
                }
                if ($this->performanceFilter === 'pass' && $tierKey !== 'pass') {
                    continue;
                }
                if ($this->performanceFilter === 'at_risk' && $tierKey !== 'at_risk') {
                    continue;
                }
                if ($this->performanceFilter === 'incomplete' && $completionRate >= 100) {
                    continue;
                }
            }

            $userEnrollments = $enrollments->get($student->id, collect());
            $userCertificates = $certificates->get($student->id, collect());

            $coursesData = $student->courses->map(function ($c) use ($userEnrollments, $userCertificates) {
                $enr = $userEnrollments->firstWhere('course_id', $c->id);
                $cert = $userCertificates->firstWhere('course_id', $c->id);
                $isCompleted = (bool) ($enr && $enr->completed_at !== null);

                return [
                    'id' => $c->id,
                    'title' => $c->title,
                    'code' => $c->code,
                    'is_completed' => $isCompleted,
                    'completed_at' => $enr?->completed_at?->format('M d, Y'),
                    'certificate_issued' => $isCompleted && $cert !== null,
                    'certificate_id' => $isCompleted ? $cert?->id : null,
                ];
            })->all();

            $rows[] = [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'track' => $student->track ?: 'Beginner',
                'courses' => $coursesData,
                'courses_count' => count($coursesData),
                'all_courses_completed' => count($coursesData) > 0 && ! collect($coursesData)->contains('is_completed', false),

                // Quizzes
                'total_quizzes' => $totalQuizzes,
                'quizzes_attempted' => $quizzesAttemptedCount,
                'quizzes_passed' => $distinctQuizzesPassed,
                'avg_quiz_score' => $avgQuizScore,
                'quiz_details' => $quizDetails,

                // Assignments
                'total_assignments' => $totalAssignments,
                'assignments_submitted' => $assignmentsSubmittedCount,
                'assignments_graded' => count($assignmentGrades),
                'avg_assignment_grade' => $avgAssignmentGrade,
                'assignment_details' => $assignmentDetails,

                // Assessments
                'total_assessments' => $totalAssessments,
                'assessments_submitted' => $assessmentsSubmittedCount,
                'assessments_graded' => count($assessmentScores),
                'avg_assessment_score' => $avgAssessmentScore,
                'assessment_details' => $assessmentDetails,

                // Consolidated
                'overall_score' => $overallScore,
                'items_total' => $itemsTotal,
                'items_done' => $itemsDone,
                'completion_rate' => min(100, $completionRate),
                'tier' => $tier,
                'tier_key' => $tierKey,
                'tier_color' => $tierColor,
            ];
        }

        // Sorting
        usort($rows, function ($a, $b) {
            switch ($this->sortBy) {
                case 'score_desc':
                case 'overall_desc':
                    return ($b['overall_score'] ?? -1) <=> ($a['overall_score'] ?? -1);
                case 'overall_asc':
                    return ($a['overall_score'] ?? 999) <=> ($b['overall_score'] ?? 999);
                case 'quiz_desc':
                    return ($b['avg_quiz_score'] ?? -1) <=> ($a['avg_quiz_score'] ?? -1);
                case 'assignment_desc':
                    return ($b['avg_assignment_grade'] ?? -1) <=> ($a['avg_assignment_grade'] ?? -1);
                case 'assessment_desc':
                    return ($b['avg_assessment_score'] ?? -1) <=> ($a['avg_assessment_score'] ?? -1);
                case 'completion_desc':
                    return $b['completion_rate'] <=> $a['completion_rate'];
                case 'name_desc':
                    return strcasecmp($b['name'], $a['name']);
                case 'name_asc':
                default:
                    return strcasecmp($a['name'], $b['name']);
            }
        });

        return $rows;
    }

    /**
     * Compute KPI stats across the current dataset.
     *
     * @param array<int, array<string, mixed>> $students
     * @return array<string, mixed>
     */
    public function getKpiStats(array $students): array
    {
        $totalStudents = count($students);

        $overallScores = array_filter(array_column($students, 'overall_score'), fn ($v) => $v !== null);
        $avgOverall = count($overallScores) > 0 ? round(array_sum($overallScores) / count($overallScores), 1) : null;

        $quizScores = array_filter(array_column($students, 'avg_quiz_score'), fn ($v) => $v !== null);
        $avgQuiz = count($quizScores) > 0 ? round(array_sum($quizScores) / count($quizScores), 1) : null;

        $assignmentGrades = array_filter(array_column($students, 'avg_assignment_grade'), fn ($v) => $v !== null);
        $avgAssignment = count($assignmentGrades) > 0 ? round(array_sum($assignmentGrades) / count($assignmentGrades), 1) : null;

        $assessmentScores = array_filter(array_column($students, 'avg_assessment_score'), fn ($v) => $v !== null);
        $avgAssessment = count($assessmentScores) > 0 ? round(array_sum($assessmentScores) / count($assessmentScores), 1) : null;

        $distinctionCount = count(array_filter($students, fn ($s) => ($s['tier_key'] ?? '') === 'distinction'));
        $atRiskCount = count(array_filter($students, fn ($s) => ($s['tier_key'] ?? '') === 'at_risk'));

        return [
            'total_students' => $totalStudents,
            'avg_overall' => $avgOverall,
            'avg_quiz' => $avgQuiz,
            'avg_assignment' => $avgAssignment,
            'avg_assessment' => $avgAssessment,
            'distinction_count' => $distinctionCount,
            'at_risk_count' => $atRiskCount,
        ];
    }

    /**
     * Stream CSV download of consolidated student results.
     */
    public function exportCsv(): StreamedResponse
    {
        $data = $this->getStudentsData();
        $tasksData = $this->getTasksData();
        $filename = 'student-results-report-' . date('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($data, $tasksData) {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            // UTF-8 BOM for Excel compatibility
            fputs($handle, "\xEF\xBB\xBF");

            // Section 1: Consolidated Per-Student Results
            fputcsv($handle, ['=== CONSOLIDATED STUDENT RESULTS ===']);
            fputcsv($handle, [
                'Student Name',
                'Email Address',
                'Track / Level',
                'Enrolled Courses',
                'Quizzes Passed',
                'Quizzes Total',
                'Quiz Avg (%)',
                'Assignments Submitted',
                'Assignments Total',
                'Assignment Avg Grade (%)',
                'Assessments Submitted',
                'Assessments Total',
                'Assessment Avg Score (%)',
                'Consolidated Score (%)',
                'Completion Rate (%)',
                'Performance Tier',
            ]);

            foreach ($data as $row) {
                $courseTitles = implode('; ', array_map(fn ($c) => $c['title'] . ($c['code'] ? ' (' . $c['code'] . ')' : ''), $row['courses']));

                fputcsv($handle, [
                    $row['name'],
                    $row['email'],
                    $row['track'],
                    $courseTitles,
                    $row['quizzes_passed'],
                    $row['total_quizzes'],
                    $row['avg_quiz_score'] !== null ? $row['avg_quiz_score'] . '%' : 'N/A',
                    $row['assignments_submitted'],
                    $row['total_assignments'],
                    $row['avg_assignment_grade'] !== null ? $row['avg_assignment_grade'] . '%' : 'N/A',
                    $row['assessments_submitted'],
                    $row['total_assessments'],
                    $row['avg_assessment_score'] !== null ? $row['avg_assessment_score'] . '%' : 'N/A',
                    $row['overall_score'] !== null ? $row['overall_score'] . '%' : 'N/A',
                    $row['completion_rate'] . '%',
                    $row['tier'],
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['=== INDIVIDUAL TASK RESULTS (FILO TASK ORDER • DESCENDING STUDENT SCORE ORDER) ===']);
            fputcsv($handle, ['Task Type', 'Task Title', 'Course', 'Student Name', 'Student Email', 'Score / Grade (%)', 'Status', 'Date']);

            // Quizzes in FILO task order and DESC student score order
            foreach ($tasksData['quizzes'] as $q) {
                foreach ($q['results'] as $res) {
                    fputcsv($handle, ['Quiz', $q['title'], $q['course'], $res['student_name'], $res['student_email'], $res['percentage'] !== null ? $res['percentage'].'%' : 'N/A', $res['passed'] ? 'Passed' : 'Failed', $res['completed_at']]);
                }
            }

            // Assignments in FILO task order and DESC student score order
            foreach ($tasksData['assignments'] as $a) {
                foreach ($a['results'] as $res) {
                    fputcsv($handle, ['Assignment', $a['title'], $a['course'], $res['student_name'], $res['student_email'], $res['grade'] !== null ? $res['grade'].'%' : 'N/A', $res['status'], $res['submitted_at']]);
                }
            }

            // Assessments in FILO task order and DESC student score order
            foreach ($tasksData['assessments'] as $as) {
                foreach ($as['results'] as $res) {
                    fputcsv($handle, ['Assessment', $as['title'], $as['course'], $res['student_name'], $res['student_email'], $res['score'] !== null ? $res['score'].'%' : 'N/A', $res['status'], $res['submitted_at']]);
                }
            }

            fclose($handle);
        }, 200, $headers);
    }
}
