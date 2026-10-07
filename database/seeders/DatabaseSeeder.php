<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Tenant;
use App\Support\TenantContext;
use Illuminate\Database\Seeder;

/**
 * Default installation: one tenant, seeded from the content files.
 *
 * `tenant:provision` reuses ContentSeeder for every additional customer, so
 * this only has to describe the first instance.
 */
final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $slug = (string) config('tenancy.fallback', 'dhardi');

        $tenant = Tenant::query()->firstOrCreate(
            ['slug' => $slug],
            [
                'name' => (string) config('portfolio.profile.name'),
                'default_locale' => 'de',
                'brand_name' => (string) config('portfolio.public_identity.display_name'),
                'brand_accent' => '#C8102E',
                'plan' => 'standard',
                'is_active' => true,
            ],
        );

        app(TenantContext::class)->use($tenant);

        // Constructed directly rather than through `$this->call()` so the
        // tenant dependency is explicit and cannot be resolved by the
        // container into an unsaved model.
        (new ContentSeeder($tenant))->run();
    }
}
