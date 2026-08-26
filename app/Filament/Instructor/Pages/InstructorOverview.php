<?php

namespace App\Filament\Instructor\Pages;

use App\Filament\Instructor\Resources\AssessmentSubmissionResource\AssessmentSubmissionResource;
use App\Filament\Instructor\Resources\AssignmentSubmissionResource\AssignmentSubmissionResource;
use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\CourseSession;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class InstructorOverview extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected static string|\UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Overview';

    protected static ?string $title = 'Instructor Dashboard';

    protected string $view = 'filament.instructor.pages.overview';

    public array $courses = [];

    public array $classrooms = [];

    public int $totalStudents = 0;

    public int $totalAssessments = 0;

    public int $totalAssignments = 0;

    public int $pendingSubmissionsCount = 0;

    public array $calendarWeeks = [];

    public array $calendarEvents = [];

    public string $calendarMonth = '';

    public string $calendarYear = '';

    public string $calendarMonthName = '';

    public int $upcomingSessionCount = 0;

    public array $upcomingSessions = [];

    public array $todaySchedule = [];

    public array $heroBanners = [];

    public array $quickActions = [];

    public array $recentSubmissions = [];

    public array $stats = [];

    public ?string $selectedDate = null;

    public function mount(): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $courseIds = $user->isAdmin()
            ? Course::query()->where('is_active', true)->pluck('id')->all()
            : $user->instructorCourses()->pluck('courses.id')->all();

        $instructorCourses = Course::query()
            ->whereIn('id', $courseIds)
            ->with(['students:id,name,profile_photo_path', 'activeIntake', 'assignments'])
            ->withCount('enrollments')
            ->get();

        $this->courses = $instructorCourses->map(fn (Course $course) => [
            'id' => $course->id,
            'title' => $course->title,
            'code' => $course->code ?: 'CS-101',
            'category' => $course->category ?: 'Instruction',
            'duration' => $course->duration ?: '6 Weeks',
            'students' => $course->enrollments_count ?? 0,
            'student_list' => $course->students->take(4)->values()->all(),
            'intake' => $course->activeIntake?->name ?? 'Current Cohort',
            'is_active' => $course->is_active,
        ])->toArray();

        $this->classrooms = $this->courses;
        $this->totalStudents = $instructorCourses->sum('enrollments_count');
        $this->totalAssessments = Assessment::query()->whereIn('course_id', $courseIds)->count();
        $this->totalAssignments = Assignment::query()->whereIn('course_id', $courseIds)->count();

        // Pending submissions waiting for review
        $pendingAssignmentsQuery = AssignmentSubmission::query()
            ->whereHas('assignment', fn ($q) => $q->whereIn('course_id', $courseIds))
            ->whereNull('viewed_at')
            ->with(['user', 'assignment.course']);

        $pendingAssessmentsQuery = AssessmentSubmission::query()
            ->whereHas('assessment', fn ($q) => $q->whereIn('course_id', $courseIds))
            ->whereNull('viewed_at')
            ->with(['user', 'assessment.course']);

        $pendingAssignmentsCount = $pendingAssignmentsQuery->count();
        $pendingAssessmentsCount = $pendingAssessmentsQuery->count();
        $this->pendingSubmissionsCount = $pendingAssignmentsCount + $pendingAssessmentsCount;

        // Recent pending submissions for the queue feed
        $recentAssignSubmissions = $pendingAssignmentsQuery->latest('submitted_at')->take(4)->get()->map(fn ($sub) => [
            'id' => $sub->id,
            'type' => 'Assignment',
            'title' => $sub->assignment?->name ?? 'Assignment',
            'course' => $sub->assignment?->course?->title ?? 'Course',
            'student_name' => $sub->user?->name ?? 'Student',
            'student_photo' => $sub->user?->profile_photo_url ?? null,
            'initials' => Str::upper(substr($sub->user?->name ?? 'ST', 0, 2)),
            'submitted_at' => $sub->submitted_at ? $sub->submitted_at->diffForHumans() : 'Recently',
            'url' => AssignmentSubmissionResource::getUrl('edit', ['record' => $sub->id]),
            'badge_color' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300',
        ]);

        $recentAssessSubmissions = $pendingAssessmentsQuery->latest('submitted_at')->take(4)->get()->map(fn ($sub) => [
            'id' => $sub->id,
            'type' => 'Assessment',
            'title' => $sub->assessment?->name ?? 'Assessment',
            'course' => $sub->assessment?->course?->title ?? 'Course',
            'student_name' => $sub->user?->name ?? 'Student',
            'student_photo' => $sub->user?->profile_photo_url ?? null,
            'initials' => Str::upper(substr($sub->user?->name ?? 'ST', 0, 2)),
            'submitted_at' => $sub->submitted_at ? $sub->submitted_at->diffForHumans() : 'Recently',
            'url' => AssessmentSubmissionResource::getUrl('edit', ['record' => $sub->id]),
            'badge_color' => 'bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300',
        ]);

        $this->recentSubmissions = $recentAssignSubmissions->concat($recentAssessSubmissions)->take(5)->values()->all();

        $totalSubmissions = AssignmentSubmission::query()->whereHas('assignment', fn ($q) => $q->whereIn('course_id', $courseIds))->count()
            + AssessmentSubmission::query()->whereHas('assessment', fn ($q) => $q->whereIn('course_id', $courseIds))->count();
        $gradedSubmissions = max(0, $totalSubmissions - $this->pendingSubmissionsCount);
        $gradingPercent = $totalSubmissions > 0 ? (int) round(($gradedSubmissions / $totalSubmissions) * 100) : 100;

        $this->stats = [
            'classes_total' => count($this->courses),
            'students_total' => $this->totalStudents,
            'assignments_total' => $this->totalAssignments,
            'assessments_total' => $this->totalAssessments,
            'pending_reviews' => $this->pendingSubmissionsCount,
            'grading_percent' => $gradingPercent,
            'upcoming_sessions' => $this->upcomingSessionCount,
        ];

        $now = Carbon::now();
        $this->calendarMonth = $now->format('m');
        $this->calendarYear = $now->format('Y');
        $this->calendarMonthName = $now->format('F Y');
        $this->loadCalendar($courseIds);

        // Instructor Hero Carousel Slides
        $instructorName = explode(' ', trim($user->name))[0] ?? 'Instructor';
        $userPhoto = $user->profile_photo_url ?? null;

        $this->heroBanners = [
            [
                'title' => "Welcome, {$instructorName}!",
                'description' => "You are mentoring {$this->totalStudents} students across " . count($this->courses) . " active courses. Let's make today impactful.",
                'badge' => 'Instructor Workspace',
                'badge_color' => 'bg-emerald-500/20 text-emerald-300 border-emerald-400/30',
                'cta_label' => 'View Classrooms',
                'cta_url' => '#classrooms-section',
                'metric_label' => 'Active Learners',
                'metric_value' => (string) $this->totalStudents,
                'css_gradient' => 'linear-gradient(135deg, #0f766e 0%, #0d9488 45%, #042f2e 100%)',
                'avatar' => $userPhoto,
            ],
            [
                'title' => $this->pendingSubmissionsCount > 0 
                    ? "{$this->pendingSubmissionsCount} Submissions Waiting for Review" 
                    : "All Student Submissions are Graded!",
                'description' => $this->pendingSubmissionsCount > 0 
                    ? 'Prompt feedback accelerates learning. Jump into the review queue to grade assignments and offer guidance.'
                    : 'Great job staying on top of feedback! Your students appreciate your timely guidance and reviews.',
                'badge' => $this->pendingSubmissionsCount > 0 ? 'Pending Reviews' : 'All Clear',
                'badge_color' => $this->pendingSubmissionsCount > 0 ? 'bg-amber-500/20 text-amber-300 border-amber-400/30' : 'bg-teal-500/20 text-teal-300 border-teal-400/30',
                'cta_label' => 'Review Submissions',
                'cta_url' => AssignmentSubmissionResource::getUrl(),
                'metric_label' => 'Submissions Queue',
                'metric_value' => (string) $this->pendingSubmissionsCount,
                'css_gradient' => 'linear-gradient(135deg, #1e1b4b 0%, #312e81 45%, #0f172a 100%)',
                'avatar' => $userPhoto,
            ],
            [
                'title' => $this->upcomingSessionCount > 0 
                    ? "{$this->upcomingSessionCount} Live Teaching Sessions Scheduled" 
                    : 'Schedule Your Next Live Class',
                'description' => 'Coordinate interactive workshops, 1-on-1 office hours, and group webinars seamlessly on your calendar.',
                'badge' => 'Live Classes',
                'badge_color' => 'bg-indigo-500/20 text-indigo-300 border-indigo-400/30',
                'cta_label' => 'Open Schedule',
                'cta_url' => route('filament.instructor.pages.schedule'),
                'metric_label' => 'Upcoming Sessions',
                'metric_value' => (string) $this->upcomingSessionCount,
                'css_gradient' => 'linear-gradient(135deg, #0369a1 0%, #0284c7 45%, #0c4a6e 100%)',
                'avatar' => $userPhoto,
            ],
        ];

        // Quick Actions Shortcuts
        $this->quickActions = [
            [
                'label' => 'Review Submissions',
                'icon' => 'heroicon-o-clipboard-document-check',
                'url' => AssignmentSubmissionResource::getUrl(),
                'badge' => $this->pendingSubmissionsCount > 0 ? (string) $this->pendingSubmissionsCount : null,
                'color' => 'bg-teal-500 text-white',
            ],
            [
                'label' => 'Schedule Class',
                'icon' => 'heroicon-o-calendar-days',
                'url' => route('filament.instructor.pages.schedule'),
                'badge' => null,
                'color' => 'bg-indigo-500 text-white',
            ],
            [
                'label' => 'Student Results',
                'icon' => 'heroicon-o-chart-bar-square',
                'url' => route('filament.instructor.pages.student-results'),
                'badge' => null,
                'color' => 'bg-emerald-500 text-white',
            ],
            [
                'label' => 'Send Broadcast',
                'icon' => 'heroicon-o-megaphone',
                'url' => route('filament.instructor.pages.broadcasts'),
                'badge' => null,
                'color' => 'bg-purple-500 text-white',
            ],
            [
                'label' => 'Analytics',
                'icon' => 'heroicon-o-presentation-chart-line',
                'url' => route('filament.instructor.pages.analytics'),
                'badge' => null,
                'color' => 'bg-sky-500 text-white',
            ],
        ];
    }

    public function selectDate(?string $date): void
    {
        $this->selectedDate = ($this->selectedDate === $date) ? null : $date;
    }

    public function previousMonth(): void
    {
        $date = Carbon::createFromDate($this->calendarYear, $this->calendarMonth, 1)->subMonth();
        $this->calendarMonth = $date->format('m');
        $this->calendarYear = $date->format('Y');
        $this->calendarMonthName = $date->format('F Y');

        $user = auth()->user();
        $courseIds = $user?->isAdmin()
            ? Course::query()->where('is_active', true)->pluck('id')->all()
            : ($user?->instructorCourses()->pluck('courses.id')->all() ?? []);

        $this->loadCalendar($courseIds);
    }

    public function nextMonth(): void
    {
        $date = Carbon::createFromDate($this->calendarYear, $this->calendarMonth, 1)->addMonth();
        $this->calendarMonth = $date->format('m');
        $this->calendarYear = $date->format('Y');
        $this->calendarMonthName = $date->format('F Y');

        $user = auth()->user();
        $courseIds = $user?->isAdmin()
            ? Course::query()->where('is_active', true)->pluck('id')->all()
            : ($user?->instructorCourses()->pluck('courses.id')->all() ?? []);

        $this->loadCalendar($courseIds);
    }

    protected function loadCalendar(array $courseIds): void
    {
        $allSessions = CourseSession::query()
            ->with(['course', 'student'])
            ->whereIn('course_id', $courseIds)
            ->get();

        $this->upcomingSessions = $allSessions
            ->where('status', 'scheduled')
            ->sortBy(fn ($s) => $s->getEffectiveDate()->toDateString() . ' ' . $s->getEffectiveStartTime())
            ->take(5)
            ->map(fn ($s) => [
                'id' => $s->id,
                'title' => $s->title ?: ($s->course->title ?? 'Class Session'),
                'course' => $s->course?->title ?? 'Course',
                'date' => $s->getEffectiveDate()->format('M j'),
                'time' => Carbon::parse($s->getEffectiveStartTime())->format('g:i A'),
                'type' => $s->type ?? 'group',
                'student_name' => $s->student?->name,
                'is_today' => $s->getEffectiveDate()->isToday(),
            ])
            ->values()
            ->all();

        $this->upcomingSessionCount = count($this->upcomingSessions);

        $monthStart = Carbon::createFromDate($this->calendarYear, $this->calendarMonth, 1)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $sessionsByDate = [];
        $eventsList = [];

        foreach ($allSessions as $s) {
            $effectiveDate = $s->getEffectiveDate()->format('Y-m-d');
            $sessionInfo = [
                'title' => $s->title ?: ($s->course->title ?? 'Live Class'),
                'course_code' => $s->course->code ?? '',
                'course_title' => $s->course->title ?? '',
                'start_time' => Carbon::parse($s->getEffectiveStartTime())->format('g:i A'),
                'status' => $s->status,
                'type' => $s->type,
                'student_name' => $s->student?->name,
            ];
            $sessionsByDate[$effectiveDate][] = $sessionInfo;
            $eventsList[$effectiveDate][] = $sessionInfo;
        }

        $this->calendarEvents = $eventsList;

        $calStart = $monthStart->copy()->startOfWeek(Carbon::SUNDAY);
        $calEnd = $monthEnd->copy()->endOfWeek(Carbon::SATURDAY);

        $this->calendarWeeks = [];
        $current = $calStart->copy();
        $week = [];

        while ($current->lte($calEnd)) {
            $dateStr = $current->format('Y-m-d');
            $week[] = [
                'date' => $current->day,
                'date_full' => $dateStr,
                'in_month' => $current->month == $monthStart->month,
                'is_today' => $current->isToday(),
                'sessions' => $sessionsByDate[$dateStr] ?? [],
            ];

            if (count($week) === 7) {
                $this->calendarWeeks[] = $week;
                $week = [];
            }

            $current->addDay();
        }
    }
}
