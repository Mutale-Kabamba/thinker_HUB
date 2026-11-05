<?php

namespace App\Ai\Tools\Admin;

use App\Models\PromoCode;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ManagePromoCodesTool implements Tool
{
    public function __construct(protected User $admin)
    {
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Manage platform promo codes: list existing codes, check usage metrics, toggle active status, or generate a new promotional discount code.';
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'action' => $schema->string()->description('Action: "list", "create", or "toggle_active". Default is "list".')->nullable(),
            'code' => $schema->string()->description('Promo code text (e.g. "TECH50", "SCHOLAR2026"). Required for "create" or "toggle_active".')->nullable(),
            'discount_type' => $schema->string()->description('Discount type for create: "percentage" or "fixed". Default is "percentage".')->nullable(),
            'discount_value' => $schema->number()->description('Value of the discount (e.g. 25 for 25% or 500 for ZMW 500 fixed). Required for create.')->nullable(),
            'max_uses' => $schema->integer()->description('Optional total usage limit for create.')->nullable(),
            'expires_at' => $schema->string()->description('Optional expiry date string (Y-m-d) for create.')->nullable(),
            'description' => $schema->string()->description('Optional promo campaign description.')->nullable(),
        ];
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        if (! $this->admin->isAdmin()) {
            return 'Error: Unauthorized. Promo code administration requires administrative privileges.';
        }

        $action = strtolower(trim((string) ($request['action'] ?? 'list')));

        if ($action === 'list') {
            $codes = PromoCode::orderByDesc('id')->limit(20)->get();

            if ($codes->isEmpty()) {
                return 'No promo codes currently created in the system.';
            }

            return json_encode([
                'total_codes' => $codes->count(),
                'promo_codes' => $codes->map(fn ($p) => [
                    'id' => $p->id,
                    'code' => $p->code,
                    'description' => $p->description,
                    'discount_type' => $p->discount_type,
                    'discount_value' => (float) $p->discount_value,
                    'is_active' => (bool) $p->is_active,
                    'uses_count' => (int) $p->uses_count,
                    'max_uses' => $p->max_uses,
                    'expires_at' => $p->expires_at?->format('Y-m-d'),
                ])->all(),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        $codeText = strtoupper(trim((string) ($request['code'] ?? '')));
        if ($codeText === '') {
            return 'Error: Please provide a "code".';
        }

        if ($action === 'toggle_active') {
            $promo = PromoCode::where('code', $codeText)->first();
            if (! $promo) {
                return "Error: Promo code '{$codeText}' does not exist.";
            }

            $promo->update(['is_active' => ! $promo->is_active]);

            return json_encode([
                'success' => true,
                'code' => $promo->code,
                'is_active' => (bool) $promo->is_active,
                'status' => $promo->is_active ? 'Activated' : 'Disabled',
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        if ($action === 'create') {
            $value = ! empty($request['discount_value']) ? (float) $request['discount_value'] : null;
            if ($value === null || $value <= 0) {
                return 'Error: Please specify a positive "discount_value".';
            }

            $type = strtolower(trim((string) ($request['discount_type'] ?? 'percentage')));
            if (! in_array($type, ['percentage', 'fixed'], true)) {
                $type = 'percentage';
            }

            $existing = PromoCode::where('code', $codeText)->first();
            if ($existing) {
                return "Error: Promo code '{$codeText}' already exists.";
            }

            $promo = PromoCode::create([
                'code' => $codeText,
                'description' => $request['description'] ?? "Created by Admin AI Co-Pilot for {$this->admin->name}",
                'discount_type' => $type,
                'discount_value' => $value,
                'max_uses' => ! empty($request['max_uses']) ? (int) $request['max_uses'] : null,
                'expires_at' => ! empty($request['expires_at']) ? trim((string) $request['expires_at']) : null,
                'is_active' => true,
            ]);

            return json_encode([
                'success' => true,
                'message' => "Promo code '{$promo->code}' created successfully.",
                'promo' => [
                    'id' => $promo->id,
                    'code' => $promo->code,
                    'type' => $promo->discount_type,
                    'value' => (float) $promo->discount_value,
                    'max_uses' => $promo->max_uses,
                    'expires_at' => $promo->expires_at?->format('Y-m-d'),
                    'is_active' => true,
                ],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        return "Error: Unknown action '{$action}'. Supported: list, create, toggle_active.";
    }
}

