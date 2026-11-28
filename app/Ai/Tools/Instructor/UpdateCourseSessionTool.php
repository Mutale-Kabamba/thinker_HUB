<?php

namespace App\Ai\Tools\Instructor;

use App\Models\CourseSession;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class UpdateCourseSessionTool implements Tool
{
    public function __construct(protected User $instructor, protected array $allowedCourseIds)
    {
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Update or reschedule an authorized course session, modifying title, status, rescheduled date/time, delivery mode, or meeting links.';
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'course_session_id' => $schema->integer()->required(),
            'title' => $schema->string()->nullable(),
            'status' => $schema->string()->enum(['scheduled', 'rescheduled', 'completed', 'cancelled'])->nullable(),
            'rescheduled_date' => $schema->string()->nullable(),
            'rescheduled_start_time' => $schema->string()->nullable(),
            'rescheduled_end_time' => $schema->string()->nullable(),
            'delivery_mode' => $schema->string()->enum(array_keys(CourseSession::DELIVERY_MODES))->nullable(),
            'meeting_link' => $schema->string()->nullable(),
            'physical_address' => $schema->string()->nullable(),
            'video_url' => $schema->string()->nullable(),
            'notes' => $schema->string()->nullable(),
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
            return "Error: Unauthorized. You do not have permission to modify session #{$session->id}.";
        }

        $fields = [
            'title',
            'status',
            'rescheduled_date',
            'rescheduled_start_time',
            'rescheduled_end_time',
            'delivery_mode',
            'meeting_link',
            'physical_address',
            'video_url',
            'notes',
        ];

        $updates = [];

        foreach ($fields as $field) {
            if (isset($request[$field]) && $request[$field] !== null && $request[$field] !== '') {
                $updates[$field] = $request[$field];
            }
        }

        // If rescheduled date or time provided without explicit status, set status to rescheduled
        if ((isset($updates['rescheduled_date']) || isset($updates['rescheduled_start_time'])) && ! isset($updates['status'])) {
            $updates['status'] = 'rescheduled';
        }

        if (empty($updates)) {
            return "No valid update parameters provided for session #{$session->id}.";
        }

        $session->update($updates);
        $session->refresh();

        $output = [
            'success' => true,
            'message' => "Session #{$session->id} updated successfully.",
            'session' => [
                'id' => $session->id,
                'title' => $session->title,
                'course' => $session->course?->title ?? 'N/A',
                'status' => $session->status,
                'effective_date' => $session->getEffectiveDate()->format('Y-m-d (l)'),
                'start_time' => $session->getEffectiveStartTime(),
                'end_time' => $session->getEffectiveEndTime(),
                'delivery_mode' => $session->delivery_mode,
                'meeting_link' => $session->meeting_link,
                'physical_address' => $session->physical_address,
                'notes' => $session->notes,
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

