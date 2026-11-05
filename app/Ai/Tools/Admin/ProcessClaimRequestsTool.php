<?php

namespace App\Ai\Tools\Admin;

use App\Models\ClaimRequest;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ProcessClaimRequestsTool implements Tool
{
    public function __construct(protected User $admin)
    {
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Review and fulfill student Thinker Coins reward claim requests (tech swag, vouchers, hardware perks), approve fulfillment, or reject with feedback.';
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'action' => $schema->string()->description('Action: "list", "fulfill", or "reject". Default is "list".')->nullable(),
            'status' => $schema->string()->description('Filter status when listing: "pending", "fulfilled", "rejected", or "all". Default is "pending".')->nullable(),
            'claim_id' => $schema->integer()->description('Specific claim request ID for "fulfill" or "reject".')->nullable(),
            'admin_remarks' => $schema->string()->description('Remarks or tracking details when fulfilling or rejecting.')->nullable(),
        ];
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        if (! $this->admin->isAdmin()) {
            return 'Error: Unauthorized. Processing claim requests requires administrative privileges.';
        }

        $action = strtolower(trim((string) ($request['action'] ?? 'list')));

        if ($action === 'list') {
            $status = strtolower(trim((string) ($request['status'] ?? 'pending')));
            $query = ClaimRequest::with(['user:id,name,email', 'claimItem:id,name,cost_coins']);

            if ($status !== 'all') {
                $query->where('status', $status);
            }

            $claims = $query->orderByDesc('id')->limit(20)->get();

            if ($claims->isEmpty()) {
                return "No reward claims found matching status '{$status}'.";
            }

            return json_encode([
                'filter' => $status,
                'total_claims' => $claims->count(),
                'claims' => $claims->map(fn ($c) => [
                    'id' => $c->id,
                    'student' => $c->user?->name,
                    'email' => $c->user?->email,
                    'item' => $c->claimItem?->name ?? 'Reward Item',
                    'coins_spent' => (int) $c->coins_spent,
                    'phone' => $c->phone_number,
                    'delivery_notes' => $c->delivery_notes,
                    'status' => $c->status,
                    'requested_at' => $c->created_at?->format('Y-m-d H:i'),
                ])->all(),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        $claimId = ! empty($request['claim_id']) ? (int) $request['claim_id'] : null;
        if (! $claimId) {
            return 'Error: Please specify "claim_id".';
        }

        $claim = ClaimRequest::with(['user', 'claimItem'])->find($claimId);
        if (! $claim) {
            return "Error: Claim request #{$claimId} not found.";
        }

        $remarks = isset($request['admin_remarks']) ? trim((string) $request['admin_remarks']) : null;

        if ($action === 'fulfill') {
            $claim->update([
                'status' => ClaimRequest::STATUS_FULFILLED,
                'admin_remarks' => $remarks ?? 'Reward fulfilled and dispatched by Administrator.',
                'fulfilled_at' => now(),
            ]);

            return json_encode([
                'success' => true,
                'claim_id' => $claim->id,
                'student' => $claim->user?->name,
                'item' => $claim->claimItem?->name,
                'status' => ClaimRequest::STATUS_FULFILLED,
                'fulfilled_at' => now()->toIso8601String(),
                'admin_remarks' => $claim->admin_remarks,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        if ($action === 'reject') {
            $claim->update([
                'status' => ClaimRequest::STATUS_REJECTED,
                'admin_remarks' => $remarks ?? 'Claim rejected after administrative review.',
            ]);

            return json_encode([
                'success' => true,
                'claim_id' => $claim->id,
                'student' => $claim->user?->name,
                'status' => ClaimRequest::STATUS_REJECTED,
                'admin_remarks' => $claim->admin_remarks,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        return "Error: Unknown action '{$action}'. Supported: list, fulfill, reject.";
    }
}

