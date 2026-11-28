<?php

namespace App\Ai\Tools\Instructor;

use App\Models\Course;
use App\Models\CourseSession;
use App\Models\User;
use App\Services\AttendanceService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class CreateCourseSessionTool implements Tool
{
    public function __construct(protected User $instructor, protected array $allowedCourseIds)
    {
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Create a new scheduled teaching session (lecture, workshop, lab) for an authorized course and automatically sync its attendance roster.';
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'course_id' => $schema->integer()->required(),
            'course_intake_id' => $schema->integer()->nullable(),
            'title' => $schema->string()->required(),
            'session_date' => $schema->string()->required(),
            'start_time' => $schema->string()->required(),
            'end_time' => $schema->string()->required(),
            'delivery_mode' => $schema->string()->enum(array_keys(CourseSession::DELIVERY_MODES))->nullable(),
            'type' => $schema->string()->enum(['group', 'one_on_one'])->nullable(),
            'meeting_link' => $schema->string()->nullable(),
            'physical_address' => $schema->string()->nullable(),
            'notes' => $schema->string()->nullable(),
        ];
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $courseId = (int) ($request['course_id'] ?? 0);

        if (! $this->isAuthorizedCourse($courseId)) {
            return "Error: Unauthorized. You cannot create sessions for course ID #{$courseId}.";
        }

        $course = Course::find($courseId);
        if (! $course) {
            return "Error: Course with ID #{$courseId} does not exist.";
        }

        $session = CourseSession::create([
            'course_id' => $courseId,
            'course_intake_id' => ! empty($request['course_intake_id']) ? (int) $request['course_intake_id'] : null,
            'instructor_id' => $this->instructor->id,
            'title' => trim((string) $request['title']),
            'session_date' => trim((string) $request['session_date']),
            'start_time' => trim((string) $request['start_time']),
            'end_time' => trim((string) $request['end_time']),
            'delivery_mode' => $request['delivery_mode'] ?? 'live_virtual',
            'type' => $request['type'] ?? 'group',
            'status' => CourseSession::STATUS_SCHEDULED,
            'meeting_link' => $request['meeting_link'] ?? null,
            'physical_address' => $request['physical_address'] ?? null,
            'notes' => $request['notes'] ?? null,
        ]);

        $sync = app(AttendanceService::class)->syncSessionRoster($session);

        $output = [
            'success' => true,
            'message' => "Session '{$session->title}' created successfully for course '{$course->title}'.",
            'session_id' => $session->id,
            'status' => $session->status,
            'date' => $session->session_date,
            'time' => "{$session->start_time} - {$session->end_time}",
            'delivery_mode' => $session->delivery_mode,
            'roster_students_synced' => $sync['total_eligible'],
        ];

        return json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    protected function isAuthorizedCourse(int $courseId): bool
    {
        if ($this->instructor->isAdmin()) {
            return true;
        }

        return in_array($courseId, $this->allowedCourseIds, true);
    }
}

