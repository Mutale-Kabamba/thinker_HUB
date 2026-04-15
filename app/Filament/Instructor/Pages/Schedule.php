<?php

namespace App\Filament\Instructor\Pages;

use App\Models\CourseSession;
use App\Models\User;
use App\Notifications\SessionRescheduledNotification;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

class Schedule extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Schedule';

    protected static ?string $title = 'Session Timetable';

    protected string $view = 'filament.instructor.pages.schedule';

    public array $sessions = [];

    public array $calendarWeeks = [];

    public string $calendarMonth = '';

    public string $calendarYear = '';

    public string $viewMode = 'calendar';

    public string $filterStatus = '';

    public string $filterType = '';

    public ?int $rescheduleSessionId = null;

    public ?string $rescheduleDate = null;

    public ?string $rescheduleStartTime = null;

    public ?string $rescheduleEndTime = null;

    public function mount(): void
    {
        $now = Carbon::now();
        $this->calendarMonth = $now->format('m');
        $this->calendarYear = $now->format('Y');
        $this->loadSessions();
    }

    public function updatedFilterStatus(): void
    {
        $this->loadSessions();
    }

    public function updatedFilterType(): void
    {
        $this->loadSessions();
    }

    public function previousMonth(): void
    {
        $date = Carbon::createFromDate($this->calendarYear, $this->calendarMonth, 1)->subMonth();
        $this->calendarMonth = $date->format('m');
        $this->calendarYear = $date->format('Y');
        $this->loadSessions();
    }

    public function nextMonth(): void
    {
        $date = Carbon::createFromDate($this->calendarYear, $this->calendarMonth, 1)->addMonth();
        $this->calendarMonth = $date->format('m');
        $this->calendarYear = $date->format('Y');
        $this->loadSessions();
    }

    public function markCompleted(int $sessionId): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $session = CourseSession::query()
            ->whereIn('course_id', $user->instructorCourses()->pluck('courses.id'))
            ->where('id', $sessionId)
            ->first();

        if (! $session || $session->status === 'completed') {
            return;
        }

        $session->update(['status' => 'completed']);

        Notification::make()->title('Session marked as completed.')->success()->send();
        $this->loadSessions();
    }

    public function openReschedule(int $sessionId): void
    {
        $this->rescheduleSessionId = $sessionId;
        $this->rescheduleDate = null;
        $this->rescheduleStartTime = null;
        $this->rescheduleEndTime = null;
    }

    public function cancelReschedule(): void
    {
        $this->rescheduleSessionId = null;
    }

    public function submitReschedule(): void
    {
        $user = auth()->user();
        if (! $user || ! $this->rescheduleSessionId || ! $this->rescheduleDate || ! $this->rescheduleStartTime) {
            Notification::make()->title('Please fill in the new date and start time.')->danger()->send();
            return;
        }

        $session = CourseSession::query()
            ->whereIn('course_id', $user->instructorCourses()->pluck('courses.id'))
            ->where('id', $this->rescheduleSessionId)
            ->first();

        if (! $session) {
            return;
        }

        $session->update([
            'status' => 'rescheduled',
            'rescheduled_date' => $this->rescheduleDate,
            'rescheduled_start_time' => $this->rescheduleStartTime,
            'rescheduled_end_time' => $this->rescheduleEndTime,
        ]);

        $session->refresh();
        $courseName = $session->course->title ?? 'Course';

        if ($session->isOneOnOne() && $session->student_id) {
            $student = User::find($session->student_id);
            $student?->notify(new SessionRescheduledNotification($session, $courseName));
        } else {
            $students = User::query()
                ->whereHas('enrollments', fn ($q) => $q->where('course_id', $session->course_id))
                ->get();
            foreach ($students as $student) {
                $student->notify(new SessionRescheduledNotification($session, $courseName));
            }
        }

        $this->rescheduleSessionId = null;
        Notification::make()->title('Session rescheduled. Students notified.')->success()->send();
        $this->loadSessions();
    }

    protected function loadSessions(): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $courseIds = $user->instructorCourses()->pluck('courses.id')->all();

        $query = CourseSession::query()
            ->with(['course', 'student'])
            ->whereIn('course_id', $courseIds)
            ->orderBy('session_date')
            ->orderBy('start_time');

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }

        $allSessions = $query->get();

        $this->sessions = $allSessions->map(fn (CourseSession $s) => [
            'id' => $s->id,
            'course_title' => $s->course->title ?? '—',
            'course_code' => $s->course->code ?? '',
            'type' => $s->type,
            'type_label' => $s->type === 'one_on_one' ? 'One-On-One' : 'Group',
            'student_name' => $s->student?->name,
            'title' => $s->title,
            'session_date' => $s->session_date->format('D, M j, Y'),
            'start_time' => Carbon::parse($s->start_time)->format('g:i A'),
            'end_time' => Carbon::parse($s->end_time)->format('g:i A'),
            'status' => $s->status,
            'rescheduled_date' => $s->rescheduled_date?->format('D, M j, Y'),
            'rescheduled_start_time' => $s->rescheduled_start_time ? Carbon::parse($s->rescheduled_start_time)->format('g:i A') : null,
            'rescheduled_end_time' => $s->rescheduled_end_time ? Carbon::parse($s->rescheduled_end_time)->format('g:i A') : null,
            'notes' => $s->notes,
        ])->all();

        $this->buildCalendar($allSessions);
    }

    protected function buildCalendar($allSessions): void
    {
        $monthStart = Carbon::createFromDate($this->calendarYear, $this->calendarMonth, 1)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

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
