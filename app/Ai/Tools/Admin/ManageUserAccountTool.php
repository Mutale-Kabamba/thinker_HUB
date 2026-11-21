<?php

namespace App\Ai\Tools\Admin;

use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ManageUserAccountTool implements Tool
{
    public function __construct(protected User $admin)
    {
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Search, inspect details, toggle account active status (activate/suspend), or update the role of any user across think.er HUB.';
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'action' => $schema->string()->description('Action to take: "search", "get_details", "toggle_active", or "update_role".')->required(),
            'query' => $schema->string()->description('Search keyword (name, email, username) for "search" action.')->nullable(),
            'user_id' => $schema->integer()->description('Specific User ID for "get_details", "toggle_active", or "update_role".')->nullable(),
            'new_role' => $schema->string()->description('Role to assign when action is "update_role": "student", "instructor", "admin", "parent", or "blogger".')->nullable(),
            'set_active' => $schema->boolean()->description('Boolean true (activate) or false (suspend) for "toggle_active" action.')->nullable(),
        ];
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        if (! $this->admin->isAdmin()) {
            return 'Error: Unauthorized. User management requires administrative privileges.';
        }

        $action = strtolower(trim((string) ($request['action'] ?? 'search')));
        $userId = ! empty($request['user_id']) ? (int) $request['user_id'] : null;

        if ($action === 'search') {
            $query = trim((string) ($request['query'] ?? ''));
            if ($query === '') {
                return 'Error: Please provide a search query (name, email, or username).';
            }

            $users = User::query()
                ->where('name', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%")
                ->orWhere('username', 'like', "%{$query}%")
                ->limit(15)
                ->get(['id', 'name', 'email', 'username', 'role', 'is_active', 'created_at']);

            return json_encode([
                'total_found' => $users->count(),
                'users' => $users->map(fn ($u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'username' => $u->username,
                    'role' => $u->role,
                    'is_active' => (bool) $u->is_active,
                    'joined_at' => $u->created_at?->format('Y-m-d'),
                ])->all(),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        if (! $userId) {
            return 'Error: Please provide a valid "user_id".';
        }

        $targetUser = User::find($userId);
        if (! $targetUser) {
            return "Error: User #{$userId} not found.";
        }

        if ($action === 'get_details') {
            return json_encode([
                'id' => $targetUser->id,
                'name' => $targetUser->name,
                'email' => $targetUser->email,
                'username' => $targetUser->username,
                'role' => $targetUser->role,
                'is_active' => (bool) $targetUser->is_active,
                'phone' => $targetUser->phone,
                'total_enrollments' => $targetUser->enrollments()->count(),
                'total_payments' => $targetUser->payments()->where('status', 'completed')->sum('amount'),
                'created_at' => $targetUser->created_at?->toIso8601String(),
                'last_login_at' => $targetUser->last_login_at?->toIso8601String(),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        if ($action === 'toggle_active') {
            $setActive = isset($request['set_active']) ? (bool) $request['set_active'] : ! $targetUser->is_active;

            // Security guard: Don't let an admin accidentally deactivate themselves
            if ($targetUser->id === $this->admin->id && ! $setActive) {
                return 'Security Error: You cannot deactivate your own active administrator account.';
            }

            $targetUser->update(['is_active' => $setActive]);

            return json_encode([
                'success' => true,
                'user_id' => $targetUser->id,
                'name' => $targetUser->name,
                'is_active' => $setActive,
                'status' => $setActive ? 'Activated' : 'Suspended',
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        if ($action === 'update_role') {
            $newRole = strtolower(trim((string) ($request['new_role'] ?? '')));
            $allowedRoles = ['student', 'instructor', 'admin', 'parent', 'blogger'];

            if (! in_array($newRole, $allowedRoles, true)) {
                return "Error: Invalid role '{$newRole}'. Allowed roles: " . implode(', ', $allowedRoles);
            }

            // Security guard: Don't let the last admin demote themselves
            if ($targetUser->id === $this->admin->id && $newRole !== 'admin') {
                return 'Security Error: You cannot demote your own administrator account.';
            }

            $oldRole = $targetUser->role;
            $targetUser->update(['role' => $newRole]);

            return json_encode([
                'success' => true,
                'user_id' => $targetUser->id,
                'name' => $targetUser->name,
                'previous_role' => $oldRole,
                'new_role' => $newRole,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        return "Error: Unknown action '{$action}'. Supported actions: search, get_details, toggle_active, update_role.";
    }
}

