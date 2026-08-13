<?php

namespace App\Filament\Student\Pages;

use App\Models\CourseSession;
use App\Models\User;
use App\Notifications\RescheduleRequestNotification;
use App\Notifications\RescheduleRequestSubmittedNotification;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Carbon;

class Schedule extends Page
{
    private const DEFAULT_CALENDAR_DURATION_HOURS = 1;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string|\UnitEnum|null $navigationGroup = 'LEARNING';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Schedule';

    protected static ?string $title = 'My Schedule';

    protected string $view = 'filament.student.pages.schedule';

    public string $rangeMode = 'month'; // 'day', 'week', 'month', 'custom'

    public string $currentDate = '';

    public ?string $customStartDate = null;

    public ?string $customEndDate = null;

    public string $filterStatus = ''; // '' for all, 'scheduled', 'completed', 'rescheduled', 'cancelled'

    public string $searchSession = '';

    public string $periodTitle = '';

    public array $statusCounts = [
        'all' => 0,
        'scheduled' => 0,
        'completed' => 0,
        'rescheduled' => 0,
        'cancelled' => 0,
    ];

    public array $sessions = [];

    public array $filteredSessions = [];

    public array $weekDays = [];

    public array $dayViewData = [];

    public array $calendarWeeks = [];

    public array $customDays = [];

    public string $calendarMonth = '';

    public string $calendarYear = '';

    // Modal state for session details pop-up
    public ?int $selectedSessionId = null;

    public ?array $selectedSessionDetails = null;

    public bool $showSessionDetailsModal = false;

    // Reschedule request drawer/form state
    public ?int $rescheduleRequestSessionId = null;

    public string $rescheduleRequestReason = '';

    public ?string $reschedulePreferredDate = null;

    public ?string $reschedulePreferredTime = null;

    public array $rescheduleRequests = [];

    public bool $showRequestHistory = false;

    // Analytics & Progress
    public array $courseProgress = [];

    public array $attendanceSummary = [];

    public array $attendanceRecords = [];

    public function mount(): void
    {
        $now = Carbon::now();
        $this->currentDate = $now->format('Y-m-d');
        $this->calendarMonth = $now->format('m');
        $this->calendarYear = $now->format('Y');
        $this->customStartDate = $now->copy()->startOfWeek()->format('Y-m-d');
        $this->customEndDate = $now->copy()->endOfWeek()->format('Y-m-d');

        $this->loadData();
    }

    public function setRangeMode(string $mode): void
    {
        if (in_array($mode, ['day', 'week', 'month', 'custom'], true)) {
            $this->rangeMode = $mode;
            $this->loadData();
        }
    }

    public function setFilterStatus(string $status): void
    {
        $this->filterStatus = $status;
        $this->loadData();
    }

    public function updatedFilterStatus(): void
    {
        $this->loadData();
    }

    public function updatedSearchSession(): void
    {
        $this->loadData();
    }

    public function updatedCustomStartDate(): void
    {
        if ($this->rangeMode === 'custom') {
            $this->loadData();
        }
    }

    public function updatedCustomEndDate(): void
    {
        if ($this->rangeMode === 'custom') {
            $this->loadData();
        }
    }

