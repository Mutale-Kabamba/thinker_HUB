<?php

namespace App\Ai\Tools\Admin;

use App\Models\ClaimRequest;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\InstructorApplication;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SystemOverviewTool implements Tool
{
    public function __construct(protected ?User $admin = null)
    {
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Get an executive snapshot of think.er HUB system vitals: total users by role, active and draft courses, enrollments, total revenue, pending instructor applications, pending rewards claims, and items requiring immediate administrative action.';
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'scope' => $schema->string()->description('Optional focus: "all", "users", "academics", "finances", or "pending_actions". Default is "all".')->nullable(),
        ];
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        if (! $this->admin->isAdmin()) {
            return 'Error: Unauthorized. System overview requires administrative privileges.';
        }

        $scope = strtolower(trim((string) ($request['scope'] ?? 'all')));

        $data = [];

        if (in_array($scope, ['all', 'users'], true)) {
            $data['users'] = [
                'total_users' => User::count(),
                'active_users' => User::where('is_active', true)->count(),
                'students' => User::where('role', 'student')->count(),
                'instructors' => User::where('role', 'instructor')->count(),
                'parents' => User::where('role', 'parent')->count(),
                'bloggers_contributors' => User::where('role', 'blogger')->count(),
                'administrators' => User::where('role', 'admin')->count(),
                'new_users_last_30_days' => User::where('created_at', '>=', now()->subDays(30))->count(),
            ];
        }

        if (in_array($scope, ['all', 'academics'], true)) {
            $data['academics'] = [
                'total_courses' => Course::count(),
                'active_courses' => Course::where('is_active', true)->count(),
                'draft_or_archived_courses' => Course::where('is_active', false)->count(),
                'total_enrollments' => Enrollment::count(),
                'active_enrollments' => Enrollment::where('status', 'active')->count(),
                'completed_enrollments' => Enrollment::where('status', 'completed')->count(),
            ];
        }

        if (in_array($scope, ['all', 'finances'], true)) {
            $completedPayments = Payment::where('status', Payment::STATUS_COMPLETED);
            $totalRevenue = (float) $completedPayments->sum('amount');
            $revenueLast30Days = (float) Payment::where('status', Payment::STATUS_COMPLETED)
                ->where('paid_at', '>=', now()->subDays(30))
                ->sum('amount');

            $data['finances'] = [
                'currency' => 'ZMW',
                'all_time_revenue' => round($totalRevenue, 2),
                'revenue_last_30_days' => round($revenueLast30Days, 2),
                'completed_transactions' => Payment::where('status', Payment::STATUS_COMPLETED)->count(),
                'pending_transactions' => Payment::where('status', Payment::STATUS_PENDING)->count(),
                'failed_transactions' => Payment::where('status', Payment::STATUS_FAILED)->count(),
            ];
        }

        if (in_array($scope, ['all', 'pending_actions'], true)) {
            $data['pending_actions'] = [
                'pending_instructor_applications' => InstructorApplication::where('status', 'pending')->count(),
                'pending_claim_requests' => ClaimRequest::where('status', ClaimRequest::STATUS_PENDING)->count(),
                'unresolved_failed_payments_24h' => Payment::where('status', Payment::STATUS_FAILED)
                    ->where('created_at', '>=', now()->subHours(24))
                    ->count(),
            ];
        }

        return json_encode([
            'system' => 'think.er HUB Core Operations',
            'timestamp' => now()->toIso8601String(),
            'scope' => $scope,
            'vitals' => $data,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}

