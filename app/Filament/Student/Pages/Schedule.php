<?php

namespace App\Filament\Student\Pages;

use App\Models\CourseSession;
use App\Models\User;
use App\Notifications\RescheduleRequestNotification;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

class Schedule extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationLabel = 'Schedule';

    protected static ?string $title = 'My Schedule';

    protected string $view = 'filament.student.pages.schedule';

    public array $sessions = [];

    public array $courseProgress = [];

    public array $calendarWeeks = [];

    public string $calendarMonth = '';

    public string $calendarYear = '';

    public string $filterStatus = '';

    public string $viewMode = 'calendar';

    public ?int $rescheduleRequestSessionId = null;

    public string $rescheduleRequestReason = '';

    public ?string $reschedulePreferredDate = null;

    public ?string $reschedulePreferredTime = null;

    public function mount(): void
    {
        $now = Carbon::now();
        $this->calendarMonth = $now->format('m');
        $this->calendarYear = $now->format('Y');
        $this->loadData();
    }

    public function updatedFilterStatus(): void
    {
        $this->loadData();
    }

    public function previousMonth(): void
    {
        $date = Carbon::createFromDate($this->calendarYear, $this->calendarMonth, 1)->subMonth();
        $this->calendarMonth = $date->format('m');
        $this->calendarYear = $date->format('Y');
        $this->loadData();
    }

    public function nextMonth(): void
    {
        $date = Carbon::createFromDate($this->calendarYear, $this->calendarMonth, 1)->addMonth();
        $this->calendarMonth = $date->format('m');
        $this->calendarYear = $date->format('Y');
        $this->loadData();
    }

    public function openRescheduleRequest(int $sessionId): void
    {
        $this->rescheduleRequestSessionId = $sessionId;
        $this->rescheduleRequestReason = '';
        $this->reschedulePreferredDate = null;
        $this->reschedulePreferredTime = null;
    }

    public function cancelRescheduleRequest(): void
    {
        $this->rescheduleRequestSessionId = null;
    }

    public function submitRescheduleRequest(): void
    {
        $user = auth()->user();
        if (! $user || ! $this->rescheduleRequestSessionId) {
            return;
        }

        if (empty(trim($this->rescheduleRequestReason))) {
            Notification::make()->title('Please provide a reason for rescheduling.')->danger()->send();
            return;
        }

        $session = CourseSession::query()
            ->with('course')
            ->where('id', $this->rescheduleRequestSessionId)
            ->first();

        if (! $session) {
            return;
        }

        // Notify the instructor (or admins if no instructor assigned)
        $recipients = collect();

        if ($session->instructor_id) {
            $instructor = User::find($session->instructor_id);
            if ($instructor) {
                $recipients->push($instructor);
            }
        }

        // Also notify admins
        User::query()->where('role', 'admin')->each(fn ($admin) => $recipients->push($admin));

        $recipients->unique('id')->each(fn (User $recipient) => $recipient->notify(
            new RescheduleRequestNotification(
                session: $session,
                studentName: $user->name,
                reason: trim($this->rescheduleRequestReason),
                preferredDate: $this->reschedulePreferredDate,
                preferredTime: $this->reschedulePreferredTime,
            )
        ));

        $this->rescheduleRequestSessionId = null;
        Notification::make()->title('Reschedule request sent to instructor.')->success()->send();
    }

    protected function loadData(): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $enrolledCourseIds = $user->courses()->pluck('courses.id')->all();

        if (empty($enrolledCourseIds)) {
            $this->sessions = [];
            $this->courseProgress = [];
            $this->calendarWeeks = [];

            return;
        }

        $query = CourseSession::query()
            ->with(['course', 'instructor'])
            ->where(function ($q) use ($enrolledCourseIds, $user) {
                $q->where(function ($q2) use ($enrolledCourseIds) {
                    $q2->whereIn('course_id', $enrolledCourseIds)
                        ->where('type', 'group');
                })->orWhere(function ($q2) use ($user) {
                    $q2->where('type', 'one_on_one')
                        ->where('student_id', $user->id);
                });
            })
            ->orderBy('session_date')
            ->orderBy('start_time');

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        $allSessions = $query->get();

        $this->sessions = $allSessions->map(fn (CourseSession $s) => [
            'id' => $s->id,
            'course_title' => $s->course->title ?? '—',
            'course_code' => $s->course->code ?? '',
            'type' => $s->type,
            'type_label' => $s->type === 'one_on_one' ? 'One-On-One' : 'Group',
            'instructor_name' => $s->instructor?->name,
            'title' => $s->title,
            'session_date' => $s->session_date->format('D, M j, Y'),
            'session_date_raw' => $s->session_date->format('Y-m-d'),
            'start_time' => Carbon::parse($s->start_time)->format('g:i A'),
            'end_time' => Carbon::parse($s->end_time)->format('g:i A'),
            'status' => $s->status,
            'rescheduled_date' => $s->rescheduled_date?->format('D, M j, Y'),
            'rescheduled_date_raw' => $s->rescheduled_date?->format('Y-m-d'),
            'rescheduled_start_time' => $s->rescheduled_start_time ? Carbon::parse($s->rescheduled_start_time)->format('g:i A') : null,
            'rescheduled_end_time' => $s->rescheduled_end_time ? Carbon::parse($s->rescheduled_end_time)->format('g:i A') : null,
            'notes' => $s->notes,
            'is_today' => $s->getEffectiveDate()->isToday(),
            'is_past' => $s->getEffectiveDate()->isPast() && ! $s->getEffectiveDate()->isToday(),
        ])->all();

        // Build calendar grid
        $this->buildCalendar($allSessions);

        // Course progress
        $this->courseProgress = [];
        $grouped = $allSessions->groupBy('course_id');
        foreach ($grouped as $courseId => $courseSessions) {
            $total = $courseSessions->count();
            $completed = $courseSessions->where('status', 'completed')->count();
            $course = $courseSessions->first()->course;
            $this->courseProgress[] = [
                'course_title' => $course->title ?? '—',
                'course_code' => $course->code ?? '',
                'total' => $total,
                'completed' => $completed,
                'percentage' => $total > 0 ? round(($completed / $total) * 100) : 0,
            ];
        }
    }

    protected function buildCalendar($allSessions): void
    {
        $monthStart = Carbon::createFromDate($this->calendarYear, $this->calendarMonth, 1)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        // Index sessions by their effective date
        $sessionsByDate = [];
        foreach ($allSessions as $s) {
            $effectiveDate = $s->getEffectiveDate()->format('Y-m-d');
            $sessionsByDate[$effectiveDate][] = [
                'id' => $s->id,
                'title' => $s->title ?: ($s->course->title ?? '—'),
                'course_code' => $s->course->code ?? '',
                'start_time' => Carbon::parse($s->getEffectiveStartTime())->format('g:i A'),
                'status' => $s->status,
                'type' => $s->type,
            ];
        }

        // Build weeks array (Sun-Sat grid)
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
