<?php

namespace App\Filament\Instructor\Pages;

use App\Filament\Actions\ImportSessionsAction;
use App\Models\CourseSession;
use App\Models\User;
use App\Notifications\RescheduleRequestDeclinedNotification;
use App\Notifications\RescheduleRequestSubmittedNotification;
use App\Notifications\SessionRescheduledNotification;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Notifications\DatabaseNotification;
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

    public array $pendingRescheduleRequests = [];

    public ?string $decisionNotificationId = null;

    public ?int $decisionSessionId = null;

    public ?int $decisionStudentId = null;

    public string $decisionStudentName = '';

    public string $decisionReason = '';

    public ?string $decisionPreferredDate = null;

    public ?string $decisionPreferredTime = null;

    public string $decisionStep = 'review';

    public ?string $decisionDate = null;

    public ?string $decisionStartTime = null;

    public ?string $decisionEndTime = null;

    public string $declineReason = '';

    public bool $suppressAutoOpenDecisionWizard = false;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        $courseIds = $user ? $user->instructorCourses()->pluck('courses.id') : collect();

        return [
            ImportSessionsAction::makeForInstructor($courseIds, $user?->id ?? 0),
        ];
    }

    public function mount(): void
    {
        $now = Carbon::now();
        $this->calendarMonth = $now->format('m');
        $this->calendarYear = $now->format('Y');
        $this->loadSessions();

        $sessionId = (int) request()->integer('reschedule_session');
        if ($sessionId > 0) {
            $notification = $this->resolvePendingNotificationBySessionId($sessionId);
            if ($notification) {
                $this->openDecisionWizard($notification->id);
            }
        }
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
        $this->notifyStudentsAboutReschedule($session);

        $this->rescheduleSessionId = null;
        Notification::make()->title('Session rescheduled. Students notified.')->success()->send();
        $this->loadSessions();
    }

    public function openDecisionWizard(string $notificationId): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $notification = $user->notifications()
            ->where('id', $notificationId)
            ->where('type', 'App\\Notifications\\RescheduleRequestNotification')
            ->first();

        if (! $notification) {
            Notification::make()->title('Request no longer available.')->warning()->send();

            return;
        }

        $data = $notification->data;

        if (! $this->isPendingRescheduleRequestData($data)) {
            Notification::make()->title('This request has already been handled.')->warning()->send();

            return;
        }

        $this->decisionNotificationId = $notification->id;
        $this->decisionSessionId = isset($data['session_id']) ? (int) $data['session_id'] : null;
        $this->decisionStudentId = isset($data['student_id']) ? (int) $data['student_id'] : null;
        $this->decisionStudentName = (string) ($data['student_name'] ?? 'Student');
        $this->decisionReason = (string) ($data['reason'] ?? '');
        $this->decisionPreferredDate = $data['preferred_date'] ?? null;
        $this->decisionPreferredTime = $data['preferred_time'] ?? null;

        $this->decisionStep = 'review';
        $this->decisionDate = $this->decisionPreferredDate;
        $this->decisionStartTime = $this->decisionPreferredTime;
        $this->decisionEndTime = null;
        $this->declineReason = '';
    }

    public function closeDecisionWizard(): void
    {
        $this->suppressAutoOpenDecisionWizard = true;
        $this->decisionNotificationId = null;
        $this->decisionSessionId = null;
        $this->decisionStudentId = null;
        $this->decisionStudentName = '';
        $this->decisionReason = '';
        $this->decisionPreferredDate = null;
        $this->decisionPreferredTime = null;
        $this->decisionStep = 'review';
        $this->decisionDate = null;
        $this->decisionStartTime = null;
        $this->decisionEndTime = null;
        $this->declineReason = '';
    }

    public function setDecisionStep(string $step): void
    {
        if (! in_array($step, ['review', 'accept', 'decline'], true)) {
            return;
        }

        $this->decisionStep = $step;
    }

    public function acceptRescheduleRequest(): void
    {
        $user = auth()->user();

        if (! $user || ! $this->decisionNotificationId || ! $this->decisionSessionId) {
            return;
        }

        if (! $this->decisionDate || ! $this->decisionStartTime) {
            Notification::make()->title('Please provide a rescheduled date and start time.')->danger()->send();

            return;
        }

        $notification = $this->resolveDecisionNotification($user, $this->decisionNotificationId);

        if (! $notification) {
            Notification::make()->title('Request no longer available.')->warning()->send();
            $this->closeDecisionWizard();
            $this->loadPendingRescheduleRequests();

            return;
        }

        $session = $this->resolveInstructorSession($user, $this->decisionSessionId);

        if (! $session) {
            Notification::make()->title('Session not found for this request.')->danger()->send();

            return;
        }

        $session->update([
            'status' => 'rescheduled',
            'rescheduled_date' => $this->decisionDate,
            'rescheduled_start_time' => $this->decisionStartTime,
            'rescheduled_end_time' => $this->decisionEndTime,
        ]);

        $session->refresh();
        $this->notifyStudentsAboutReschedule($session);

        $notification->update([
            'read_at' => now(),
            'data' => array_merge($notification->data ?? [], ['decision_status' => 'accepted']),
        ]);

        $this->updateStudentRequestDecision($this->decisionSessionId, $this->decisionStudentId, 'accepted');

        Notification::make()->title('Reschedule request accepted and students notified.')->success()->send();

        $this->closeDecisionWizard();
        $this->loadSessions();
    }

    public function declineRescheduleRequest(): void
    {
        $user = auth()->user();

        if (! $user || ! $this->decisionNotificationId || ! $this->decisionSessionId) {
            return;
        }

        $notification = $this->resolveDecisionNotification($user, $this->decisionNotificationId);

        if (! $notification) {
            Notification::make()->title('Request no longer available.')->warning()->send();
            $this->closeDecisionWizard();
            $this->loadPendingRescheduleRequests();

            return;
        }

        $session = $this->resolveInstructorSession($user, $this->decisionSessionId);

        if (! $session) {
            Notification::make()->title('Session not found for this request.')->danger()->send();

            return;
        }

        $student = $this->decisionStudentId ? User::find($this->decisionStudentId) : null;
        if ($student) {
            $student->notify(new RescheduleRequestDeclinedNotification(
                session: $session,
                courseName: $session->course->title ?? 'Course',
                reason: trim($this->declineReason) !== '' ? trim($this->declineReason) : null,
            ));
        }

        $notification->update([
            'read_at' => now(),
            'data' => array_merge($notification->data ?? [], ['decision_status' => 'declined']),
        ]);

        $this->updateStudentRequestDecision($this->decisionSessionId, $this->decisionStudentId, 'declined');

        Notification::make()->title('Reschedule request declined. Student notified.')->success()->send();

        $this->closeDecisionWizard();
        $this->loadPendingRescheduleRequests();
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
            'live_started' => (bool) $s->live_started_at,
            'live_url' => route('live.sessions.show', ['session' => $s->id, 'host' => 1]),
        ])->all();

        $this->buildCalendar($allSessions);
        $this->loadPendingRescheduleRequests();
    }

    protected function loadPendingRescheduleRequests(): void
    {
        $user = auth()->user();

        if (! $user) {
            $this->pendingRescheduleRequests = [];

            return;
        }

        $this->pendingRescheduleRequests = $user->notifications()
            ->where('type', 'App\\Notifications\\RescheduleRequestNotification')
            ->latest()
            ->take(30)
            ->get()
            ->filter(fn (DatabaseNotification $notification): bool => $this->isPendingRescheduleRequestData($notification->data ?? []))
            ->map(function (DatabaseNotification $notification): array {
                $data = $notification->data;

                return [
                    'id' => $notification->id,
                    'session_id' => isset($data['session_id']) ? (int) $data['session_id'] : null,
                    'student_name' => (string) ($data['student_name'] ?? 'Student'),
                    'reason' => (string) ($data['reason'] ?? ''),
                    'preferred_date' => $data['preferred_date'] ?? null,
                    'preferred_time' => $data['preferred_time'] ?? null,
                    'created_at' => $notification->created_at?->diffForHumans(),
                ];
            })
            ->values()
            ->all();

        if ($this->suppressAutoOpenDecisionWizard) {
            $this->suppressAutoOpenDecisionWizard = false;

            return;
        }

        if (! $this->decisionNotificationId && ! empty($this->pendingRescheduleRequests)) {
            $this->openDecisionWizard($this->pendingRescheduleRequests[0]['id']);
        }
    }

    protected function resolveDecisionNotification(User $user, string $notificationId): ?DatabaseNotification
    {
        return $user->notifications()
            ->where('id', $notificationId)
            ->where('type', 'App\\Notifications\\RescheduleRequestNotification')
            ->latest()
            ->first();
    }

    protected function resolvePendingNotificationBySessionId(int $sessionId): ?DatabaseNotification
    {
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        return $user->notifications()
            ->where('type', 'App\\Notifications\\RescheduleRequestNotification')
            ->latest()
            ->get()
            ->first(function (DatabaseNotification $notification) use ($sessionId): bool {
                $data = $notification->data ?? [];

                return (int) ($data['session_id'] ?? 0) === $sessionId
                    && $this->isPendingRescheduleRequestData($data);
            });
    }

    protected function isPendingRescheduleRequestData(array $data): bool
    {
        return empty($data['decision_status']);
    }

    protected function resolveInstructorSession(User $user, int $sessionId): ?CourseSession
    {
        return CourseSession::query()
            ->with('course')
            ->whereIn('course_id', $user->instructorCourses()->pluck('courses.id'))
            ->where('id', $sessionId)
            ->first();
    }

    protected function notifyStudentsAboutReschedule(CourseSession $session): void
    {
        $courseName = $session->course->title ?? 'Course';

        if ($session->isOneOnOne() && $session->student_id) {
            $student = User::find($session->student_id);
            $student?->notify(new SessionRescheduledNotification($session, $courseName));

            return;
        }

        $students = User::query()
            ->whereHas('enrollments', fn ($q) => $q->where('course_id', $session->course_id))
            ->get();

        foreach ($students as $student) {
            $student->notify(new SessionRescheduledNotification($session, $courseName));
        }
    }

    protected function updateStudentRequestDecision(?int $sessionId, ?int $studentId, string $decision): void
    {
        if (! $sessionId || ! $studentId) {
            return;
        }

        $student = User::find($studentId);

        if (! $student) {
            return;
        }

        $requestNotification = $student->notifications()
            ->where('type', RescheduleRequestSubmittedNotification::class)
            ->latest()
            ->get()
            ->first(function (DatabaseNotification $notification) use ($sessionId): bool {
                $data = $notification->data ?? [];

                return (int) ($data['session_id'] ?? 0) === $sessionId
                    && in_array((string) ($data['decision_status'] ?? 'pending'), ['pending', ''], true);
            });

        if (! $requestNotification) {
            return;
        }

        $requestNotification->update([
            'read_at' => now(),
            'data' => array_merge($requestNotification->data ?? [], ['decision_status' => $decision]),
        ]);
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
