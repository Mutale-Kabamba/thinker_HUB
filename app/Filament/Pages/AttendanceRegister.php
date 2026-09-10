<?php

namespace App\Filament\Pages;

use App\Models\Attendance;
use App\Models\Course;
use App\Models\CourseIntake;
use App\Models\CourseSession;
use App\Services\AttendanceService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceRegister extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string|\UnitEnum|null $navigationGroup = 'ACADEMICS & CONTENT';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Attendance Register';

    protected static ?string $title = 'Attendance Register';

    protected string $view = 'filament.pages.attendance-register';

    public ?int $selectedSessionId = null;

    public ?int $filterCourseId = null;

    public ?int $filterIntakeId = null;

    public string $sessionDateFilter = 'all'; // 'all', 'today', 'upcoming', 'past'

    public string $sessionSearch = '';

    public string $searchStudent = '';

    public string $statusFilter = 'all'; // 'all', 'present', 'absent', 'late', 'apology'

    public string $viewMode = 'register'; // 'register' (markable session sheet), 'overview' (sessions list)

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->isAdmin();
    }

    public function mount(): void
    {
        $requestedSessionId = request()->query('session_id');

        if ($requestedSessionId) {
            $session = $this->getBaseSessionsQuery()->find($requestedSessionId);
            if ($session) {
                $this->selectSession((int) $requestedSessionId);

                return;
            }
        }

        $defaultSession = $this->getDefaultSession();
        if ($defaultSession) {
            $this->selectSession($defaultSession->id);
        } else {
            $this->viewMode = 'overview';
        }
    }

    protected function getBaseSessionsQuery(): Builder
    {
        return CourseSession::query();
    }

    public function getAvailableCourses(): Collection
    {
        return Course::query()
            ->where('is_active', true)
            ->orderBy('title')
            ->get();
    }

    public function getAvailableIntakes(): Collection
    {
        if (! $this->filterCourseId) {
            return CourseIntake::query()->where('status', '!=', CourseIntake::STATUS_ARCHIVED)->orderBy('name')->get();
        }

        return CourseIntake::query()
            ->where('course_id', $this->filterCourseId)
            ->where('status', '!=', CourseIntake::STATUS_ARCHIVED)
            ->orderBy('name')
            ->get();
    }

    public function getDefaultSession(): ?CourseSession
    {
        // Try today's sessions first
        $today = Carbon::today();
        $session = $this->getBaseSessionsQuery()
            ->where('session_date', '>=', $today)
            ->orderBy('session_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->first();

        if ($session) {
            return $session;
        }

        // Otherwise latest completed/past session
        return $this->getBaseSessionsQuery()
            ->orderBy('session_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->first();
    }

    public function selectSession(int $sessionId): void
    {
        $session = $this->getBaseSessionsQuery()->with(['course', 'intake', 'instructor'])->find($sessionId);

        if (! $session) {
            Notification::make()->title('Session not found or inaccessible.')->danger()->send();

            return;
        }

        $this->selectedSessionId = $session->id;
        $this->filterCourseId = $session->course_id;
        $this->filterIntakeId = $session->course_intake_id;
        $this->viewMode = 'register';

        // Automatically sync roster idempotently on selection
        app(AttendanceService::class)->syncSessionRoster($session);
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = $mode;
    }

    public function getSelectedSessionProperty(): ?CourseSession
    {
        if (! $this->selectedSessionId) {
            return null;
        }

        return $this->getBaseSessionsQuery()
            ->with(['course', 'intake', 'instructor'])
            ->find($this->selectedSessionId);
    }

    public function syncRoster(): void
    {
        $session = $this->selectedSession;

        if (! $session) {
            Notification::make()->title('No active session selected.')->warning()->send();

            return;
        }

        $result = app(AttendanceService::class)->syncSessionRoster($session);

        Notification::make()
            ->title('Roster Synchronized')
            ->body("{$result['total_eligible']} students ready ({$result['newly_created']} added to register).")
            ->success()
            ->send();
    }

    public function markStatus(int $attendanceId, string $status): void
    {
        $session = $this->selectedSession;
        if (! $session) {
            return;
        }

        try {
            $attendance = Attendance::where('course_session_id', $session->id)->findOrFail($attendanceId);
            app(AttendanceService::class)->markAttendance($attendance->id, $status);

            Notification::make()
                ->title('Attendance updated')
                ->body(($attendance->student?->name ?? 'Student') . ' marked as ' . ucfirst($status) . '.')
                ->success()
                ->duration(2500)
                ->send();
        } catch (\Throwable $e) {
            report($e);
            Notification::make()
                ->title('Failed to update attendance')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function updateNote(int $attendanceId, string $notes): void
    {
        $session = $this->selectedSession;
        if (! $session) {
            return;
        }

        try {
            $attendance = Attendance::where('course_session_id', $session->id)->findOrFail($attendanceId);
            $attendance->update(['notes' => trim($notes) ?: null]);

            Notification::make()
                ->title('Note saved')
                ->success()
                ->duration(2000)
                ->send();
        } catch (\Throwable $e) {
            report($e);
            Notification::make()->title('Failed to save note')->danger()->send();
        }
    }

    public function markAll(string $status): void
    {
        $session = $this->selectedSession;
        if (! $session) {
            return;
        }

        $count = app(AttendanceService::class)->markBatchAttendance($session, $status);

        Notification::make()
            ->title("All students marked as " . ucfirst($status))
            ->body("{$count} attendance records updated.")
            ->success()
            ->send();
    }

    public function getRegisterRecordsProperty(): Collection
    {
        $session = $this->selectedSession;
        if (! $session) {
            return collect();
        }

        $query = Attendance::query()
            ->with(['student'])
            ->where('course_session_id', $session->id)
            ->join('users', 'attendances.user_id', '=', 'users.id')
            ->select('attendances.*');

        if ($this->statusFilter !== 'all') {
            $query->where('attendances.status', $this->statusFilter);
        }

        if (filled($this->searchStudent)) {
            $term = '%' . trim($this->searchStudent) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('users.name', 'like', $term)
                  ->orWhere('users.email', 'like', $term);
            });
        }

        return $query->orderBy('users.name', 'asc')->get();
    }

    public function getAttendanceSummaryProperty(): array
    {
        $session = $this->selectedSession;
        if (! $session) {
            return [
                'total' => 0,
                'present' => 0,
                'absent' => 0,
                'late' => 0,
                'apology' => 0,
                'attended_total' => 0,
                'attendance_rate' => 0,
                'marked_count' => 0,
            ];
        }

        return app(AttendanceService::class)->getSessionAttendanceSummary($session);
    }

    public function getSessionsListProperty(): Collection
    {
        $query = $this->getBaseSessionsQuery()
            ->with(['course', 'intake', 'instructor'])
            ->withCount('attendances');

        if ($this->filterCourseId) {
            $query->where('course_id', $this->filterCourseId);
        }

        if ($this->filterIntakeId) {
            $query->where('course_intake_id', $this->filterIntakeId);
        }

        if (filled($this->sessionSearch)) {
            $term = '%' . trim($this->sessionSearch) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', $term)
                  ->orWhereHas('course', fn ($cq) => $cq->where('title', 'like', $term)->orWhere('code', 'like', $term));
            });
        }

        $today = Carbon::today();
        if ($this->sessionDateFilter === 'today') {
            $query->whereDate('session_date', $today);
        } elseif ($this->sessionDateFilter === 'upcoming') {
            $query->where('session_date', '>=', $today);
        } elseif ($this->sessionDateFilter === 'past') {
            $query->where('session_date', '<', $today);
        }

        return $query->orderBy('session_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->limit(100)
            ->get();
    }

    public function exportExcel(): ?StreamedResponse
    {
        $session = $this->selectedSession;
        if (! $session) {
            Notification::make()->title('Please select a session to export.')->warning()->send();

            return null;
        }

        return app(AttendanceService::class)->exportSessionExcel($session);
    }

    public function exportPdf()
    {
        $session = $this->selectedSession;
        if (! $session) {
            Notification::make()->title('Please select a session to export.')->warning()->send();

            return null;
        }

        try {
            $pdf = app(AttendanceService::class)->exportSessionPdf($session);
            $cleanTitle = preg_replace('/[^A-Za-z0-9_\-]/', '_', $session->title ?: 'Session');
            $filename = "Attendance_Register_{$cleanTitle}_" . date('Ymd') . '.pdf';

            return response()->streamDownload(
                fn () => print($pdf->output()),
                $filename,
                ['Content-Type' => 'application/pdf']
            );
        } catch (\Throwable $e) {
            report($e);
            Notification::make()->title('Failed to generate PDF')->body($e->getMessage())->danger()->send();

            return null;
        }
    }

    public function exportCourseCumulativeExcel(): ?StreamedResponse
    {
        if (! $this->filterCourseId) {
            Notification::make()->title('Please select a course to export cumulative attendance.')->warning()->send();

            return null;
        }

        $course = Course::find($this->filterCourseId);
        if (! $course) {
            return null;
        }

        return app(AttendanceService::class)->exportCourseCumulativeExcel($course, $this->filterIntakeId);
    }

    public function exportCourseCumulativePdf()
    {
        if (! $this->filterCourseId) {
            Notification::make()->title('Please select a course to export cumulative attendance.')->warning()->send();

            return null;
        }

        $course = Course::find($this->filterCourseId);
        if (! $course) {
            return null;
        }

        try {
            $pdf = app(AttendanceService::class)->exportCourseCumulativePdf($course, $this->filterIntakeId);
            $cleanTitle = preg_replace('/[^A-Za-z0-9_\-]/', '_', $course->title);
            $filename = "Cumulative_Attendance_{$cleanTitle}_" . date('Ymd') . '.pdf';

            return response()->streamDownload(
                fn () => print($pdf->output()),
                $filename,
                ['Content-Type' => 'application/pdf']
            );
        } catch (\Throwable $e) {
            report($e);
            Notification::make()->title('Failed to generate cumulative PDF')->body($e->getMessage())->danger()->send();

            return null;
        }
    }
}
