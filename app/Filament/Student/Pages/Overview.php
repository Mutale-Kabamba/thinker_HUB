<?php

namespace App\Filament\Student\Pages;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Models\CourseSession;
use App\Models\LearningMaterial;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

class Overview extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'Overview';

    protected static ?string $title = 'Student Dashboard';

    protected static ?int $navigationSort = -10;

    protected string $view = 'filament.student.pages.overview';

    public array $stats = [];

    public array $materials = [];

    public array $quickLinks = [];

    public array $calendar = [];

    public array $calendarEvents = [];

    public array $upcoming = [];

    public array $assignmentSummary = [];

    public array $assessmentSummary = [];

    public array $payments = [];

    public array $enrolledCourses = [];

    public ?array $activeCourse = null;

    public array $rankingList = [];

    public array $heroBanners = [];

    public int $xp = 0;

    public int $coins = 0;

    public array $todaySchedule = [];

    public ?string $selectedDate = null;

    public ?int $selectedSessionId = null;

    public ?array $selectedSessionDetails = null;

    public bool $showSessionDetailsModal = false;

    public function mount(): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        try {
            app(\App\Services\GamificationService::class)->recordDailyLogin($user);
        } catch (\Throwable $e) {
            report($e);
        }

        $today = Carbon::today();

        $visibleAssignments = Assignment::query()
            ->visibleTo($user)
            ->released()
            ->get();

        $submittedCount = AssessmentSubmission::query()->where('user_id', $user->id)->count();
        $assignmentCount = max($visibleAssignments->count(), 1);
        $completion = (int) round(($submittedCount / $assignmentCount) * 100);

        $assignmentSubmissions = AssignmentSubmission::query()
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('assignment_id');

        $assessmentRecords = Assessment::query()
            ->with('course')
            ->visibleTo($user)
            ->released()
            ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_date')
            ->latest('id')
            ->get();

        $assessmentSubmissions = AssessmentSubmission::query()
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('assessment_id');

        $visibleMaterials = LearningMaterial::query()
            ->visibleTo($user)
            ->latest()
            ->get();

        $nextDueItem = $visibleAssignments
            ->filter(fn (Assignment $item): bool => (bool) $item->due_date && $item->due_date->greaterThanOrEqualTo($today))
            ->sortBy('due_date')
            ->first();

        $overdueCount = $visibleAssignments
            ->filter(fn (Assignment $item): bool => (bool) $item->due_date && $item->due_date->lt($today))
            ->count();

        $this->stats = [
            'greeting' => 'Hello '.$user->name,
            'course' => $user->courses()->orderBy('courses.title')->value('courses.title') ?: 'No course selected',
            'track' => $user->track,
            'submissions' => $submittedCount,
            'assignments' => $visibleAssignments->count(),
            'materials' => $visibleMaterials->count(),
            'next_due' => $nextDueItem?->due_date?->format('Y-m-d') ?: 'No due dates',
            'overdue' => $overdueCount,
            'completion' => max(0, min(100, $completion)),
        ];

        $this->quickLinks = [
            ['label' => 'Overview', 'section' => 'overview'],
            ['label' => 'Assignments', 'section' => 'assignments'],
            ['label' => 'Assessments', 'section' => 'assessments'],
            ['label' => 'Materials', 'section' => 'materials'],
            ['label' => 'Payments & Receipts', 'section' => 'payments'],
        ];

        $this->materials = $visibleMaterials
            ->take(8)
            ->map(fn (LearningMaterial $item): array => [
                'name' => $item->file_name ?: $item->title,
                'type' => $item->material_type,
                'course' => $item->course?->title ?? 'Unassigned course',
            ])
            ->values()
            ->all();

        $this->upcoming = $visibleAssignments
            ->filter(fn (Assignment $item): bool => (bool) $item->due_date && $item->due_date->greaterThanOrEqualTo($today))
            ->sortBy('due_date')
            ->take(3)
            ->map(fn (Assignment $item): array => [
                'name' => $item->name,
                'due' => $item->due_date?->format('Y-m-d') ?: '-',
                'status' => $assignmentSubmissions->get($item->id)?->status ?? 'Not submitted',
            ])
            ->values()
            ->all();

        $submittedAssignmentsCount = $assignmentSubmissions->count();
        $pendingAssignmentsCount = max($visibleAssignments->count() - $submittedAssignmentsCount, 0);

        $this->assignmentSummary = [
            'total' => $visibleAssignments->count(),
            'submitted' => $submittedAssignmentsCount,
            'pending' => $pendingAssignmentsCount,
            'next_due' => $nextDueItem?->due_date?->format('Y-m-d') ?: 'No due dates',
        ];

        $scoredAssessments = $assessmentSubmissions
            ->pluck('score')
            ->filter(fn ($value): bool => is_numeric($value));

        $averageScore = $scoredAssessments->isNotEmpty()
            ? round($scoredAssessments->avg(), 1)
            : null;

        $this->assessmentSummary = [
            'total' => $assessmentRecords->count(),
            'submitted' => $assessmentSubmissions->count(),
            'average_score' => $averageScore,
            'items' => $assessmentRecords
                ->take(4)
                ->map(fn (Assessment $item): array => [
                        'name' => $item->name ?: 'Assessment',
                    'course' => $item->course?->title ?? 'Unassigned course',
                        'due_date' => $item->due_date?->format('Y-m-d') ?? '-',
                    'score' => $assessmentSubmissions->get($item->id)?->score ?? '-',
                    'submission_status' => $assessmentSubmissions->get($item->id)?->status ?? 'Not submitted',
                ])
                ->values()
                ->all(),
        ];

        $this->payments = $user->payments()
            ->with('course')
            ->latest()
            ->get()
            ->map(fn (\App\Models\Payment $p): array => [
                'reference' => $p->reference,
                'course' => $p->course?->title ?? 'Course Tuition Fee',
                'amount' => $p->formattedAmount(),
                'method' => str_replace('_', ' ', $p->payment_method) . ' (' . strtoupper($p->provider ?? 'Gateway') . ')',
                'status' => $p->status,
                'paid_at' => $p->paid_at?->format('M j, Y') ?? $p->created_at->format('M j, Y'),
                'receipt_url' => route('payment.receipt', $p->reference),
            ])
            ->toArray();

        $this->xp = (int) ($user->lifetime_xp ?? 0);
        $this->coins = (int) ($user->thinker_coins ?? 0);
        try {
            $rankData = app(\App\Services\GamificationService::class)->calculateUserRank($this->xp);
            $this->rankName = $rankData['rank_name'] ?? 'Novice';
        } catch (\Throwable $e) {
            $this->rankName = 'Novice';
        }

        // Leaderboard Ranking List Calculation
        $ordinal = function (int $n): string {
            $ends = ['th','st','nd','rd','th','th','th','th','th','th'];
            if ((($n % 100) >= 11) && (($n % 100) <= 13)) {
                return $n.'th';
            }
            return $n.$ends[$n % 10];
        };

        try {
            $leaderboard = app(\App\Services\GamificationService::class)->leaderboard();
            $userRow = $leaderboard->firstWhere('user_id', $user->id);
            $userRankIndex = $userRow ? (int) $userRow['rank'] : 1;

            $rankingItems = [];
            // Logged-in student ranking
            $rankingItems[] = [
                'name' => $user->name,
                'is_current_user' => true,
                'rank' => $ordinal($userRankIndex),
                'avatar' => $user->profile_photo_url ?? null,
                'initials' => strtoupper(substr($user->name, 0, 2)),
                'xp' => (int) ($user->lifetime_xp ?? 0),
            ];

            // Top students
            foreach ($leaderboard->take(3) as $topUser) {
                if ($topUser['user_id'] !== $user->id && count($rankingItems) < 3) {
                    $rankingItems[] = [
                        'name' => $topUser['name'],
                        'is_current_user' => false,
                        'rank' => $ordinal($topUser['rank']),
                        'avatar' => null,
                        'initials' => strtoupper(substr($topUser['name'], 0, 2)),
                        'xp' => $topUser['xp'],
                    ];
                }
            }

            $this->rankingList = $rankingItems;
        } catch (\Throwable $e) {
            $this->rankingList = [
                [
                    'name' => $user->name,
                    'is_current_user' => true,
                    'rank' => '1st',
                    'avatar' => $user->profile_photo_url ?? null,
                    'initials' => strtoupper(substr($user->name, 0, 2)),
                    'xp' => (int) ($user->lifetime_xp ?? 0),
                ],
            ];
        }

        // 3 Real Status Metrics: Learning Materials, Assignments, Assessments
        $submittedAssignmentsCount = $assignmentSubmissions->count();

        $totalLessons = $visibleMaterials->count();
        $visibleMaterialIds = $visibleMaterials->pluck('id')->all();
        $completedLessons = min(
            $totalLessons,
            $user->xpTransactions()
                ->where(function ($q) use ($visibleMaterialIds) {
                    $q->whereIn('activity_type', ['material_view', 'material_viewed', 'material_read'])
                        ->orWhereIn('source', ['material_view', 'material_viewed', 'material_read'])
                        ->orWhere(fn ($sub) => $sub->where('subject_type', LearningMaterial::class)->whereIn('subject_id', $visibleMaterialIds));
                })
                ->whereIn('source_id', $visibleMaterialIds)
                ->distinct('source_id')
                ->count('source_id')
        );
        $lessonsPercent = $totalLessons > 0 ? (int) round(($completedLessons / $totalLessons) * 100) : 0;

        $totalAssignmentsCount = $visibleAssignments->count();
        $assignmentsPercent = $totalAssignmentsCount > 0 ? (int) round(($submittedAssignmentsCount / $totalAssignmentsCount) * 100) : 0;

        $totalTestsCount = $assessmentRecords->count();
        $completedTestsCount = $assessmentSubmissions->count();
        $testsPercent = $totalTestsCount > 0 ? (int) round(($completedTestsCount / $totalTestsCount) * 100) : 0;

        // Load enriched enrolled courses for table and hero banner
        $coursesQuery = $user->courses()
            ->with(['instructors:id,name,profile_photo_path', 'students:id,name,profile_photo_path', 'sessions', 'assignments', 'materials', 'assessments'])
            ->where('courses.is_active', true)
            ->get();

        $enrolledCourseIds = $coursesQuery->pluck('id')->all();
        if (empty($enrolledCourseIds)) {
            $enrolledCourseIds = $user->courses()->pluck('courses.id')->all();
        }

        $rawQuizzes = Quiz::query()
            ->visibleTo($user)
            ->where(function ($query) {
                $query->where('is_active', true)
                    ->orWhereNotNull('publish_at');
            })
            ->get();

        $quizAttempts = QuizAttempt::query()
            ->where('user_id', $user->id)
            ->whereIn('quiz_id', $rawQuizzes->pluck('id'))
            ->get();

        $totalQuizzesCount = $rawQuizzes->count();
        $completedAttempts = $quizAttempts->filter(fn (QuizAttempt $a) => $a->completed_at !== null);
        $quizzesDoneCount = $completedAttempts->pluck('quiz_id')->unique()->count();
        $quizzesPassedCount = $completedAttempts->filter(fn (QuizAttempt $a) => (bool) $a->passed)->pluck('quiz_id')->unique()->count();
        $quizzesPercent = $totalQuizzesCount > 0 ? (int) round(($quizzesDoneCount / $totalQuizzesCount) * 100) : 0;

        $this->stats = [
            'greeting' => 'Hello '.$user->name,
            'course' => $user->courses()->orderBy('courses.title')->value('courses.title') ?: 'No course selected',
            'track' => $user->track,
            'submissions' => $submittedCount,
            'assignments' => $visibleAssignments->count(),
            'materials' => $visibleMaterials->count(),
            'next_due' => $nextDueItem?->due_date?->format('Y-m-d') ?: 'No due dates',
            'overdue' => $overdueCount,
            'completion' => max(0, min(100, $completion)),
            'lessons_total' => $totalLessons,
            'lessons_completed' => $completedLessons,
            'lessons_percent' => $lessonsPercent,
            'assignments_total' => $totalAssignmentsCount,
            'assignments_completed' => $submittedAssignmentsCount,
            'assignments_percent' => $assignmentsPercent,
            'tests_total' => $totalTestsCount,
            'tests_completed' => $completedTestsCount,
            'tests_percent' => $testsPercent,
            'quizzes_total' => $totalQuizzesCount,
            'quizzes_completed' => $quizzesDoneCount,
            'quizzes_passed' => $quizzesPassedCount,
            'quizzes_percent' => $quizzesPercent,
        ];

        $gradientPalette = [
            ['gradient' => 'linear-gradient(135deg, #7C3AED, #4F46E5)', 'bar' => '#7C3AED', 'bg' => 'bg-purple-500'],
            ['gradient' => 'linear-gradient(135deg, #EC4899, #F43F5E)', 'bar' => '#EC4899', 'bg' => 'bg-pink-500'],
            ['gradient' => 'linear-gradient(135deg, #F97316, #EA580C)', 'bar' => '#F97316', 'bg' => 'bg-orange-500'],
            ['gradient' => 'linear-gradient(135deg, #0D9488, #059669)', 'bar' => '#0D9488', 'bg' => 'bg-teal-500'],
            ['gradient' => 'linear-gradient(135deg, #0284C7, #2563EB)', 'bar' => '#0284C7', 'bg' => 'bg-sky-500'],
        ];

        $this->enrolledCourses = $coursesQuery->map(function ($c, $idx) use ($user, $gradientPalette) {
            $sessProgress = $user->courseSessionsProgress($c);
            $isCompleted = $sessProgress['is_completed'];
            $courseProgress = $sessProgress['percent'];
            
            $totalSessions = $sessProgress['total'];
            $completedSessions = $sessProgress['completed'];

            $palette = $gradientPalette[$idx % count($gradientPalette)];

            return [
                'id' => $c->id,
                'title' => $c->title,
                'code' => $c->code ?: 'CS-101',
                'category' => $c->category ?: 'Development',
                'duration' => $c->duration ?: '6 Weeks',
                'progress' => $courseProgress,
                'sessions_total' => $totalSessions,
                'sessions_completed' => $completedSessions,
                'modules_count' => $totalSessions,
                'completed_count' => $completedSessions,
                'lessons_count' => $c->materials->count(),
                'assignments_count' => $c->assignments->count(),
                'tests_count' => $c->assessments->count(),
                'status' => $isCompleted ? 'Completed' : ($courseProgress > 0 ? 'In progress' : 'Pending'),
                'is_completed' => $isCompleted,
                'students' => $c->students->take(4)->values()->all(),
                'students_count' => $c->students->count(),
                'instructor' => $c->instructors->first()?->name ?? 'Instructor',
                'gradient' => $palette['gradient'],
                'bar_color' => $palette['bar'],
                'url' => route('filament.student.pages.courses'),
            ];
        })->values()->all();

        $this->activeCourse = $this->enrolledCourses[0] ?? null;

        // 5 Dynamic Carousel Banners for Student Overview
        $userPhoto = $user->profile_photo_url;
        $userInitials = strtoupper(substr($user->name, 0, 2));

        $mainCourseTitle = $this->activeCourse['title'] ?? ($user->courses()->orderBy('courses.title')->value('courses.title') ?? null);
        $mainCourseProgress = $this->activeCourse['progress'] ?? 0;
        $mainCourseSessionsDone = $this->activeCourse['sessions_completed'] ?? 0;
        $mainCourseSessionsTotal = $this->activeCourse['sessions_total'] ?? 0;
        $mainCourseIsCompleted = ($this->activeCourse['status'] ?? '') === 'Completed';

        $this->heroBanners = [
            // 1. Main Course Progress (Determined by sessions X/X or instructor mark)
            [
                'id' => 'course_progress',
                'badge' => 'Main Course Progress',
                'badge_color' => 'bg-indigo-500/25 text-indigo-100 border-indigo-400/40',
                'title' => $mainCourseTitle ? 'Active Course: ' . $mainCourseTitle : 'Start Your Learning Journey!',
                'description' => $mainCourseTitle 
                    ? ($mainCourseIsCompleted 
                        ? "You have successfully completed this course ({$mainCourseSessionsDone}/{$mainCourseSessionsTotal} sessions). Congratulations!"
                        : ($mainCourseSessionsTotal > 0 
                            ? "You have completed {$mainCourseSessionsDone} of {$mainCourseSessionsTotal} scheduled sessions ({$mainCourseProgress}%). Keep attending live sessions to earn your certificate!" 
                            : "Enrollment is active ({$mainCourseProgress}%). Live class sessions will appear here as scheduled."))
                    : 'Enroll in an active course to unlock structured learning materials, submit assignments, and earn official certifications.',
                'metric_label' => 'Sessions Done',
                'metric_value' => $mainCourseSessionsTotal > 0 ? "{$mainCourseSessionsDone} / {$mainCourseSessionsTotal}" : "{$mainCourseProgress}%",
                'progress_val' => $mainCourseProgress,
                'cta_label' => $mainCourseTitle ? 'View Schedule' : 'Browse Courses',
                'cta_url' => $mainCourseTitle ? route('filament.student.pages.schedule') : route('filament.student.pages.courses'),
                'css_gradient' => 'linear-gradient(135deg, #0f766e 0%, #0e7490 50%, #1d4ed8 100%)',
                'avatar' => $userPhoto,
                'initials' => $userInitials,
            ],
            // 2. Assignments
            [
                'id' => 'assignments',
                'badge' => 'Assignments',
                'badge_color' => 'bg-rose-500/25 text-rose-100 border-rose-400/40',
                'title' => 'Assignments & Submissions',
                'description' => "{$submittedAssignmentsCount} of {$totalAssignmentsCount} assignments submitted ({$assignmentsPercent}%). " . ($overdueCount > 0 ? "You have {$overdueCount} overdue item(s) to review." : 'All upcoming assignments are in good standing!'),
                'metric_label' => 'Submissions',
                'metric_value' => "{$submittedAssignmentsCount} / {$totalAssignmentsCount}",
                'progress_val' => $assignmentsPercent,
                'cta_label' => 'View Assignments',
                'cta_url' => route('filament.student.pages.assignments'),
                'css_gradient' => 'linear-gradient(135deg, #831843 0%, #9d174d 50%, #6d28d9 100%)',
                'avatar' => $userPhoto,
                'initials' => $userInitials,
            ],
            // 3. Assessments
            [
                'id' => 'assessments',
                'badge' => 'Assessments',
                'badge_color' => 'bg-emerald-500/25 text-emerald-100 border-emerald-400/40',
                'title' => 'New Assessments Available Now!',
                'description' => "Welcome to your assessments & exams. {$completedTestsCount} of {$totalTestsCount} completed ({$testsPercent}%). Test your skills, reinforce knowledge, and check results.",
                'metric_label' => 'Completed',
                'metric_value' => "{$completedTestsCount} / {$totalTestsCount}",
                'progress_val' => $testsPercent,
                'cta_label' => 'Explore More',
                'cta_url' => route('filament.student.pages.assessments'),
                'css_gradient' => 'linear-gradient(135deg, #065f46 0%, #047857 50%, #0f766e 100%)',
                'avatar' => $userPhoto,
                'initials' => $userInitials,
            ],
            // 4. Quizzes (Accurate Schedule & Done Metrics)
            [
                'id' => 'quizzes',
                'badge' => 'Interactive Quizzes',
                'badge_color' => 'bg-amber-500/25 text-amber-100 border-amber-400/40',
                'title' => 'Interactive Quizzes',
                'description' => "{$quizzesDoneCount} of {$totalQuizzesCount} scheduled quizzes completed ({$quizzesPercent}%). " . ($quizzesPassedCount > 0 ? "({$quizzesPassedCount} passed). " : "") . "Challenge yourself with topic quizzes to earn instant XP and Thinker Coins.",
                'metric_label' => 'Quizzes Done',
                'metric_value' => "{$quizzesDoneCount} / {$totalQuizzesCount}",
                'progress_val' => $quizzesPercent,
                'cta_label' => 'Take Quizzes',
                'cta_url' => route('filament.student.pages.quizzes'),
                'css_gradient' => 'linear-gradient(135deg, #9a3412 0%, #c2410c 50%, #b45309 100%)',
                'avatar' => $userPhoto,
                'initials' => $userInitials,
            ],
            // 5. Learning Material
            [
                'id' => 'materials',
                'badge' => 'Learning Materials',
                'badge_color' => 'bg-sky-500/25 text-sky-100 border-sky-400/40',
                'title' => 'Learning Materials & Resources',
                'description' => "{$completedLessons} of {$totalLessons} materials studied ({$lessonsPercent}%). Read lecture notes, access video recordings, and study course references.",
                'metric_label' => 'Reviewed',
                'metric_value' => "{$completedLessons} / {$totalLessons}",
                'progress_val' => $lessonsPercent,
                'cta_label' => 'Browse Materials',
                'cta_url' => route('filament.student.pages.materials'),
                'css_gradient' => 'linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 50%, #0284c7 100%)',
                'avatar' => $userPhoto,
                'initials' => $userInitials,
            ],
        ];

        // Collect REAL Upcoming Items (Assignments + Assessments + Live Sessions)
        $upcomingAssignments = $visibleAssignments
            ->filter(fn (Assignment $item): bool => (bool) $item->due_date && $item->due_date->greaterThanOrEqualTo($today))
            ->map(fn (Assignment $item): array => [
                'name' => $item->name,
                'type' => 'Assignments',
                'color' => 'bg-rose-500 text-rose-500',
                'due' => $item->due_date->format('Y-m-d'),
                'day' => $item->due_date->format('d'),
                'month' => $item->due_date->format('M'),
            ]);

        $upcomingAssessments = $assessmentRecords
            ->filter(fn (Assessment $item): bool => (bool) $item->due_date && $item->due_date->greaterThanOrEqualTo($today))
            ->map(fn (Assessment $item): array => [
                'name' => $item->name ?: 'Assessment Test',
                'type' => 'Assessments',
                'color' => 'bg-emerald-500 text-emerald-500',
                'due' => $item->due_date->format('Y-m-d'),
                'day' => $item->due_date->format('d'),
                'month' => $item->due_date->format('M'),
            ]);

        $upcomingSessions = CourseSession::query()
            ->whereIn('course_id', $coursesQuery->pluck('id'))
            ->where('session_date', '>=', $today->toDateString())
            ->orderBy('session_date')
            ->take(4)
            ->get()
            ->map(fn ($s) => [
                'name' => $s->title ?: 'Live Class Session',
                'type' => 'Live Sessions',
                'color' => 'bg-purple-500 text-purple-500',
                'due' => Carbon::parse($s->session_date)->format('Y-m-d'),
                'day' => Carbon::parse($s->session_date)->format('d'),
                'month' => Carbon::parse($s->session_date)->format('M'),
            ]);

        $this->upcoming = $upcomingAssignments
            ->concat($upcomingAssessments)
            ->concat($upcomingSessions)
            ->sortBy('due')
            ->take(4)
            ->values()
            ->all();

        // Today & Upcoming Sessions
        $this->todaySchedule = CourseSession::query()
            ->whereIn('course_id', $coursesQuery->pluck('id'))
            ->where('session_date', '>=', $today->toDateString())
            ->with(['course', 'instructor'])
            ->orderBy('session_date')
            ->orderBy('start_time')
            ->take(4)
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'title' => $s->title,
                'course' => $s->course?->title ?? 'Live Class',
                'date' => Carbon::parse($s->session_date)->format('M j'),
                'time' => Carbon::parse($s->start_time)->format('h:i A'),
                'instructor' => $s->instructor?->name ?? 'Instructor',
                'is_today' => Carbon::parse($s->session_date)->isToday(),
                'type' => $s->type ?? 'group',
            ])
            ->values()
            ->all();

        $this->loadCalendar(Carbon::today(), $visibleAssignments, $assessmentRecords, $assignmentSubmissions, $assessmentSubmissions);
    }

    public function navigateCalendar(int $year, int $month): void
    {
        $date = Carbon::createFromDate($year, $month, 1);
        $this->loadCalendar($date);
    }

    protected function loadCalendar(
        Carbon $reference,
        $visibleAssignments = null,
        $assessmentRecords = null,
        $assignmentSubmissions = null,
        $assessmentSubmissions = null,
    ): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $monthStart = $reference->copy()->startOfMonth();
        $monthEnd = $reference->copy()->endOfMonth();
        $daysInMonth = $monthStart->daysInMonth;
        $today = Carbon::today();

        if ($visibleAssignments === null) {
            $visibleAssignments = Assignment::query()
                ->with('course')
                ->visibleTo($user)
                ->released()
                ->get();
        }

        if ($assessmentRecords === null) {
            $assessmentRecords = Assessment::query()
                ->with('course')
                ->visibleTo($user)
                ->released()
                ->get();
        }

        if ($assignmentSubmissions === null) {
            $assignmentSubmissions = AssignmentSubmission::query()
                ->where('user_id', $user->id)
                ->get()
                ->keyBy('assignment_id');
        }

        if ($assessmentSubmissions === null) {
            $assessmentSubmissions = AssessmentSubmission::query()
                ->where('user_id', $user->id)
                ->get()
                ->keyBy('assessment_id');
        }

        $assignmentDueMap = $visibleAssignments
            ->filter(fn (Assignment $a): bool => (bool) $a->due_date && $a->due_date->between($monthStart, $monthEnd))
            ->groupBy(fn (Assignment $a): string => $a->due_date->format('Y-m-d'));

        $assessmentDueMap = $assessmentRecords
            ->filter(fn (Assessment $a): bool => (bool) $a->due_date && $a->due_date->between($monthStart, $monthEnd))
            ->groupBy(fn (Assessment $a): string => $a->due_date->format('Y-m-d'));

        $courseIds = $user->courses()->pluck('courses.id')->all();
        $sessions = CourseSession::query()
            ->with('course')
            ->where(function ($q) use ($courseIds, $user) {
                $q->whereIn('course_id', $courseIds)
                    ->where(function ($q2) use ($user) {
                        $q2->where('type', 'group')
                            ->orWhere('student_id', $user->id);
                    });
            })
            ->whereIn('status', ['scheduled', 'rescheduled'])
            ->get();

        $sessionDateMap = [];
        foreach ($sessions as $s) {
            $effectiveDate = $s->getEffectiveDate();
            if ($effectiveDate->between($monthStart, $monthEnd)) {
                $key = $effectiveDate->format('Y-m-d');
                $sessionDateMap[$key][] = $s;
            }
        }

        $days = [];
        $events = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $monthStart->copy()->day($day);
            $key = $date->format('Y-m-d');

            $dayAssignments = $assignmentDueMap->get($key, collect());
            $dayAssessments = $assessmentDueMap->get($key, collect());
            $daySessions = $sessionDateMap[$key] ?? [];
            $hasItems = $dayAssignments->isNotEmpty() || $dayAssessments->isNotEmpty() || count($daySessions) > 0;

            $sessionNames = collect($daySessions)->map(fn ($s) => ($s->title ?: $s->course?->title ?? 'Session').' @ '.Carbon::parse($s->getEffectiveStartTime())->format('g:i A'));

            $days[] = [
                'day' => $day,
                'date' => $key,
                'is_today' => $date->isToday(),
                'is_past' => $date->lt($today),
                'has_due' => $hasItems,
                'assignment_count' => $dayAssignments->count(),
                'assessment_count' => $dayAssessments->count(),
                'session_count' => count($daySessions),
                'due_names' => $dayAssignments->pluck('name')->merge($dayAssessments->pluck('name'))->merge($sessionNames)->filter()->values()->all(),
            ];

            if ($hasItems) {
                $items = [];

                foreach ($dayAssignments as $a) {
                    $sub = $assignmentSubmissions->get($a->id);
                    $items[] = [
                        'type' => 'Assignment',
                        'name' => $a->name,
                        'course' => $a->course?->title ?? 'Unassigned',
                        'status' => $sub?->status ?? 'Not submitted',
                        'grade' => $sub?->grade,
                    ];
                }

                foreach ($dayAssessments as $a) {
                    $sub = $assessmentSubmissions->get($a->id);
                    $items[] = [
                        'type' => 'Assessment',
                        'name' => $a->name ?: 'Assessment',
                        'course' => $a->course?->title ?? 'Unassigned',
                        'status' => $sub?->status ?? 'Not submitted',
                        'grade' => $sub?->score,
                    ];
                }

                foreach ($daySessions as $s) {
                    $items[] = [
                        'type' => 'Session',
                        'session_id' => $s->id,
                        'name' => $s->title ?: ($s->course?->title ?? 'Session'),
                        'course' => $s->course?->title ?? 'Unassigned',
                        'status' => ucfirst($s->status),
                        'grade' => null,
                        'time' => Carbon::parse($s->getEffectiveStartTime())->format('g:i A').' – '.Carbon::parse($s->getEffectiveEndTime())->format('g:i A'),
                        'session_type' => $s->type === 'one_on_one' ? 'One-On-One' : 'Group',
                    ];
                }

                $events[$key] = $items;
            }
        }

        $this->calendar = [
            'month' => $reference->format('F Y'),
            'month_num' => (int) $reference->format('m'),
            'year' => (int) $reference->format('Y'),
            'start_day' => $monthStart->dayOfWeek,
            'days' => $days,
            'prev' => ['year' => (int) $reference->copy()->subMonth()->format('Y'), 'month' => (int) $reference->copy()->subMonth()->format('m')],
            'next' => ['year' => (int) $reference->copy()->addMonth()->format('Y'), 'month' => (int) $reference->copy()->addMonth()->format('m')],
        ];

        $this->calendarEvents = $events;
    }

    public function selectDay(?string $date = null): void
    {
        if ($this->selectedDate === $date) {
            $this->selectedDate = null;
        } else {
            $this->selectedDate = $date;
        }
    }

    public function openSessionDetails(int $sessionId): void
    {
        $session = CourseSession::query()
            ->with(['course', 'instructor'])
            ->find($sessionId);

        if (! $session) {
            return;
        }

        $canAddToCalendar = in_array($session->status, ['scheduled', 'rescheduled'], true) && $session->effectiveEndAt()->isFuture();
        $effectiveDate = $session->getEffectiveDate();

        $this->selectedSessionId = $sessionId;
        $this->selectedSessionDetails = [
            'id' => $session->id,
            'title' => $session->title ?: ($session->course->title ?? 'Class Session'),
            'course_title' => $session->course->title ?? '—',
            'course_code' => $session->course->code ?? '',
            'type' => $session->type,
            'type_label' => $session->type === 'one_on_one' ? 'One-On-One' : 'Cohort / Group',
            'instructor_name' => $session->instructor?->name ?? 'Assigned Instructor',
            'instructor_email' => $session->instructor?->email,
            'instructor_whatsapp' => $session->instructor?->whatsapp,
            'session_date' => $effectiveDate->format('l, F j, Y'),
            'session_date_raw' => $effectiveDate->format('Y-m-d'),
            'start_time' => Carbon::parse($session->getEffectiveStartTime())->format('g:i A'),
            'end_time' => Carbon::parse($session->getEffectiveEndTime())->format('g:i A'),
            'status' => $session->status,
            'meeting_link' => $session->meeting_link,
            'notes' => $session->notes,
            'is_today' => $effectiveDate->isToday(),
            'can_add_to_calendar' => $canAddToCalendar,
            'google_calendar_url' => $canAddToCalendar ? $this->buildGoogleCalendarUrl($session) : null,
        ];
        $this->showSessionDetailsModal = true;
    }

    public function closeSessionDetails(): void
    {
        $this->showSessionDetailsModal = false;
        $this->selectedSessionId = null;
        $this->selectedSessionDetails = null;
    }

    protected function buildGoogleCalendarUrl(CourseSession $session): string
    {
        $timezone = config('app.timezone', 'UTC');

        try {
            $startAt = $session->effectiveStartAt()->copy()->utc();
            $endAt = $session->effectiveEndAt()->copy()->utc();
        } catch (\Throwable) {
            $startAt = Carbon::today()->utc();
            $endAt = $startAt->copy()->addHours(1);
        }

        if ($endAt->lessThanOrEqualTo($startAt)) {
            $endAt = $startAt->copy()->addHours(1);
        }

        $courseTitle = $session->course?->title;
        $sessionTitle = $session->title ?: ($session->type === 'one_on_one' ? 'One-On-One Session' : 'Group Session');
        $title = $courseTitle ? trim($courseTitle.' — '.$sessionTitle) : $sessionTitle;

        $details = array_filter([
            $session->course?->code ? 'Course: '.$session->course->code : null,
            'Session type: '.($session->type === 'one_on_one' ? 'One-On-One' : 'Group'),
            $session->instructor?->name ? 'Instructor: '.$session->instructor->name : null,
            $session->notes ? 'Notes: '.$session->notes : null,
        ]);

        return 'https://calendar.google.com/calendar/render?'.http_build_query([
            'action' => 'TEMPLATE',
            'text' => $title,
            'dates' => $startAt->format('Ymd\THis\Z').'/'.$endAt->format('Ymd\THis\Z'),
            'ctz' => $timezone,
            'details' => implode("\n", $details),
        ]);
    }
}