    public function updatedShowRequestHistory(): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $this->loadRescheduleRequests($user);
    }

    public function previousPeriod(): void
    {
        $curr = Carbon::parse($this->currentDate);

        if ($this->rangeMode === 'day') {
            $this->currentDate = $curr->subDay()->format('Y-m-d');
        } elseif ($this->rangeMode === 'week') {
            $this->currentDate = $curr->subWeek()->format('Y-m-d');
        } elseif ($this->rangeMode === 'month') {
            $this->currentDate = $curr->subMonth()->format('Y-m-d');
            $this->calendarMonth = Carbon::parse($this->currentDate)->format('m');
            $this->calendarYear = Carbon::parse($this->currentDate)->format('Y');
        }

        $this->loadData();
    }

    public function nextPeriod(): void
    {
        $curr = Carbon::parse($this->currentDate);

        if ($this->rangeMode === 'day') {
            $this->currentDate = $curr->addDay()->format('Y-m-d');
        } elseif ($this->rangeMode === 'week') {
            $this->currentDate = $curr->addWeek()->format('Y-m-d');
        } elseif ($this->rangeMode === 'month') {
            $this->currentDate = $curr->addMonth()->format('Y-m-d');
            $this->calendarMonth = Carbon::parse($this->currentDate)->format('m');
            $this->calendarYear = Carbon::parse($this->currentDate)->format('Y');
        }

        $this->loadData();
    }

    public function goToToday(): void
    {
        $now = Carbon::now();
        $this->currentDate = $now->format('Y-m-d');
        $this->calendarMonth = $now->format('m');
        $this->calendarYear = $now->format('Y');
        $this->loadData();
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
            'is_past' => $effectiveDate->isPast() && ! $effectiveDate->isToday(),
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

    public function openClassModal(int $id): void
    {
        $this->openSessionDetails($id);
    }

    public function closeClassModal(): void
    {
        $this->closeSessionDetails();
    }

    public function openRescheduleFromDetails(): void
    {
        if ($this->selectedSessionId) {
            $sessionId = $this->selectedSessionId;
            $this->closeSessionDetails();
            $this->openRescheduleRequest($sessionId);
        }
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

        $recipients = collect();

        if ($session->instructor_id) {
            $instructor = User::find($session->instructor_id);
            if ($instructor) {
                $recipients->push($instructor);
            }
        }

        User::query()->where('role', 'admin')->each(fn ($admin) => $recipients->push($admin));

        $recipients->unique('id')->each(fn (User $recipient) => $recipient->notify(
            new RescheduleRequestNotification(
                session: $session,
                studentId: $user->id,
                studentName: $user->name,
                reason: trim($this->rescheduleRequestReason),
                preferredDate: $this->reschedulePreferredDate,
                preferredTime: $this->reschedulePreferredTime,
            )
        ));

        $user->notify(new RescheduleRequestSubmittedNotification(
            session: $session,
            reason: trim($this->rescheduleRequestReason),
            preferredDate: $this->reschedulePreferredDate,
            preferredTime: $this->reschedulePreferredTime,
        ));

        $this->rescheduleRequestSessionId = null;
        Notification::make()->title('Reschedule request sent to instructor.')->success()->send();
        $this->loadData();
    }

    public function loadData(): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $this->loadRescheduleRequests($user);
        $this->loadAttendance($user);

        $enrolledCourseIds = $user->courses()->pluck('courses.id')->all();

        if (empty($enrolledCourseIds)) {
            $this->sessions = [];
            $this->filteredSessions = [];
            $this->courseProgress = [];
            $this->calendarWeeks = [];
            $this->weekDays = [];
            $this->dayViewData = [];
            $this->customDays = [];
            $this->statusCounts = [
                'all' => 0,
                'scheduled' => 0,
                'completed' => 0,
                'rescheduled' => 0,
                'cancelled' => 0,
            ];

            return;
        }

        $allAccessibleQuery = CourseSession::query()
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

        $allAccessibleSessions = $allAccessibleQuery->get();

        // Calculate status counts across all accessible sessions
        $this->statusCounts = [
            'all' => $allAccessibleSessions->count(),
            'scheduled' => $allAccessibleSessions->where('status', 'scheduled')->count(),
            'completed' => $allAccessibleSessions->where('status', 'completed')->count(),
            'rescheduled' => $allAccessibleSessions->where('status', 'rescheduled')->count(),
            'cancelled' => $allAccessibleSessions->where('status', 'cancelled')->count(),
        ];

        // Format all sessions for structured consumption
        $this->sessions = $allAccessibleSessions->map(function (CourseSession $s): array {
            $canAddToCalendar = in_array($s->status, ['scheduled', 'rescheduled'], true) && $s->effectiveEndAt()->isFuture();
            $effectiveDate = $s->getEffectiveDate();
            $startTime = $s->getEffectiveStartTime();
            $endTime = $s->getEffectiveEndTime();

            return [
                'id' => $s->id,
                'course_title' => $s->course?->title ?? '—',
                'course_code' => $s->course?->code ?? '',
                'type' => $s->type,
                'type_label' => $s->type === 'one_on_one' ? 'One-On-One' : 'Group',
                'instructor_name' => $s->instructor?->name ?? 'Instructor',
                'title' => $s->title ?: ($s->course?->title ?? 'Session'),
                'session_date' => $effectiveDate->format('D, M j, Y'),
                'session_date_raw' => $effectiveDate->format('Y-m-d'),
                'start_time' => filled($startTime) ? Carbon::parse($startTime)->format('g:i A') : '—',
                'end_time' => filled($endTime) ? Carbon::parse($endTime)->format('g:i A') : '—',
                'status' => $s->status,
                'rescheduled_date' => $s->rescheduled_date?->format('D, M j, Y'),
                'rescheduled_date_raw' => $s->rescheduled_date?->format('Y-m-d'),
                'notes' => $s->notes,
                'is_today' => $effectiveDate->isToday(),
                'is_past' => $effectiveDate->isPast() && ! $effectiveDate->isToday(),
                'can_add_to_calendar' => $canAddToCalendar,
                'google_calendar_url' => $canAddToCalendar ? $this->buildGoogleCalendarUrl($s) : null,
            ];
        })->all();

        // Compute date range based on rangeMode & currentDate
        $refDate = filled($this->currentDate) ? Carbon::parse($this->currentDate) : Carbon::today();

        if ($this->rangeMode === 'day') {
            $startDate = $refDate->copy()->startOfDay();
            $endDate = $refDate->copy()->endOfDay();
            $this->periodTitle = $refDate->format('l, F j, Y');
        } elseif ($this->rangeMode === 'week') {
            $startDate = $refDate->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
            $endDate = $refDate->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();
            $this->periodTitle = $startDate->format('M j').' – '.$endDate->format('M j, Y');
        } elseif ($this->rangeMode === 'month') {
            $startDate = $refDate->copy()->startOfMonth()->startOfDay();
            $endDate = $refDate->copy()->endOfMonth()->endOfDay();
            $this->periodTitle = $refDate->format('F Y');
            $this->calendarMonth = $refDate->format('m');
            $this->calendarYear = $refDate->format('Y');
        } else { // custom
            $startDate = filled($this->customStartDate) ? Carbon::parse($this->customStartDate)->startOfDay() : $refDate->copy()->startOfWeek();
            $endDate = filled($this->customEndDate) ? Carbon::parse($this->customEndDate)->endOfDay() : $refDate->copy()->endOfWeek();
            $this->periodTitle = $startDate->format('M j, Y').' – '.$endDate->format('M j, Y');
        }

        // Filter sessions for Right Column List & Calendar Grid
        $filtered = $allAccessibleSessions->filter(function (CourseSession $s) use ($startDate, $endDate): bool {
            $effDate = $s->getEffectiveDate()->copy()->startOfDay();
            if ($effDate->lt($startDate->copy()->startOfDay()) || $effDate->gt($endDate->copy()->endOfDay())) {
                return false;
            }

            if ($this->filterStatus && $s->status !== $this->filterStatus) {
                return false;
            }

            if (filled($this->searchSession)) {
                $term = strtolower(trim($this->searchSession));
                $matchTitle = str_contains(strtolower((string) $s->title), $term);
                $matchCourse = str_contains(strtolower((string) ($s->course?->title ?? '')), $term);
                $matchCode = str_contains(strtolower((string) ($s->course?->code ?? '')), $term);
                if (! $matchTitle && ! $matchCourse && ! $matchCode) {
                    return false;
                }
            }

            return true;
        });

        $this->filteredSessions = $filtered->map(function (CourseSession $s): array {
            $canAddToCalendar = in_array($s->status, ['scheduled', 'rescheduled'], true) && $s->effectiveEndAt()->isFuture();
            $effectiveDate = $s->getEffectiveDate();
            $startTime = $s->getEffectiveStartTime();
            $endTime = $s->getEffectiveEndTime();

            return [
                'id' => $s->id,
                'course_title' => $s->course?->title ?? '—',
                'course_code' => $s->course?->code ?? '',
                'type' => $s->type,
                'type_label' => $s->type === 'one_on_one' ? 'One-On-One' : 'Group',
                'instructor_name' => $s->instructor?->name ?? 'Instructor',
                'title' => $s->title ?: ($s->course?->title ?? 'Session'),
                'session_date' => $effectiveDate->format('D, M j, Y'),
                'session_date_raw' => $effectiveDate->format('Y-m-d'),
                'start_time' => filled($startTime) ? Carbon::parse($startTime)->format('g:i A') : '—',
                'end_time' => filled($endTime) ? Carbon::parse($endTime)->format('g:i A') : '—',
                'status' => $s->status,
                'notes' => $s->notes,
                'is_today' => $effectiveDate->isToday(),
                'is_past' => $effectiveDate->isPast() && ! $effectiveDate->isToday(),
                'can_add_to_calendar' => $canAddToCalendar,
                'google_calendar_url' => $canAddToCalendar ? $this->buildGoogleCalendarUrl($s) : null,
            ];
        })->values()->all();

        // Index sessions by effective date for calendar grids
        $sessionsByDate = [];
        foreach ($allAccessibleSessions as $s) {
            $effectiveDate = $s->getEffectiveDate()->format('Y-m-d');
            $startTime = $s->getEffectiveStartTime();
            $endTime = $s->getEffectiveEndTime();

            $sessionsByDate[$effectiveDate][] = [
                'id' => $s->id,
                'title' => $s->title ?: ($s->course?->title ?? '—'),
                'course_code' => $s->course?->code ?? '',
                'course_title' => $s->course?->title ?? '',
                'start_time' => filled($startTime) ? Carbon::parse($startTime)->format('g:i A') : '—',
                'end_time' => filled($endTime) ? Carbon::parse($endTime)->format('g:i A') : '—',
                'status' => $s->status,
                'type' => $s->type,
                'type_label' => $s->type === 'one_on_one' ? 'One-On-One' : 'Group',
                'instructor_name' => $s->instructor?->name,
            ];
        }

        // Build Week Days (7 days Monday - Sunday)
        $this->weekDays = [];
        $weekCursor = $refDate->copy()->startOfWeek(Carbon::MONDAY);
        for ($i = 0; $i < 7; $i++) {
            $dateStr = $weekCursor->format('Y-m-d');
            $this->weekDays[] = [
                'day_name' => $weekCursor->format('D'),
                'day_full' => $weekCursor->format('l'),
                'date_num' => $weekCursor->day,
                'date_full' => $dateStr,
                'is_today' => $weekCursor->isToday(),
                'sessions' => $sessionsByDate[$dateStr] ?? [],
            ];
            $weekCursor->addDay();
        }

        // Build Day View
        $dayStr = $refDate->format('Y-m-d');
        $this->dayViewData = [
            'date_full' => $dayStr,
            'day_name' => $refDate->format('l'),
            'formatted_date' => $refDate->format('F j, Y'),
            'is_today' => $refDate->isToday(),
            'sessions' => $sessionsByDate[$dayStr] ?? [],
        ];

        // Build Month Calendar (Sun-Sat grid)
        $monthStart = $refDate->copy()->startOfMonth();
        $monthEnd = $refDate->copy()->endOfMonth();
        $calStart = $monthStart->copy()->startOfWeek(Carbon::SUNDAY);
        $calEnd = $monthEnd->copy()->endOfWeek(Carbon::SATURDAY);

        $this->calendarWeeks = [];
        $calCursor = $calStart->copy();
        $week = [];

        while ($calCursor->lte($calEnd)) {
            $dateStr = $calCursor->format('Y-m-d');
            $week[] = [
                'date' => $calCursor->day,
                'date_full' => $dateStr,
                'in_month' => $calCursor->month == $monthStart->month,
                'is_today' => $calCursor->isToday(),
                'sessions' => $sessionsByDate[$dateStr] ?? [],
            ];

            if (count($week) === 7) {
                $this->calendarWeeks[] = $week;
                $week = [];
            }

            $calCursor->addDay();
        }

        // Build Custom Range Days
        $this->customDays = [];
        if ($this->rangeMode === 'custom') {
            $customCursor = $startDate->copy();
            $maxDays = 60; // limit safety
            $count = 0;
            while ($customCursor->lte($endDate) && $count < $maxDays) {
                $dateStr = $customCursor->format('Y-m-d');
                $this->customDays[] = [
                    'day_name' => $customCursor->format('D'),
                    'day_full' => $customCursor->format('l'),
                    'date_num' => $customCursor->day,
                    'date_full' => $dateStr,
                    'is_today' => $customCursor->isToday(),
                    'sessions' => $sessionsByDate[$dateStr] ?? [],
                ];
                $customCursor->addDay();
                $count++;
            }
        }

        // Course progress
        $this->courseProgress = [];
        $grouped = $allAccessibleSessions->groupBy('course_id');
        foreach ($grouped as $courseId => $courseSessions) {
            $total = $courseSessions->count();
            $completed = $courseSessions->where('status', 'completed')->count();
            $course = $courseSessions->first()?->course;
            $this->courseProgress[] = [
                'course_title' => $course?->title ?? '—',
                'course_code' => $course?->code ?? '',
                'total' => $total,
                'completed' => $completed,
                'percentage' => $total > 0 ? round(($completed / $total) * 100) : 0,
            ];
        }
    }

    protected function loadAttendance(User $user): void
    {
        $attendances = $user->attendances()
            ->with(['session.course'])
            ->whereHas('session')
            ->get()
            ->filter(fn ($attendance) => $attendance->session !== null)
            ->sortBy(fn ($attendance) => $attendance->session?->getEffectiveDate())
            ->values();

        $this->attendanceRecords = $attendances->map(fn ($attendance) => [
            'session_title' => $attendance->session?->title ?: 'Session',
            'course_title' => $attendance->session?->course?->title ?? '—',
            'session_date' => $attendance->session ? $attendance->session->getEffectiveDate()->format('D, M j, Y') : '—',
            'status' => $attendance->status,
        ])->all();

        $this->attendanceSummary = $attendances
            ->groupBy(fn ($attendance) => (int) ($attendance->session?->course_id ?? 0))
            ->map(function ($courseAttendances) {
                $total = $courseAttendances->count();
                $attended = $courseAttendances->whereIn('status', ['present', 'late'])->count();
                $firstSession = $courseAttendances->first()?->session;
                $course = $firstSession?->course;

                return [
                    'course_title' => $course?->title ?? '—',
                    'course_code' => $course?->code ?? '',
                    'attended' => $attended,
                    'total' => $total,
                    'percentage' => $total > 0 ? round(($attended / $total) * 100) : 0,
                ];
            })
            ->values()
            ->all();
    }

    protected function loadRescheduleRequests(User $user): void
    {
        $notifications = $user->notifications()
            ->whereIn('type', [
                RescheduleRequestSubmittedNotification::class,
                'App\\Notifications\\RescheduleRequestSubmittedNotification',
            ])
            ->latest()
            ->take(30)
            ->get()
            ->map(function (DatabaseNotification $notification): array {
                $data = $notification->data ?? [];

                return [
                    'id' => $notification->id,
                    'session_id' => (int) ($data['session_id'] ?? 0),
                    'message' => (string) ($data['message'] ?? ''),
                    'reason' => (string) ($data['reason'] ?? ''),
                    'preferred_date' => $data['preferred_date'] ?? null,
                    'preferred_time' => $data['preferred_time'] ?? null,
                    'decision_status' => (string) ($data['decision_status'] ?? 'pending'),
                    'created_at' => $notification->created_at ? $notification->created_at->diffForHumans() : 'Recently',
                ];
            });

        if (! $this->showRequestHistory) {
            $notifications = $notifications->filter(fn (array $request): bool => $request['decision_status'] === 'pending');
        }

        $this->rescheduleRequests = $notifications
            ->take(10)
            ->values()
            ->all();
    }

    protected function buildGoogleCalendarUrl(CourseSession $session): string
    {
        $timezone = config('app.timezone', 'UTC');

        try {
            $startAt = $session->effectiveStartAt()->copy()->utc();
            $endAt = $session->effectiveEndAt()->copy()->utc();
        } catch (\Throwable) {
            $startAt = Carbon::today()->utc();
            $endAt = $startAt->copy()->addHours(self::DEFAULT_CALENDAR_DURATION_HOURS);
        }

        if ($endAt->lessThanOrEqualTo($startAt)) {
            $endAt = $startAt->copy()->addHours(self::DEFAULT_CALENDAR_DURATION_HOURS);
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
