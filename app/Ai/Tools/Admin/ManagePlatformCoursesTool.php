<?php

namespace App\Ai\Tools\Admin;

use App\Models\Course;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ManagePlatformCoursesTool implements Tool
{
    public function __construct(protected User $admin)
    {
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'System-wide course catalog governance: list all courses across all instructors, view enrollment and revenue stats per course, search courses, or toggle active/published status.';
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'action' => $schema->string()->description('Action: "list", "search", "get_details", or "toggle_active". Default is "list".')->nullable(),
            'query' => $schema->string()->description('Search keyword for "search" action.')->nullable(),
            'course_id' => $schema->integer()->description('Course ID for "get_details" or "toggle_active".')->nullable(),
            'is_active' => $schema->boolean()->description('Boolean true (activate/publish) or false (deactivate/draft) for "toggle_active".')->nullable(),
            'category' => $schema->string()->description('Optional category filter when listing.')->nullable(),
        ];
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        if (! $this->admin->isAdmin()) {
            return 'Error: Unauthorized. Course governance requires administrative privileges.';
        }

        $action = strtolower(trim((string) ($request['action'] ?? 'list')));
        $courseId = ! empty($request['course_id']) ? (int) $request['course_id'] : null;

        if ($action === 'list' || $action === 'search') {
            $query = Course::query()->with('instructorUser:id,name,email');

            if ($action === 'search') {
                $term = trim((string) ($request['query'] ?? ''));
                if ($term !== '') {
                    $query->where(function ($q) use ($term) {
                        $q->where('title', 'like', "%{$term}%")
                            ->orWhere('code', 'like', "%{$term}%")
                            ->orWhere('category', 'like', "%{$term}%");
                    });
                }
            }

            if (! empty($request['category'])) {
                $query->where('category', trim((string) $request['category']));
            }

            $courses = $query->orderByDesc('id')->limit(20)->get();

            return json_encode([
                'total_listed' => $courses->count(),
                'courses' => $courses->map(fn ($c) => [
                    'id' => $c->id,
                    'title' => $c->title,
                    'code' => $c->code,
                    'category' => $c->category,
                    'instructor' => $c->instructorUser?->name ?? 'Unassigned',
                    'fees' => (float) $c->fees,
                    'is_active' => (bool) $c->is_active,
                    'enrollments_count' => $c->enrollments()->count(),
                ])->all(),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        if (! $courseId) {
            return 'Error: Please provide a "course_id".';
        }

        $course = Course::with(['instructorUser:id,name,email', 'intakes'])->find($courseId);
        if (! $course) {
            return "Error: Course #{$courseId} does not exist.";
        }

        if ($action === 'get_details') {
            $enrollmentsCount = $course->enrollments()->count();
            $activeEnrollments = $course->enrollments()->where('status', 'active')->count();
            $totalRevenue = (float) $course->payments()->where('status', 'completed')->sum('amount');

            return json_encode([
                'id' => $course->id,
                'title' => $course->title,
                'code' => $course->code,
                'category' => $course->category,
                'level' => $course->level,
                'fees' => (float) $course->fees,
                'is_active' => (bool) $course->is_active,
                'for_kids' => (bool) $course->is_for_kids,
                'instructor' => [
                    'id' => $course->instructorUser?->id,
                    'name' => $course->instructorUser?->name,
                    'email' => $course->instructorUser?->email,
                ],
                'stats' => [
                    'total_enrollments' => $enrollmentsCount,
                    'active_enrollments' => $activeEnrollments,
                    'total_revenue_generated' => round($totalRevenue, 2),
                    'total_sessions_scheduled' => $course->sessions()->count(),
                    'total_quizzes' => $course->quizzes()->count(),
                    'total_assignments' => $course->assignments()->count(),
                ],
                'upcoming_intakes' => $course->intakes->map(fn ($i) => [
                    'id' => $i->id,
                    'name' => $i->name,
                    'start_date' => $i->start_date?->format('Y-m-d'),
                    'is_active' => (bool) $i->is_active,
                ])->all(),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        if ($action === 'toggle_active') {
            $newActive = isset($request['is_active']) ? (bool) $request['is_active'] : ! $course->is_active;
            $course->update(['is_active' => $newActive]);

            return json_encode([
                'success' => true,
                'course_id' => $course->id,
                'title' => $course->title,
                'is_active' => $newActive,
                'status' => $newActive ? 'Published & Active' : 'Unpublished / Draft',
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        return "Error: Unknown action '{$action}'. Supported: list, search, get_details, toggle_active.";
    }
}

