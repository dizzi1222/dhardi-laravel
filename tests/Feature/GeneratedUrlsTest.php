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
 * A page that returns 200 can still be broken in a browser.
 *
 * The failure this guards against is the expensive kind: the document loads,
 * the status is 200, and the response is unusable because every asset URL was
 * emitted over `http://` on a page served over `https://`. The browser blocks
 * mixed active content, the page renders unstyled and without JavaScript, and
 * nothing in the HTTP status codes says so.
 *
 * So the assertion is on the scheme of the URLs in the markup, not on the
 * response status.
 */
final class GeneratedUrlsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    private function getHomeMarkup(): string
    {
        app(TenantContext::class)
            ->use(Tenant::query()->where('slug', 'dhardi')->firstOrFail());

        // Reproduces the platform's shape: the browser connects over TLS,
        // the edge forwards plain HTTP, and the scheme only survives in
        // X-Forwarded-Proto. Asking for an https URL directly would hide the
        // bug, because the framework would never have to consult the header.
        $response = $this->withHeaders([
            'X-Forwarded-Proto' => 'https',
        ])->get('http://localhost/');

        $response->assertOk();

        return $response->getContent();
    }

    public function test_asset_urls_are_emitted_with_the_forwarded_scheme(): void
    {
        $markup = $this->getHomeMarkup();

        preg_match_all('/(?:href|src)="([^"]+)"/', $markup, $matches);

        $assets = array_filter(
            $matches[1],
            static fn (string $url): bool => str_contains($url, '/build/') || str_contains($url, 'favicon'),
        );

        self::assertNotEmpty($assets, 'The page is expected to reference built assets.');

        foreach ($assets as $url) {
            self::assertStringStartsNotWith(
                'http://',
                $url,
                "Asset URL generated over http on an https page: {$url}",
            );
        }
    }

    public function test_generated_urls_are_absolute_and_https(): void
    {
        $markup = $this->getHomeMarkup();

        preg_match_all('/href="(https?:\/\/[^"]+)"/', $markup, $matches);

        foreach ($matches[1] as $url) {
            self::assertStringStartsWith(
                'https://',
                $url,
                "Generated URL is not https: {$url}",
            );
        }
    }

    public function test_the_canonical_and_hreflang_alternates_are_https(): void
    {
        $markup = $this->getHomeMarkup();

        self::assertMatchesRegularExpression(
            '/<link rel="canonical" href="https:\/\/[^"]+"/',
            $markup,
            'The canonical URL must be https, otherwise it points search engines at an insecure origin.',
        );

        foreach (['de', 'es', 'en'] as $locale) {
            self::assertMatchesRegularExpression(
                '/<link rel="alternate" hreflang="'.$locale.'" href="https:\/\//',
                $markup,
                "The {$locale} alternate is not https.",
            );
        }
    }

    public function test_the_inertia_payload_is_served_on_the_page(): void
    {
        app(TenantContext::class)
            ->use(Tenant::query()->where('slug', 'dhardi')->firstOrFail());

        $this->withHeaders(['X-Forwarded-Proto' => 'https'])
            ->get('http://localhost/')
            ->assertInertia(fn (AssertableInertia $page) => $page->component('Home'));
    }
}
