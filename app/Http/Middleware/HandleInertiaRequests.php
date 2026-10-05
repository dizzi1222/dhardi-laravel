<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\TenantContext;
use Illuminate\Http\Request;
use Inertia\Middleware;

/**
 * Bridges Laravel and the React client.
 *
 * Every visitor shares data, so the tenant identity is exposed as read-only
 * display context. Operational counters about the model layer are shared too,
 * which is the point: the numbers are derived from the same table the feature
 * writes to, so they cannot drift from reality.
 */
final class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $tenant = app(TenantContext::class)->tenant();

        return [
            ...parent::share($request),

            'locale' => app()->getLocale(),
            'availableLocales' => (array) config('tenancy.locales'),

            // All interface copy lives in the PHP lang files, so the client
            // receives the active locale's tree rather than keeping a second
            // copy in TypeScript. Two sources of truth is how a translation
            // silently goes stale.
            'translations' => [
                'ui' => (array) trans('ui'),
                'hero' => (array) trans('hero'),
                'fit' => (array) trans('fit'),
                'meta' => (array) trans('meta'),
                'profile' => [
                    'summary' => (string) trans('profile.summary'),
                    'languages' => (array) trans('profile.languages'),
                    'citizenship_heading' => (string) trans('profile.citizenship_heading'),
                    'citizenship_body' => (string) trans('profile.citizenship_body'),
                ],
            ],

            'tenant' => $tenant === null ? null : [
                'slug' => $tenant->slug,
                'name' => $tenant->brand_name ?? $tenant->name,
                'accent' => $tenant->brand_accent,
            ],

            'flash' => [
                'status' => fn () => $request->session()->get('status'),
            ],
        ];
    }
}
