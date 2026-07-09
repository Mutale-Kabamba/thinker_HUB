<?php

namespace App\Filament\Widgets;

use App\Models\CourseSession;
use App\Models\User;
use App\Notifications\RescheduleRequestDeclinedNotification;
use App\Notifications\RescheduleRequestNotification;
use App\Notifications\RescheduleRequestSubmittedNotification;
use App\Notifications\SessionRescheduledNotification;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Notifications\DatabaseNotification;

class AdminRescheduleRequestsWidget extends Widget
{
    protected string $view = 'filament.widgets.admin-reschedule-requests';

    protected int | string | array $columnSpan = 'full';

    public array $requests = [];

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

    public function mount(): void
    {
        $this->loadRequests();
    }

    public function openDecisionWizard(string $notificationId): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $notification = $user->notifications()
            ->where('id', $notificationId)
            ->where('type', RescheduleRequestNotification::class)
            ->first();

        if (! $notification) {
            Notification::make()->title('Request no longer available.')->warning()->send();

            return;
        }

        $data = $notification->data ?? [];

        if (! empty($data['decision_status'])) {
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

    public function acceptRequest(): void
    {
        if (! $this->decisionNotificationId || ! $this->decisionSessionId || ! $this->decisionDate || ! $this->decisionStartTime) {
            Notification::make()->title('Please set date and start time.')->danger()->send();

            return;
        }

        $user = auth()->user();

        if (! $user) {
            return;
        }

        $notification = $user->notifications()
            ->where('id', $this->decisionNotificationId)
            ->where('type', RescheduleRequestNotification::class)
            ->first();

        $session = CourseSession::query()->with('course')->find($this->decisionSessionId);

        if (! $notification || ! $session) {
            Notification::make()->title('Request or session not found.')->warning()->send();
            $this->closeDecisionWizard();
            $this->loadRequests();

            return;
        }

        $session->update([
            'status' => 'rescheduled',
            'rescheduled_date' => $this->decisionDate,
            'rescheduled_start_time' => $this->decisionStartTime,
            'rescheduled_end_time' => $this->decisionEndTime,
        ]);

        $this->notifyStudentsAboutReschedule($session);

        $notification->update([
            'read_at' => now(),
            'data' => array_merge($notification->data ?? [], ['decision_status' => 'accepted']),
        ]);

        $this->updateStudentRequestDecision($session->id, $this->decisionStudentId, 'accepted');

        Notification::make()->title('Reschedule request accepted. Session updated and students notified.')->success()->send();

        $this->closeDecisionWizard();
        $this->loadRequests();
    }

    public function declineRequest(): void
    {
        if (! $this->decisionNotificationId || ! $this->decisionSessionId) {
            return;
        }

        $user = auth()->user();

        if (! $user) {
            return;
        }

        $notification = $user->notifications()
            ->where('id', $this->decisionNotificationId)
            ->where('type', RescheduleRequestNotification::class)
            ->first();

        $session = CourseSession::query()->with('course')->find($this->decisionSessionId);

        if (! $notification || ! $session) {
            Notification::make()->title('Request or session not found.')->warning()->send();
            $this->closeDecisionWizard();
            $this->loadRequests();

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

        $this->updateStudentRequestDecision($session->id, $this->decisionStudentId, 'declined');

        Notification::make()->title('Reschedule request declined. Student notified.')->success()->send();

        $this->closeDecisionWizard();
        $this->loadRequests();
    }

    protected function loadRequests(): void
    {
        $user = auth()->user();

        if (! $user) {
            $this->requests = [];

            return;
        }

        $this->requests = $user->notifications()
            ->where('type', RescheduleRequestNotification::class)
            ->latest()
            ->take(50)
            ->get()
            ->filter(fn (DatabaseNotification $notification): bool => empty(($notification->data ?? [])['decision_status']))
            ->take(8)
            ->map(function (DatabaseNotification $notification): array {
                $data = $notification->data ?? [];

                return [
                    'id' => $notification->id,
                    'session_id' => (int) ($data['session_id'] ?? 0),
                    'student_name' => (string) ($data['student_name'] ?? 'Student'),
                    'reason' => (string) ($data['reason'] ?? ''),
                    'preferred_date' => $data['preferred_date'] ?? null,
                    'preferred_time' => $data['preferred_time'] ?? null,
                    'created_at' => $notification->created_at?->diffForHumans(),
                ];
            })
            ->values()
            ->all();
    }

    protected function notifyStudentsAboutReschedule(CourseSession $session): void
    {
        $session->refresh();
        $courseName = $session->course->title ?? 'Course';

        if ($session->isOneOnOne() && $session->student_id) {
            $student = User::find($session->student_id);
            $student?->notify(new SessionRescheduledNotification($session, $courseName));

            return;
        }

        $students = User::query()
            ->whereHas('enrollments', fn ($query) => $query->where('course_id', $session->course_id))
            ->get();

        foreach ($students as $student) {
            $student->notify(new SessionRescheduledNotification($session, $courseName));
        }
    }

    protected function updateStudentRequestDecision(int $sessionId, ?int $studentId, string $decision): void
    {
        if (! $studentId) {
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

    protected function getViewData(): array
    {
        return [
            'requests' => collect($this->requests),
        ];
    }
}
