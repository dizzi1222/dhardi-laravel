<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Tenant;
use App\Support\TenantContext;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

/**
 * The portfolio page is the product. These assert that it renders in every
 * language and that the content is the seeded content rather than something
 * hard-coded in a component.
 *
 * Every request is made with a fresh context on purpose: the tenant is resolved
 * once per request and memoised in the container, so a second visit in the same
 * test process would otherwise reuse the first language and quietly pass for
 * the wrong reason.
 */
final class HomePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    private function actingAsTenant(): self
    {
        app(TenantContext::class)->use(Tenant::query()->where('slug', 'dhardi')->firstOrFail());

        return $this;
    }

    /**
     * Rebinds the tenant to a fresh instance so the next request resolves its
     * language from scratch instead of reusing a memoised one.
     */
    private function resetTenant(): self
    {
        $this->app->forgetInstance(TenantContext::class);
        app(TenantContext::class)->use(Tenant::query()->where('slug', 'dhardi')->firstOrFail());

        return $this;
    }

    public function test_it_renders_the_home_page(): void
    {
        $this->actingAsTenant()
            ->get('/')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Home')
                ->has('profile')
                ->has('hero')
                ->has('fit')
                ->has('experiences')
                ->has('projects')
                ->has('skills')
                ->has('certifications')
                ->has('stackProof')
                ->has('translations')
            );
    }

    public function test_it_renders_in_german_by_default(): void
    {
        $this->actingAsTenant()
            ->get('/')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('locale', 'de')
                ->where('translations.ui.nav.experience', 'Erfahrung')
            );
    }

    public function test_it_switches_to_spanish(): void
    {
        $this->actingAsTenant()
            ->get('/?lang=es')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('locale', 'es')
                ->where('translations.ui.nav.experience', 'Experiencia')
            );
    }

    public function test_it_switches_to_english(): void
    {
        $this->actingAsTenant()
            ->get('/?lang=en')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('locale', 'en')
                ->where('translations.ui.nav.experience', 'Experience')
            );
    }

    public function test_it_falls_back_to_german_for_an_unknown_language(): void
    {
        $this->actingAsTenant()
            ->get('/?lang=xx')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->where('locale', 'de'));
    }

    public function test_it_exposes_the_project_named_in_each_language(): void
    {
        $expected = [
            'de' => 'Diese Website',
            'es' => 'Mi portafolio',
            'en' => 'This website',
        ];

        foreach ($expected as $locale => $name) {
            $this->resetTenant()
                ->withSession(['locale' => null])
                ->get('/?lang='.$locale)
                ->assertOk()
                ->assertInertia(fn (AssertableInertia $page) => $page
                    ->component('Home')
                    ->where('locale', $locale)
                    ->where('projects.0.name', $name)
                );
        }
    }

    public function test_it_renders_a_project_case_study(): void
    {
        $this->actingAsTenant()
            ->get('/projekt/dhardi-laravel')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('ProjectShow')
                ->has('project.problem')
                ->has('project.approach')
                ->has('project.highlights')
                ->has('all')
            );
    }

    public function test_an_unknown_project_returns_404(): void
    {
        $this->actingAsTenant()
            ->get('/projekt/nope-not-real')
            ->assertNotFound();
    }

    public function test_it_renders_hreflang_alternates(): void
    {
        $response = $this->actingAsTenant()->get('/');

        $response->assertOk();

        foreach (['de', 'es', 'en'] as $locale) {
            self::assertStringContainsString('hreflang="'.$locale.'"', $response->getContent());
        }
    }

    public function test_the_health_endpoint_is_reachable(): void
    {
        $this->get('/up')->assertOk();
    }
}
