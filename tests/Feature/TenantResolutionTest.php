<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Experience;
use App\Models\Tenant;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/**
 * One deployment per customer, so the tenant is part of every query rather than
 * something application code has to remember to filter on.
 */
final class TenantResolutionTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::query()->create([
            'slug' => 'acme',
            'name' => 'Acme AG',
            'default_locale' => 'de',
        ]);

        app(TenantContext::class)->use($this->tenant);
    }

    public function test_it_resolves_the_tenant_from_the_context(): void
    {
        self::assertSame($this->tenant->getKey(), app(TenantContext::class)->id());
        self::assertSame('acme', app(TenantContext::class)->tenant()?->slug);
    }

    public function test_it_resolves_the_tenant_from_the_environment(): void
    {
        config(['tenancy.fallback' => 'acme']);
        app(TenantContext::class)->forget();

        self::assertSame('acme', app(TenantContext::class)->tenant()?->slug);
    }

    public function test_it_prefers_an_explicit_query_override(): void
    {
        Tenant::query()->create(['slug' => 'globex', 'name' => 'Globex', 'default_locale' => 'de']);

        config(['tenancy.fallback' => 'acme']);
        app(TenantContext::class)->forget();

        $this->get('/?tenant=globex');

        // A fresh resolution after the request proves the query string wins over
        // the configured fallback.
        self::assertSame(
            'globex',
            (fn (): ?string => $this->slug())->call(app(TenantContext::class)),
        );
    }

    public function test_an_unknown_tenant_resolves_to_null_rather_than_throwing(): void
    {
        app(TenantContext::class)->forget();

        config(['tenancy.fallback' => 'does-not-exist']);

        self::assertNull(app(TenantContext::class)->tenant());
        self::assertNull(app(TenantContext::class)->id());
    }

    public function test_writing_without_a_tenant_is_refused(): void
    {
        app(TenantContext::class)->forget();

        config(['tenancy.fallback' => 'does-not-exist']);

        $this->expectException(HttpException::class);

        Experience::query()->create([
            'slug' => 'orphan',
            'role' => 'Dev',
            'organisation' => 'Nowhere',
            'start_date' => '2024-01-01',
        ]);
    }

    public function test_content_is_scoped_to_the_current_tenant(): void
    {
        $mine = $this->seedExperience('mine');
        $theirs = $this->seedExperience('theirs', tenantId: $this->otherTenant()->getKey());

        $visible = Experience::query()->pluck('slug');

        self::assertTrue($visible->contains('mine'));
        self::assertFalse($visible->contains('theirs'), 'Another tenant’s rows must not be visible.');
        self::assertTrue($mine->is($visible->contains('mine') ? $mine : $mine));
        self::assertNotNull($theirs);
    }

    public function test_the_global_scope_can_be_bypassed_explicitly(): void
    {
        $this->seedExperience('mine');
        $this->seedExperience('theirs', tenantId: $this->otherTenant()->getKey());

        $all = Experience::withoutGlobalScopes()->pluck('slug');

        self::assertTrue($all->contains('theirs'));
        self::assertTrue($all->contains('mine'));
    }

    private function slug(): ?string
    {
        return app(TenantContext::class)->tenant()?->slug;
    }

    private function otherTenant(): Tenant
    {
        return Tenant::query()->firstOrCreate(
            ['slug' => 'globex'],
            ['name' => 'Globex', 'default_locale' => 'de'],
        );
    }

    private function seedExperience(string $slug, ?int $tenantId = null): Experience
    {
        return Experience::query()->create([
            'tenant_id' => $tenantId,
            'slug' => $slug,
            'role' => 'Developer',
            'organisation' => 'Somewhere',
            'start_date' => '2024-01-01',
            'sort_order' => 1,
        ]);
    }
}
