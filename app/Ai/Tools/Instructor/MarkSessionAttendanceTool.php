<?php

namespace App\Ai\Tools\Instructor;

use App\Models\Attendance;
use App\Models\CourseSession;
use App\Models\User;
use App\Services\AttendanceService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class MarkSessionAttendanceTool implements Tool
{
    public function __construct(protected User $instructor, protected array $allowedCourseIds)
    {
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Mark attendance for an authorized class session. Can batch-mark all students with a base status and apply individual status/note overrides.';
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'course_session_id' => $schema->integer()->required(),
            'mark_all_status' => $schema->string()->enum(Attendance::STATUSES)->nullable(),
            'individual_overrides' => $schema->array()->items(
                $schema->object([
                    'student_name_or_email' => $schema->string()->required(),
                    'status' => $schema->string()->enum(Attendance::STATUSES)->required(),
                    'notes' => $schema->string()->nullable(),
                ])
            )->nullable(),
        ];
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $sessionId = (int) ($request['course_session_id'] ?? 0);
        $session = CourseSession::find($sessionId);

        if (! $session) {
            return "Error: Session with ID #{$sessionId} was not found.";
        }

        if (! $this->isAuthorizedSession($session)) {
            return "Error: Unauthorized. You do not have permission to manage attendance for session #{$session->id} in course '{$session->course?->title}'.";
        }

        $attendanceService = app(AttendanceService::class);

        // 1. Sync session roster
        $syncResult = $attendanceService->syncSessionRoster($session);

        $markedBatch = 0;
        $batchStatus = $request['mark_all_status'] ?? null;

        // 2. Apply batch status if provided
        if ($batchStatus && in_array($batchStatus, Attendance::STATUSES, true)) {
            $markedBatch = $attendanceService->markBatchAttendance($session, $batchStatus);
        }

        // 3. Apply individual student overrides
        $overrides = $request['individual_overrides'] ?? [];
        $appliedOverrides = [];
        $unmatched = [];

        if (is_array($overrides)) {
            foreach ($overrides as $override) {
                $term = trim((string) ($override['student_name_or_email'] ?? ''));
                $status = $override['status'] ?? null;
                $notes = ! empty($override['notes']) ? (string) $override['notes'] : null;

                if ($term === '' || ! in_array($status, Attendance::STATUSES, true)) {
                    continue;
                }

                $attendances = Attendance::query()
                    ->where('course_session_id', $session->id)
                    ->whereHas('student', function ($q) use ($term) {
                        $q->where('name', 'like', "%{$term}%")
                            ->orWhere('email', 'like', "%{$term}%");
                    })
                    ->with('student')
                    ->get();

                if ($attendances->isEmpty()) {
                    $unmatched[] = $term;
                } else {
                    foreach ($attendances as $att) {
                        $attendanceService->markAttendance($att->id, $status, $notes);
                        $appliedOverrides[] = "{$att->student?->name} marked as {$status}";
                    }
                }
            }
        }

        // 4. Ensure session completion state is synced
        $session->syncAsCompleted();

        // 5. Build summary
        $summary = $attendanceService->getSessionAttendanceSummary($session);

        $output = [
            'success' => true,
            'session_id' => $session->id,
            'session_title' => $session->title,
            'session_status' => $session->status,
            'roster_synced' => $syncResult['total_eligible'],
            'batch_applied' => $batchStatus ? "{$markedBatch} students marked {$batchStatus}" : null,
            'overrides_applied' => $appliedOverrides,
            'unmatched_students' => $unmatched,
            'updated_attendance_summary' => [
                'total_enrolled' => $summary['total'],
                'present' => $summary['present'],
                'late' => $summary['late'],
                'apology' => $summary['apology'],
                'absent' => $summary['absent'],
                'attendance_rate' => "{$summary['attendance_rate']}%",
            ],
        ];

        return json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    protected function isAuthorizedSession(CourseSession $session): bool
    {
        if ($this->instructor->isAdmin()) {
            return true;
        }

        if ($session->instructor_id && (int) $session->instructor_id === (int) $this->instructor->id) {
            return true;
        }

        return in_array((int) $session->course_id, $this->allowedCourseIds, true);
    }
}

