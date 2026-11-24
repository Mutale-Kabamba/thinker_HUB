<?php

namespace App\Models\Concerns;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Opt-in tenant scoping. Passing null returns all records (preserving global/legacy behavior).
     */
    public function scopeForTenant(Builder $query, ?int $tenantId, bool $includeGlobal = false): Builder
    {
        if ($tenantId === null) {
            return $query;
        }

        if ($includeGlobal) {
            return $query->where(function (Builder $q) use ($tenantId): void {
                $q->where($q->qualifyColumn('tenant_id'), $tenantId)
                    ->orWhereNull($q->qualifyColumn('tenant_id'));
            });
        }

        return $query->where($query->qualifyColumn('tenant_id'), $tenantId);
    }

    /**
     * Automatically scope queries when the acting user is a Program Admin.
     * Full Admins and non-scoped users see all records identically to existing behavior.
     */
    public function scopeVisibleToAdministrator(Builder $query, ?User $user, bool $includeGlobal = false): Builder
    {
        if (! $user || ! $user->isProgramAdmin()) {
            return $query;
        }

        $tenantId = $user->managedTenantId();

        return $tenantId !== null
            ? $this->scopeForTenant($query, $tenantId, $includeGlobal)
            : $query->whereRaw('1 = 0');
    }
}
