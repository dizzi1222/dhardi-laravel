<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Tenant;
use App\Support\TenantContext;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The CV notice.
 *
 * The CV lists .NET, C#, SQL Server and Angular, none of which appear in this
 * repository. The notice exists so a reader holding both documents compares them
 * honestly rather than discovering the difference later, and for that it has to
 * be in the page payload in every language — a notice that silently disappears in
 * one locale is worse than none.
 */
final class CvNoticeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    /**
     * Rebinds the tenant to a fresh instance.
     *
     * Same reason as in HomePageTest: the tenant context is a singleton, so a
     * second request in the same process would otherwise reuse the language
     * memoised by the first one and the locale assertion would pass for the
     * wrong reason.
     */
    private function asTenant(): self
    {
        $this->app->forgetInstance(TenantContext::class);
        app(TenantContext::class)->use(Tenant::query()->where('slug', 'dhardi')->firstOrFail());

        return $this;
    }

    /**
     * @return array<int, array{0: string}>
     */
    public static function locales(): array
    {
        return [['de'], ['es'], ['en']];
    }

    #[DataProvider('locales')]
    public function test_the_notice_is_present_in_every_locale(string $locale): void
    {
        $this->asTenant()
            ->get('/?lang='.$locale)
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Home')
                ->where('translations.cv.notice.title', fn (mixed $value): bool => is_string($value) && $value !== '')
                ->where('translations.cv.notice.body', fn (mixed $value): bool => is_string($value) && mb_strlen((string) $value) > 40)
            );
    }

    public function test_the_notice_names_the_unverified_technologies(): void
    {
        $body = (string) trans('cv_notice.notice.body');

        foreach (['.NET', 'C#', 'SQL Server', 'Angular'] as $technology) {
            self::assertStringContainsString(
                $technology,
                $body,
                "The notice should name {$technology} so the discrepancy is concrete.",
            );
        }
    }

    public function test_the_notice_also_points_at_the_verifiable_stack(): void
    {
        $body = (string) trans('cv_notice.notice.body');

        self::assertStringContainsString('Laravel', $body);
        self::assertStringContainsString('React', $body);
    }

    public function test_each_locale_has_a_distinct_translation_of_the_notice(): void
    {
        $bodies = [];

        foreach (['de', 'es', 'en'] as $locale) {
            app()->setLocale($locale);
            $bodies[$locale] = (string) trans('cv_notice.notice.body');
        }

        self::assertCount(3, array_unique($bodies), 'Each locale needs its own wording.');
    }
}
