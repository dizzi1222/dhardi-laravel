<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Tenant;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Every interface string is resolved from a lang file. If a key is missing in
 * one locale, a section silently disappears for exactly the reader who matters
 * most — the German one. This turns that into a failing test.
 */
final class TranslationCoverageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Keys the React client resolves. Adding a key to the payload without
     * listing it here would hide a gap, so the list is deliberately explicit.
     *
     * @var array<int, string>
     */
    private const CLIENT_KEYS = [
        'ui.skip_to_content',
        'ui.cv',
        'ui.available',
        'ui.language_label',
        'ui.brand',
        'ui.profile.role',
        'ui.profile.born',
        'ui.nav.fit',
        'ui.nav.experience',
        'ui.nav.projects',
        'ui.nav.stack',
        'ui.nav.assistant',
        'ui.nav.contact',
        'ui.status.shipped',
        'ui.status.in_progress',
        'ui.status.archived',
        'ui.cert_status.completed',
        'ui.cert_status.in_progress',
        'ui.metrics.languages',
        'ui.metrics.prs',
        'ui.metrics.sprints',
        'ui.metrics.team',
        'ui.metrics.epics',
        'ui.metrics.backends',
        'ui.metrics.tenancy',
        'ui.metrics.resources',
        'ui.metrics.ci_stages',
        'ui.metrics.tables',
        'ui.metrics.deploy',
        'ui.metrics.migrations',
        'ui.metrics.windows',
        'ui.metrics.packages',
        'ui.metrics.rebuild',
        'ui.metrics.tools',
        'ui.metrics.ssrf',
        'ui.sections.fit.eyebrow',
        'ui.sections.fit.heading',
        'ui.sections.experience.eyebrow',
        'ui.sections.experience.heading',
        'ui.sections.experience.subtitle',
        'ui.sections.experience.highlights',
        'ui.sections.experience.current',
        'ui.sections.experience.months',
        'ui.sections.experience.remote',
        'ui.sections.projects.eyebrow',
        'ui.sections.projects.heading',
        'ui.sections.projects.subtitle',
        'ui.sections.projects.live',
        'ui.sections.projects.source',
        'ui.sections.projects.view',
        'ui.sections.projects.problem',
        'ui.sections.projects.approach',
        'ui.sections.projects.highlights',
        'ui.sections.stack.eyebrow',
        'ui.sections.stack.heading',
        'ui.sections.stack.subtitle',
        'ui.sections.certifications.eyebrow',
        'ui.sections.certifications.heading',
        'ui.sections.certifications.subtitle',
        'ui.sections.contact.eyebrow',
        'ui.sections.contact.heading',
        'ui.sections.contact.subtitle',
        'ui.sections.contact.email_label',
        'ui.sections.contact.cv_label',
        'ui.sections.contact.primary_cta',
        'ui.sections.footer.built_with',
        'ui.sections.footer.source',
        'ui.assistant.eyebrow',
        'ui.assistant.heading',
        'ui.assistant.subtitle',
        'ui.assistant.placeholder',
        'ui.assistant.send',
        'ui.assistant.sending',
        'ui.assistant.clear',
        'ui.assistant.meta_provider',
        'ui.assistant.meta_latency',
        'ui.assistant.meta_cached',
        'ui.assistant.meta_cost',
        'ui.assistant.disclaimer',
        'ui.assistant.empty',
        'ui.telemetry.eyebrow',
        'ui.telemetry.heading',
        'ui.telemetry.subtitle',
        'ui.telemetry.runs',
        'ui.telemetry.since',
        'ui.telemetry.metrics.success',
        'ui.telemetry.metrics.cache_hit',
        'ui.telemetry.metrics.blocked',
        'ui.telemetry.metrics.fallback',
        'ui.telemetry.metrics.p50',
        'ui.telemetry.metrics.p95',
        'ui.telemetry.metrics.max',
        'ui.telemetry.metrics.cost_total',
        'ui.telemetry.evals.heading',
        'ui.telemetry.evals.cases',
        'ui.telemetry.evals.measured',
        'ui.telemetry.evals.pass_rate',
        'ui.telemetry.evals.last_run',
        'ui.project.back',
        'ui.project.other',
        'hero.headline',
        'hero.subheadline',
        'hero.cta',
        'hero.secondary_cta',
        'fit.intro',
        'fit.legend.addressed',
        'fit.legend.partial',
        'meta.title',
        'meta.description',
        'meta.site_short',
        'profile.summary',
        'profile.citizenship_heading',
        'profile.citizenship_body',
        'assistant.system',
        'assistant.stack_line',
        'assistant.intents_fallback',
        'assistant.field.question',
        'assistant.error.blocked',
        'assistant.error.unavailable',
    ];

    /**
     * @return array<int, array{0: string}>
     */
    public static function locales(): array
    {
        return [
            ['de'],
            ['es'],
            ['en'],
        ];
    }

    #[DataProvider('locales')]
    public function test_every_client_key_exists_in_the_locale(string $locale): void
    {
        app()->setLocale($locale);

        $missing = [];

        foreach (self::CLIENT_KEYS as $key) {
            $value = trans($key);

            if (! is_string($value) || trim($value) === '' || $value === $key) {
                $missing[] = $key;
            }
        }

        self::assertSame([], $missing, "Missing translations in [{$locale}]: ".implode(', ', $missing));
    }

    #[DataProvider('locales')]
    public function test_the_locale_declares_the_same_intents_as_german(string $locale): void
    {
        app()->setLocale('de');
        $german = array_column((array) trans('assistant.intents'), 'key');

        app()->setLocale($locale);
        $current = array_column((array) trans('assistant.intents'), 'key');

        self::assertSame($german, $current, "Intent keys differ in [{$locale}].");
    }

    #[DataProvider('locales')]
    public function test_every_intent_has_keywords_and_an_answer(string $locale): void
    {
        app()->setLocale($locale);

        $broken = [];

        foreach ((array) trans('assistant.intents') as $index => $intent) {
            if (blank($intent['answer'] ?? null) || ($intent['keywords'] ?? []) === []) {
                $broken[] = (string) ($intent['key'] ?? $index);
            }
        }

        self::assertSame([], $broken, "Incomplete intents in [{$locale}]: ".implode(', ', $broken));
    }

    public function test_the_german_locale_is_the_default_for_the_instance(): void
    {
        $tenant = Tenant::query()->create([
            'slug' => 'dhardi',
            'name' => 'Diego Härdi',
            'default_locale' => 'de',
        ]);

        app(TenantContext::class)->use($tenant);

        $this->get('/');

        self::assertSame('de', app()->getLocale());
    }
}
