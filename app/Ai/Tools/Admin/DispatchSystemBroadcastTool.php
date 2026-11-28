<?php

namespace App\Ai\Tools\Admin;

use App\Models\Broadcast;
use App\Models\Course;
use App\Models\User;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class DispatchSystemBroadcastTool implements Tool
{
    public function __construct(protected User $admin)
    {
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Dispatch a high-priority system-level broadcast or announcement to targeted user roles ("all", "students", "instructors", "parents", or specific course enrolled students).';
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'target' => $schema->string()->description('Target audience: "all", "students", "instructors", "parents", or "course". Default is "all".')->required(),
            'subject' => $schema->string()->description('Announcement headline or title.')->required(),
            'message' => $schema->string()->description('Full body message of the announcement.')->required(),
            'course_id' => $schema->integer()->description('Course ID if target is "course".')->nullable(),
        ];
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        if (! $this->admin->isAdmin()) {
            return 'Error: Unauthorized. Dispatching system broadcasts requires administrative privileges.';
        }

        $target = strtolower(trim((string) ($request['target'] ?? 'all')));
        $subject = trim((string) ($request['subject'] ?? ''));
        $body = trim((string) ($request['message'] ?? ''));
        $courseId = ! empty($request['course_id']) ? (int) $request['course_id'] : null;

        if ($subject === '' || $body === '') {
            return 'Error: Both "subject" and "message" are required.';
        }

        $recipientsQuery = User::where('is_active', true);

        if ($target === 'students') {
            $recipientsQuery->where('role', 'student');
        } elseif ($target === 'instructors') {
            $recipientsQuery->where('role', 'instructor');
        } elseif ($target === 'parents') {
            $recipientsQuery->where('role', 'parent');
        } elseif ($target === 'course' && $courseId) {
            $recipientsQuery->whereHas('enrollments', fn ($q) => $q->where('course_id', $courseId));
        }

        $recipients = $recipientsQuery->get(['id', 'name', 'email']);

        // Record in broadcasts table
        $broadcast = Broadcast::create([
            'course_id' => $courseId,
            'user_id' => $this->admin->id,
            'subject' => "[System Announcement] {$subject}",
            'body' => $body,
            'sent_at' => now(),
        ]);

        $notifiedCount = 0;
        foreach ($recipients as $recipient) {
            $notifiedCount++;
            try {
                FilamentNotification::make()
                    ->title("System Announcement: {$subject}")
                    ->body(Str::limit($body, 200))
                    ->icon('heroicon-o-megaphone')
                    ->sendToDatabase($recipient);
            } catch (\Throwable $e) {
                // Continue delivering
            }
        }

        return json_encode([
            'success' => true,
            'broadcast_id' => $broadcast->id,
            'subject' => $subject,
            'target_audience' => $target,
            'course_id' => $courseId,
            'total_recipients_notified' => $notifiedCount,
            'dispatched_by' => $this->admin->name,
            'dispatched_at' => now()->toIso8601String(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}

