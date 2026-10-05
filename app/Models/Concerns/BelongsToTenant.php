<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Tenant;
use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Restricts a model to the tenant of the current instance.
 *
 * Every deployment belongs to exactly one customer, so the constraint is
 * enforced at query level rather than left to application code that can
 * forget it. Tests and the provisioning command opt out explicitly.
 *
 * @property-read Tenant $tenant
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $query): void {
            $tenant = app(TenantContext::class)->id();

            if ($tenant !== null) {
                $query->where($query->getModel()->qualifyColumn('tenant_id'), $tenant);
            }
        });

        static::creating(function ($model): void {
            if ($model->getAttribute('tenant_id') === null) {
                $tenant = app(TenantContext::class)->id();

                abort_if($tenant === null, 500, 'No tenant resolved for the current instance.');
                $model->setAttribute('tenant_id', $tenant);
            }
        });
    }

    /**
     * @return BelongsTo<Tenant, static>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
