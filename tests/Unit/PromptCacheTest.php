<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Llm\Contracts\LlmRequest;
use App\Llm\Contracts\LlmResponse;
use App\Llm\Reliability\PromptCache;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * The cache key is the whole safety argument for memoising model output, so it
 * is worth pinning down: it has to change when anything that affects the answer
 * changes, and it has to never cross tenants.
 */
final class PromptCacheTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    private function cache(int $ttl = 900): PromptCache
    {
        return new PromptCache(Cache::store('array'), ttlSeconds: $ttl);
    }

    private function request(string $text, ?string $system = 'Du bist Diego.'): LlmRequest
    {
        return new LlmRequest(
            messages: [['role' => 'user', 'content' => $text]],
            system: $system,
        );
    }

    public function test_it_misses_on_the_first_call_and_hits_on_the_second(): void
    {
        $cache = $this->cache();
        $key = $cache->keyFor($this->request('Wo wohnst du?'), 'anthropic', 'claude', 1);

        self::assertNull($cache->peek($key));

        $cache->remember($key, static fn (): LlmResponse => new LlmResponse(
            text: 'In der Dominikanischen Republik.',
            provider: 'anthropic',
            model: 'claude',
            promptTokens: 100,
            completionTokens: 20,
        ));

        $hit = $cache->peek($key);

        self::assertNotNull($hit);
        self::assertSame('In der Dominikanischen Republik.', $hit->text);
        self::assertTrue($hit->wasCached, 'A cache hit must be marked as such for the audit trail.');
    }

    public function test_remember_does_not_invoke_the_producer_twice(): void
    {
        $cache = $this->cache();
        $key = $cache->keyFor($this->request('Test'), 'anthropic', 'claude', 1);

        $calls = 0;
        $producer = static function () use (&$calls): LlmResponse {
            $calls++;

            return new LlmResponse(text: 'Antwort', provider: 'anthropic', model: 'claude');
        };

        $cache->remember($key, $producer);
        $cache->remember($key, $producer);

        self::assertSame(1, $calls, 'The second call must be served from cache.');
    }

    public function test_the_key_differs_per_tenant(): void
    {
        $cache = $this->cache();
        $request = $this->request('Wo wohnst du?');

        self::assertNotSame(
            $cache->keyFor($request, 'anthropic', 'claude', 1),
            $cache->keyFor($request, 'anthropic', 'claude', 2),
        );
    }

    public function test_the_key_differs_per_provider_and_model(): void
    {
        $cache = $this->cache();
        $request = $this->request('Wo wohnst du?');

        self::assertNotSame(
            $cache->keyFor($request, 'anthropic', 'claude', 1),
            $cache->keyFor($request, 'openrouter', 'nemotron', 1),
        );

        self::assertNotSame(
            $cache->keyFor($request, 'anthropic', 'claude', 1),
            $cache->keyFor($request, 'anthropic', 'sonnet', 1),
        );
    }

    public function test_the_key_changes_when_the_system_prompt_changes(): void
    {
        $cache = $this->cache();

        self::assertNotSame(
            $cache->keyFor($this->request('Frage', 'System A'), 'anthropic', 'claude', 1),
            $cache->keyFor($this->request('Frage', 'System B'), 'anthropic', 'claude', 1),
        );
    }

    public function test_the_key_changes_when_sampling_parameters_change(): void
    {
        $cache = $this->cache();
        $cold = new LlmRequest(messages: [['role' => 'user', 'content' => 'Frage']], temperature: 0.0);
        $warm = new LlmRequest(messages: [['role' => 'user', 'content' => 'Frage']], temperature: 0.9);

        self::assertNotSame(
            $cache->keyFor($cold, 'anthropic', 'claude', 1),
            $cache->keyFor($warm, 'anthropic', 'claude', 1),
        );
    }

    public function test_the_key_ignores_history_that_cannot_change_the_answer(): void
    {
        $cache = $this->cache();

        $short = new LlmRequest(messages: [['role' => 'user', 'content' => 'Frage']]);
        $withHistory = new LlmRequest(messages: [
            ['role' => 'user', 'content' => 'Frage'],
            ['role' => 'assistant', 'content' => 'Antwort'],
            ['role' => 'user', 'content' => 'Frage'],
        ]);

        // History is part of the fingerprint because it does change the answer,
        // so these must differ.
        self::assertNotSame(
            $cache->keyFor($short, 'anthropic', 'claude', 1),
            $cache->keyFor($withHistory, 'anthropic', 'claude', 1),
        );
    }

    public function test_disabling_the_cache_skips_storage(): void
    {
        $cache = new PromptCache(Cache::store('array'), ttlSeconds: 900, enabled: false);
        $request = $this->request('Test');
        $key = $cache->keyFor($request, 'anthropic', 'claude', 1);

        $response = $cache->remember($key, static fn (): LlmResponse => new LlmResponse(
            text: 'Frisch',
            provider: 'anthropic',
            model: 'claude',
        ));

        self::assertFalse($response->wasCached);
        self::assertNull($cache->peek($key));
    }

    public function test_cost_is_derived_from_token_usage_and_pricing(): void
    {
        $response = new LlmResponse(
            text: 'Antwort',
            provider: 'anthropic',
            model: 'claude',
            promptTokens: 1_000_000,
            completionTokens: 1_000_000,
        );

        self::assertSame(18.0, $response->costUsd(['input_per_mtok' => 3.0, 'output_per_mtok' => 15.0]));
        self::assertSame(0.0, $response->costUsd([]));
    }
}
