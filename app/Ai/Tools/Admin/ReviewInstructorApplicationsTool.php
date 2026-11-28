<?php

namespace App\Ai\Tools\Admin;

use App\Models\InstructorApplication;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ReviewInstructorApplicationsTool implements Tool
{
    public function __construct(protected User $admin)
    {
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'List, inspect, approve, or reject candidate instructor applications submitted to think.er HUB. Approving an application activates the instructor role for the applicant.';
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'action' => $schema->string()->description('Action: "list", "inspect", "approve", or "reject". Default is "list".')->nullable(),
            'status' => $schema->string()->description('Filter status when listing: "pending", "approved", "rejected", or "all". Default is "pending".')->nullable(),
            'application_id' => $schema->integer()->description('Specific application ID when inspecting, approving, or rejecting.')->nullable(),
            'admin_notes' => $schema->string()->description('Optional comments or rationale when approving or rejecting.')->nullable(),
        ];
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        if (! $this->admin->isAdmin()) {
            return 'Error: Unauthorized. Reviewing instructor applications requires administrative privileges.';
        }

        $action = strtolower(trim((string) ($request['action'] ?? 'list')));
        $statusFilter = strtolower(trim((string) ($request['status'] ?? 'pending')));
        $appId = ! empty($request['application_id']) ? (int) $request['application_id'] : null;
        $adminNotes = isset($request['admin_notes']) ? trim((string) $request['admin_notes']) : null;

        if ($action === 'list') {
            $query = InstructorApplication::query();
            if ($statusFilter !== 'all') {
                $query->where('status', $statusFilter);
            }

            $applications = $query->orderByDesc('created_at')->limit(15)->get();

            if ($applications->isEmpty()) {
                return "No instructor applications found with status '{$statusFilter}'.";
            }

            $list = $applications->map(fn ($app) => [
                'id' => $app->id,
                'name' => $app->name,
                'email' => $app->email,
                'phone' => $app->phone,
                'proficiency' => $app->proficiency,
                'proposed_course' => $app->proposed_course_name,
                'status' => $app->status,
                'applied_at' => $app->created_at?->format('Y-m-d H:i'),
            ])->all();

            return json_encode([
                'filter' => $statusFilter,
                'total_count' => count($list),
                'applications' => $list,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        if (! $appId) {
            return 'Error: Please specify "application_id".';
        }

        $application = InstructorApplication::find($appId);
        if (! $application) {
            return "Error: Instructor application #{$appId} not found.";
        }

        if ($action === 'inspect') {
            return json_encode([
                'id' => $application->id,
                'name' => $application->name,
                'email' => $application->email,
                'phone' => $application->phone,
                'whatsapp' => $application->whatsapp,
                'bio' => $application->bio,
                'proficiency' => $application->proficiency,
                'experience' => $application->experience,
                'qualifications' => $application->qualifications,
                'linkedin_url' => $application->linkedin_url,
                'portfolio_url' => $application->portfolio_url,
                'proposed_course_name' => $application->proposed_course_name,
                'proposed_course_description' => $application->proposed_course_description,
                'proposed_course_fees' => $application->proposed_course_fees,
                'status' => $application->status,
                'admin_notes' => $application->admin_notes,
                'applied_at' => $application->created_at?->toIso8601String(),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        if ($action === 'approve') {
            $application->update([
                'status' => 'approved',
                'admin_notes' => $adminNotes ?? $application->admin_notes,
                'reviewed_by' => $this->admin->id,
                'reviewed_at' => now(),
            ]);

            // Promote or create instructor account if linked user exists
            $user = $application->user ?? User::where('email', $application->email)->first();
            if ($user) {
                $user->update(['role' => 'instructor', 'is_active' => true]);
            }

            return json_encode([
                'success' => true,
                'message' => "Instructor application #{$appId} for {$application->name} approved successfully.",
                'applicant_name' => $application->name,
                'email' => $application->email,
                'status' => 'approved',
                'user_promoted' => (bool) $user,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        if ($action === 'reject') {
            $application->update([
                'status' => 'rejected',
                'admin_notes' => $adminNotes ?? 'Application does not meet current instructional requirements.',
                'reviewed_by' => $this->admin->id,
                'reviewed_at' => now(),
            ]);

            return json_encode([
                'success' => true,
                'message' => "Instructor application #{$appId} for {$application->name} marked rejected.",
                'status' => 'rejected',
                'admin_notes' => $application->admin_notes,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        return "Error: Unknown action '{$action}'. Supported: list, inspect, approve, reject.";
    }
}

