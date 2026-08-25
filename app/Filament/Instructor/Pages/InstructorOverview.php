<?php

namespace App\Filament\Instructor\Pages;

use App\Models\Assessment;
use App\Models\Course;
use App\Models\CourseSession;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

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

    public int $pendingSubmissionsCount = 0;

    public array $calendarWeeks = [];

    public string $calendarMonth = '';

    public string $calendarYear = '';

    public int $upcomingSessionCount = 0;

    public array $upcomingSessions = [];

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

        // Pending submissions waiting for review
        $pendingAssignments = \App\Models\AssignmentSubmission::query()
            ->whereHas('assignment', fn ($q) => $q->whereIn('course_id', $courseIds))
            ->whereNull('viewed_at')
            ->count();

        $pendingAssessments = \App\Models\AssessmentSubmission::query()
            ->whereHas('assessment', fn ($q) => $q->whereIn('course_id', $courseIds))
            ->whereNull('viewed_at')
            ->count();

        $this->pendingSubmissionsCount = $pendingAssignments + $pendingAssessments;

        $now = Carbon::now();
        $this->calendarMonth = $now->format('m');
        $this->calendarYear = $now->format('Y');
        $this->loadCalendar($courseIds);
    }

    public function previousMonth(): void
    {
        $date = Carbon::createFromDate($this->calendarYear, $this->calendarMonth, 1)->subMonth();
        $this->calendarMonth = $date->format('m');
        $this->calendarYear = $date->format('Y');

        $courseIds = auth()->user()?->instructorCourses()->pluck('courses.id')->all() ?? [];
        $this->loadCalendar($courseIds);
    }

    public function nextMonth(): void
    {
        $date = Carbon::createFromDate($this->calendarYear, $this->calendarMonth, 1)->addMonth();
        $this->calendarMonth = $date->format('m');
        $this->calendarYear = $date->format('Y');

        $courseIds = auth()->user()?->instructorCourses()->pluck('courses.id')->all() ?? [];
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
        foreach ($allSessions as $s) {
            $effectiveDate = $s->getEffectiveDate()->format('Y-m-d');
            $sessionsByDate[$effectiveDate][] = [
                'title' => $s->title ?: ($s->course->title ?? '—'),
                'course_code' => $s->course->code ?? '',
                'start_time' => Carbon::parse($s->getEffectiveStartTime())->format('g:i A'),
                'status' => $s->status,
                'type' => $s->type,
                'student_name' => $s->student?->name,
            ];
        }

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
