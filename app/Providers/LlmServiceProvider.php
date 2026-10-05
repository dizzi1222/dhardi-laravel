<?php

declare(strict_types=1);

namespace App\Providers;

use App\Llm\Contracts\LlmProvider;
use App\Llm\LlmGateway;
use App\Llm\Providers\AnthropicProvider;
use App\Llm\Providers\DeterministicProvider;
use App\Llm\Providers\OpenRouterProvider;
use App\Llm\Reliability\BreakerRegistry;
use App\Llm\Reliability\FallbackChain;
use App\Llm\Reliability\FrameworkSleeper;
use App\Llm\Reliability\Guardrails;
use App\Llm\Reliability\PromptCache;
use App\Llm\Reliability\RetryPolicy;
use App\Llm\Reliability\Sleeper;
use App\Support\TenantContext;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

/**
 * Wires the model layer.
 *
 * Everything stateful — the tenant, the breaker registry — is a singleton, so
 * that its state survives the whole request. Everything stateless is resolved
 * fresh, which keeps long-lived instances from pinning a stale locale or a
 * stale configuration into memory.
 */
final class LlmServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TenantContext::class);

        $this->app->singleton(Sleeper::class, FrameworkSleeper::class);

        $this->app->singleton(RetryPolicy::class, fn (Application $app): RetryPolicy => new RetryPolicy(
            maxAttempts: (int) config('llm.retry.max_attempts'),
            baseDelayMs: (int) config('llm.retry.base_delay_ms'),
            maxDelayMs: (int) config('llm.retry.max_delay_ms'),
            multiplier: (float) config('llm.retry.multiplier'),
            sleeper: $app->make(Sleeper::class),
            // Injected rather than reached through the Log facade so the policy
            // can be unit tested without booting the container.
            logger: $app->make('log'),
        ));

        $this->app->singleton(BreakerRegistry::class, fn (Application $app): BreakerRegistry => new BreakerRegistry(
            cache: $app->make('cache')->store((string) config('llm.cache.store')),
            failureThreshold: (int) config('llm.breaker.failure_threshold'),
            cooldownSeconds: (int) config('llm.breaker.cooldown_seconds'),
        ));

        $this->app->singleton(PromptCache::class, fn (Application $app): PromptCache => new PromptCache(
            cache: $app->make('cache')->store((string) config('llm.cache.store')),
            ttlSeconds: (int) config('llm.cache.ttl_seconds'),
            enabled: (bool) config('llm.cache.enabled'),
        ));

        $this->app->singleton(Guardrails::class, fn (): Guardrails => new Guardrails(
            maxInputChars: (int) config('llm.guardrails.max_input_chars'),
            maxOutputChars: (int) config('llm.guardrails.max_output_chars'),
            blockedInputFragments: (array) config('llm.guardrails.blocked_input_fragments'),
            blockedOutputFragments: (array) config('llm.guardrails.blocked_output_fragments'),
        ));

        $this->app->bind(AnthropicProvider::class, fn (): AnthropicProvider => new AnthropicProvider(
            apiKey: config('llm.providers.anthropic.key'),
            model: (string) config('llm.providers.anthropic.model'),
            baseUrl: (string) config('llm.providers.anthropic.base_url'),
            version: (string) config('llm.providers.anthropic.version'),
            inputPerMtok: (float) config('llm.providers.anthropic.input_per_mtok'),
            outputPerMtok: (float) config('llm.providers.anthropic.output_per_mtok'),
        ));

        $this->app->bind(OpenRouterProvider::class, fn (): OpenRouterProvider => new OpenRouterProvider(
            apiKey: config('llm.providers.openrouter.key'),
            model: (string) config('llm.providers.openrouter.model'),
            baseUrl: (string) config('llm.providers.openrouter.base_url'),
            inputPerMtok: (float) config('llm.providers.openrouter.input_per_mtok'),
            outputPerMtok: (float) config('llm.providers.openrouter.output_per_mtok'),
        ));

        $this->app->bind(DeterministicProvider::class, fn (): DeterministicProvider => new DeterministicProvider);

        $this->app->singleton(FallbackChain::class, function (Application $app): FallbackChain {
            $providers = [];

            foreach ((array) config('llm.chain') as $class) {
                $provider = $app->make($class);

                if ($provider instanceof LlmProvider) {
                    $providers[] = $provider;
                }
            }

            return new FallbackChain($providers);
        });

        $this->app->singleton(LlmGateway::class);
    }
}
