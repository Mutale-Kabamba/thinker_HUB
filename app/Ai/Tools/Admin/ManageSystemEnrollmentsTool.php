<?php

namespace App\Ai\Tools\Admin;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ManageSystemEnrollmentsTool implements Tool
{
    public function __construct(protected User $admin)
    {
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Enroll a student directly into any course (with optional scholarship/grant notes), search system enrollments, or update enrollment status (active, completed, dropped).';
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'action' => $schema->string()->description('Action: "enroll_student", "search_enrollments", or "update_status".')->required(),
            'user_id' => $schema->integer()->description('Student user ID when enrolling or filtering.')->nullable(),
            'course_id' => $schema->integer()->description('Course ID when enrolling or filtering.')->nullable(),
            'enrollment_id' => $schema->integer()->description('Specific enrollment ID for "update_status".')->nullable(),
            'new_status' => $schema->string()->description('New status ("active", "completed", "dropped") for "update_status".')->nullable(),
            'notes' => $schema->string()->description('Administrative notes or reason when enrolling or updating.')->nullable(),
        ];
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        if (! $this->admin->isAdmin()) {
            return 'Error: Unauthorized. Enrollment administration requires administrative privileges.';
        }

        $action = strtolower(trim((string) ($request['action'] ?? 'search_enrollments')));

        if ($action === 'enroll_student') {
            $userId = ! empty($request['user_id']) ? (int) $request['user_id'] : null;
            $courseId = ! empty($request['course_id']) ? (int) $request['course_id'] : null;
            $notes = isset($request['notes']) ? trim((string) $request['notes']) : null;

            if (! $userId || ! $courseId) {
                return 'Error: Both "user_id" and "course_id" are required to enroll a student.';
            }

            $user = User::find($userId);
            if (! $user) {
                return "Error: Student user #{$userId} not found.";
            }

            $course = Course::find($courseId);
            if (! $course) {
                return "Error: Course #{$courseId} not found.";
            }

            $existing = Enrollment::where('user_id', $userId)->where('course_id', $courseId)->first();
            if ($existing) {
                return json_encode([
                    'success' => false,
                    'message' => "User {$user->name} is already enrolled in {$course->title} with status '{$existing->status}'.",
                    'enrollment_id' => $existing->id,
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            }

            $enrollment = Enrollment::create([
                'user_id' => $userId,
                'course_id' => $courseId,
                'status' => 'active',
                'enrolled_at' => now(),
            ]);

            return json_encode([
                'success' => true,
                'message' => "Student {$user->name} successfully enrolled into {$course->title}.",
                'enrollment' => [
                    'id' => $enrollment->id,
                    'student' => $user->name,
                    'course' => $course->title,
                    'status' => 'active',
                    'enrolled_at' => now()->toIso8601String(),
                    'admin_notes' => $notes,
                ],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        if ($action === 'search_enrollments') {
            $query = Enrollment::query()->with(['user:id,name,email', 'course:id,title,code']);

            if (! empty($request['user_id'])) {
                $query->where('user_id', (int) $request['user_id']);
            }

            if (! empty($request['course_id'])) {
                $query->where('course_id', (int) $request['course_id']);
            }

            $enrollments = $query->orderByDesc('id')->limit(20)->get();

            return json_encode([
                'total_found' => $enrollments->count(),
                'enrollments' => $enrollments->map(fn ($e) => [
                    'id' => $e->id,
                    'student_id' => $e->user_id,
                    'student_name' => $e->user?->name,
                    'student_email' => $e->user?->email,
                    'course_id' => $e->course_id,
                    'course_title' => $e->course?->title,
                    'status' => $e->status,
                    'created_at' => $e->created_at?->format('Y-m-d'),
                ])->all(),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        if ($action === 'update_status') {
            $enrollmentId = ! empty($request['enrollment_id']) ? (int) $request['enrollment_id'] : null;
            $newStatus = strtolower(trim((string) ($request['new_status'] ?? '')));

            if (! $enrollmentId || $newStatus === '') {
                return 'Error: Both "enrollment_id" and "new_status" are required.';
            }

            $enrollment = Enrollment::with(['user:id,name', 'course:id,title'])->find($enrollmentId);
            if (! $enrollment) {
                return "Error: Enrollment #{$enrollmentId} not found.";
            }

            $enrollment->update(['status' => $newStatus]);

            return json_encode([
                'success' => true,
                'enrollment_id' => $enrollment->id,
                'student' => $enrollment->user?->name,
                'course' => $enrollment->course?->title,
                'status' => $newStatus,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        return "Error: Unknown action '{$action}'. Supported: enroll_student, search_enrollments, update_status.";
    }
}

