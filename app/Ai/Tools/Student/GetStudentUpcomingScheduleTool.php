<?php

namespace App\Ai\Tools\Student;

use App\Models\CourseSession;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetStudentUpcomingScheduleTool implements Tool
{
    public function __construct(protected User $student)
    {
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Get the upcoming scheduled class sessions, lectures, labs, and workshops for the student across their enrolled courses.';
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $sessions = CourseSession::query()
            ->visibleTo($this->student)
            ->scheduledFirst()
            ->with(['course', 'intake', 'instructor'])
            ->limit(10)
            ->get();

        if ($sessions->isEmpty()) {
            return 'No upcoming class sessions are scheduled for your enrolled courses.';
        }

        $results = [];

        foreach ($sessions as $session) {
            $effectiveDate = $session->getEffectiveDate()->format('l, M d, Y');
            $startTime = $session->getEffectiveStartTime();
            $endTime = $session->getEffectiveEndTime();

            $results[] = [
                'session_id' => $session->id,
                'title' => $session->title,
                'course' => $session->course?->title ?? 'N/A',
                'intake' => $session->intake?->name ?? 'All Cohorts',
                'instructor' => $session->instructor?->name ?? 'Assigned Instructor',
                'date' => $effectiveDate,
                'time' => "{$startTime} - {$endTime}",
                'status' => $session->status,
                'delivery_mode' => $session->delivery_mode ?? 'live_virtual',
                'meeting_link' => $session->meeting_link,
                'physical_address' => $session->physical_address,
            ];
        }

        return json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}

