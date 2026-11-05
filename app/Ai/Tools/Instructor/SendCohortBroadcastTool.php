<?php

namespace App\Ai\Tools\Instructor;

use App\Mail\CohortBroadcast;
use App\Models\Broadcast;
use App\Models\Course;
use App\Models\User;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SendCohortBroadcastTool implements Tool
{
    public function __construct(protected User $instructor, protected array $allowedCourseIds)
    {
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Send an official announcement broadcast to all enrolled students in an authorized course. Creates an announcement record and delivers in-app dashboard notifications to the cohort.';
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'course_id' => $schema->integer()->description('The course ID to send the broadcast to.')->required(),
            'subject' => $schema->string()->description('The headline or subject line of the announcement.')->required(),
            'message' => $schema->string()->description('The body content of the announcement to communicate to the cohort.')->required(),
        ];
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $courseId = (int) ($request['course_id'] ?? 0);
        $subject = trim((string) ($request['subject'] ?? ''));
        $body = trim((string) ($request['message'] ?? ''));

        if ($courseId <= 0) {
            return 'Error: Please provide a valid "course_id".';
        }

        if ($subject === '') {
            return 'Error: Please provide a "subject" for the broadcast.';
        }

        if ($body === '') {
            return 'Error: Please provide a "message" body for the broadcast.';
        }

        if (! $this->isAuthorizedCourse($courseId)) {
            return "Error: Unauthorized. You cannot broadcast to course ID #{$courseId}.";
        }

        $course = Course::find($courseId);
        if (! $course) {
            return "Error: Course #{$courseId} does not exist.";
        }

        $students = User::query()
            ->whereHas('enrollments', fn ($q) => $q->where('course_id', $courseId))
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->distinct()
            ->get();

        $broadcast = Broadcast::query()->create([
            'course_id' => $course->id,
            'user_id' => $this->instructor->id,
            'subject' => $subject,
            'body' => $body,
            'sent_at' => now(),
        ]);

        $recipientCount = 0;
        $emailErrors = 0;

        foreach ($students as $student) {
            $recipientCount++;

            // Deliver email if mailer is configured
            try {
                Mail::to($student->email)->send(new CohortBroadcast(
                    $course,
                    $this->instructor,
                    $body,
                    $subject
                ));
            } catch (\Throwable $e) {
                $emailErrors++;
                Log::warning("AI Co-Pilot broadcast email delivery skipped or failed for student #{$student->id}: " . $e->getMessage());
            }

            // In-app Filament database notification
            try {
                FilamentNotification::make()
                    ->title("Announcement from {$this->instructor->name}: {$subject}")
                    ->body(Str::limit($body, 180))
                    ->icon('heroicon-o-megaphone')
                    ->sendToDatabase($student);
            } catch (\Throwable $e) {
                // Non-critical fallback
            }
        }

        return json_encode([
            'success' => true,
            'broadcast_id' => $broadcast->id,
            'course' => $course->title,
            'course_code' => $course->code,
            'subject' => $subject,
            'recipients_notified' => $recipientCount,
            'email_errors' => $emailErrors,
            'sent_at' => now()->toIso8601String(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    protected function isAuthorizedCourse(int $courseId): bool
    {
        if ($this->instructor->isAdmin()) {
            return true;
        }

        return in_array($courseId, $this->allowedCourseIds, true);
    }
}

