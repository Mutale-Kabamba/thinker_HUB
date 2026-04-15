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

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Overview';

    protected static ?string $title = 'Instructor Dashboard';

    protected string $view = 'filament.instructor.pages.overview';

    public array $courses = [];

    public int $totalStudents = 0;

    public int $totalAssessments = 0;

    public array $calendarWeeks = [];

    public string $calendarMonth = '';

    public string $calendarYear = '';

    public int $upcomingSessionCount = 0;

    public function mount(): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $instructorCourses = $user->instructorCourses()->withCount('enrollments')->get();

        $this->courses = $instructorCourses->map(fn (Course $course) => [
            'id' => $course->id,
            'title' => $course->title,
            'code' => $course->code,
            'students' => $course->enrollments_count ?? 0,
            'is_active' => $course->is_active,
        ])->toArray();

        $this->totalStudents = $instructorCourses->sum('enrollments_count');

        $courseIds = $instructorCourses->pluck('id')->toArray();
        $this->totalAssessments = Assessment::query()->whereIn('course_id', $courseIds)->count();

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

        $this->upcomingSessionCount = $allSessions->where('status', 'scheduled')->count();

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
