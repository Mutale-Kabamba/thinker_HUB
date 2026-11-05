<?php

namespace App\Ai\Tools\Instructor;

use App\Models\CourseSession;
use App\Models\User;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ListInstructorSessionsTool implements Tool
{
    public function __construct(protected User $instructor, protected array $allowedCourseIds)
    {
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'List and filter teaching sessions for the instructor by course, date filter (today, upcoming, past, all, or Y-m-d), or status, with live attendance summaries.';
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'course_id' => $schema->integer()->nullable(),
            'date_filter' => $schema->string()->nullable(),
            'status' => $schema->string()->enum(['scheduled', 'rescheduled', 'completed', 'cancelled'])->nullable(),
        ];
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $courseId = ! empty($request['course_id']) ? (int) $request['course_id'] : null;
        $dateFilter = strtolower(trim((string) ($request['date_filter'] ?? 'all')));
        $status = ! empty($request['status']) ? (string) $request['status'] : null;

        $query = CourseSession::query()->with(['course', 'intake', 'instructor']);

        if (! $this->instructor->isAdmin()) {
            $query->where(function (Builder $q) {
                $q->where('instructor_id', $this->instructor->id)
                    ->orWhereIn('course_id', $this->allowedCourseIds);
            });
        }

        if ($courseId) {
            $query->where('course_id', $courseId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $today = now()->toDateString();

        if ($dateFilter === 'today') {
            $query->where(function (Builder $q) use ($today) {
                $q->where(function ($sub) use ($today) {
                    $sub->where('status', 'rescheduled')->where('rescheduled_date', $today);
                })->orWhere(function ($sub) use ($today) {
                    $sub->where('status', '!=', 'rescheduled')->where('session_date', $today);
                });
            });
        } elseif ($dateFilter === 'upcoming') {
            $query->where(function (Builder $q) use ($today) {
                $q->where(function ($sub) use ($today) {
                    $sub->where('status', 'rescheduled')->where('rescheduled_date', '>=', $today);
                })->orWhere(function ($sub) use ($today) {
                    $sub->where('status', '!=', 'rescheduled')->where('session_date', '>=', $today);
                });
            });
        } elseif ($dateFilter === 'past') {
            $query->where(function (Builder $q) use ($today) {
                $q->where(function ($sub) use ($today) {
                    $sub->where('status', 'rescheduled')->where('rescheduled_date', '<', $today);
                })->orWhere(function ($sub) use ($today) {
                    $sub->where('status', '!=', 'rescheduled')->where('session_date', '<', $today);
                });
            });
        } elseif ($dateFilter !== 'all' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFilter)) {
            $query->where(function (Builder $q) use ($dateFilter) {
                $q->where('session_date', $dateFilter)
                    ->orWhere('rescheduled_date', $dateFilter);
            });
        }

        $sessions = $query->scheduledFirst()->limit(15)->get();

        if ($sessions->isEmpty()) {
            return 'No sessions found matching your criteria for your assigned courses.';
        }

        $attendanceService = app(AttendanceService::class);
        $results = [];

        foreach ($sessions as $session) {
            $summary = $attendanceService->getSessionAttendanceSummary($session);

            $results[] = [
                'id' => $session->id,
                'title' => $session->title,
                'course' => $session->course?->title ?? 'N/A',
                'intake' => $session->intake?->name ?? 'All Cohorts',
                'status' => $session->status,
                'delivery_mode' => $session->delivery_mode ?? 'live_virtual',
                'effective_date' => $session->getEffectiveDate()->format('Y-m-d (l)'),
                'start_time' => $session->getEffectiveStartTime(),
                'end_time' => $session->getEffectiveEndTime(),
                'meeting_link' => $session->meeting_link,
                'physical_address' => $session->physical_address,
                'attendance_summary' => [
                    'total_enrolled' => $summary['total'],
                    'present' => $summary['present'],
                    'late' => $summary['late'],
                    'apology' => $summary['apology'],
                    'absent' => $summary['absent'],
                    'attendance_rate' => "{$summary['attendance_rate']}%",
                ],
            ];
        }

        return json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}

