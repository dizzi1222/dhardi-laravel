<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;

/**
 * Resolves and memoises the tenant of the running instance.
 *
 * Bound as a singleton for the lifetime of the request, so the lookup happens
 * once even when several models resolve their scope.
 *
 * Deliberately not caching the Eloquent model itself: a model round-tripped
 * through a cache store comes back as `__PHP_Incomplete_Class` under some
 * serializers, and a cached model is stale the moment the row changes. Only the
 * scalar id is cached, and the model is always hydrated fresh.
 */
final class TenantContext
{
    private const CACHE_PREFIX = 'tenant-id:';

    private ?Tenant $tenant = null;

    public function id(): ?int
    {
        return $this->tenant()?->getKey();
    }

    public function tenant(): ?Tenant
    {
        if ($this->tenant !== null) {
            return $this->tenant;
        }

        $slug = $this->slug();

        $id = Cache::rememberForever(
            self::CACHE_PREFIX.$slug,
            fn (): mixed => Tenant::withoutGlobalScopes()->where('slug', $slug)->value('id'),
        );

        if ($id === null) {
            return null;
        }

        return $this->tenant = Tenant::withoutGlobalScopes()->find($id);
    }

    /**
     * Force the context to a specific tenant.
     *
     * Needed by the provisioning command and the seeders, which create a tenant
     * and then write its content in the same process — there is no session and
     * no environment variable to read it from.
     */
    public function use(Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    /**
     * Forget the memoised tenant and the cached id.
     */
    public function forget(): void
    {
        $this->tenant = null;
        Cache::forget(self::CACHE_PREFIX.$this->slug());
    }

    /**
     * Precedence: an explicit choice made by this instance, then the value baked
     * into the deployment, then the configured fallback.
     *
     * The fallback is read from config on every call rather than injected in the
     * constructor, so that a test or a console command can point the context at
     * a different customer without rebuilding the container.
     */
    private function slug(): string
    {
        if (app()->bound('request')) {
            $request = request();

            $fromSession = $request->hasSession() ? $request->session()->get('tenant_slug') : null;

            $explicit = $fromSession ?? $request->query('tenant');

            if (is_string($explicit) && $explicit !== '') {
                return $explicit;
            }
        }

        return (string) config('tenancy.fallback', 'dhardi');
    }
}
