<?php

declare(strict_types=1);

namespace App\Llm;

use App\Llm\Contracts\LlmProvider;
use App\Llm\Contracts\LlmRequest;
use App\Llm\Contracts\LlmResponse;
use App\Llm\Exceptions\GuardrailViolation;
use App\Llm\Exceptions\LlmException;
use App\Llm\Exceptions\LlmUnavailableException;
use App\Llm\Reliability\BreakerRegistry;
use App\Llm\Reliability\FallbackChain;
use App\Llm\Reliability\Guardrails;
use App\Llm\Reliability\PromptCache;
use App\Llm\Reliability\RetryPolicy;
use App\Models\LlmRun;
use App\Support\BestEffort;
use App\Support\TenantContext;
use Generator;
use Throwable;

/**
 * The single entry point for talking to a model.
 *
 * Order of operations, and the reason for it:
 *
 *   1. Guardrails on input, before anything spends money or hits the network.
 *   2. Cache, so a repeated question never becomes a repeated bill.
 *   3. For each available backend: breaker check, then bounded retries.
 *   4. Guardrails on output, before the text reaches a caller.
 *   5. One observability row per attempt, whatever the outcome.
 *
 * The last point is what separates an LLM feature that works from one that can
 * be operated: after an incident, the question is answerable from the data.
 */
final class LlmGateway
{
    private ?LlmProvider $lastStreamingProvider = null;

    public function __construct(
        private readonly FallbackChain $chain,
        private readonly RetryPolicy $retry,
        private readonly BreakerRegistry $breakers,
        private readonly PromptCache $cache,
        private readonly Guardrails $guardrails,
        private readonly TenantContext $tenants,
    ) {}

    /**
     * @throws LlmException
     */
    public function complete(LlmRequest $request, ?int $conversationId = null): LlmResponse
    {
        $request = $this->sanitize($request);

        $providers = $this->chain->available();

        if ($providers === []) {
            throw new LlmUnavailableException('No LLM backend is available.');
        }

        $cached = $this->recall($request, $providers, $conversationId);

        if ($cached !== null) {
            return $cached;
        }

        $failures = [];

        foreach ($providers as $provider) {
            $breaker = $this->breakers->for($provider->name());

            if (! $breaker->allows()) {
                $this->audit($provider->name(), $provider->model(), $request, LlmRun::OUTCOME_ERROR, [
                    'attempt' => 0,
                    'error_class' => 'CircuitBreakerOpen',
                    'http_status' => 503,
                    'conversation_id' => $conversationId,
                ]);

                continue;
            }

            try {
                $response = $this->retry->execute(
                    fn (): LlmResponse => $this->attempt($provider, $request, $conversationId),
                );

                $breaker->recordSuccess();
                $this->primeCache($request, $provider, $response);

                return $response;
            } catch (GuardrailViolation $e) {
                // A refusal is a correct outcome, not an outage: propagate so
                // the caller can show it, because retrying identical text
                // cannot change the outcome.
                throw $e;
            } catch (Throwable $e) {
                $breaker->recordFailure();
                $failures[] = $e;
            }
        }

        throw $this->lastOf($failures);
    }

    /**
     * Streaming answers. Guardrails and the fallback chain are applied for
     * availability, but the cache is deliberately skipped: a partially consumed
     * stream cannot be replayed as a cache hit, and caching on completion
     * would require buffering, which defeats the purpose of streaming.
     *
     * @return Generator<int, string>
     */
    public function stream(LlmRequest $request, ?int $conversationId = null): Generator
    {
        $request = $this->sanitize($request);

        $providers = $this->chain->available();

        if ($providers === []) {
            throw new LlmUnavailableException('No LLM backend is available.');
        }

        $lastError = null;

        foreach ($providers as $provider) {
            $breaker = $this->breakers->for($provider->name());

            if (! $breaker->allows()) {
                continue;
            }

            $started = microtime(true);
            $emitted = false;

            try {
                $this->lastStreamingProvider = $provider;

                foreach ($provider->stream($request) as $delta) {
                    $emitted = true;

                    yield $delta;
                }

                $breaker->recordSuccess();

                $this->audit($provider->name(), $provider->model(), $request, LlmRun::OUTCOME_SUCCESS, [
                    'latency_ms' => (int) round((microtime(true) - $started) * 1000),
                    'conversation_id' => $conversationId,
                    'error_class' => $emitted ? null : 'EmptyStream',
                ]);

                return;
            } catch (Throwable $e) {
                $lastError = $e;

                if ($emitted) {
                    // Tokens already reached the client. Falling back now would
                    // append a second answer to a half-written sentence.
                    break;
                }

                $breaker->recordFailure();
            }
        }

        $this->audit(null, null, $request, LlmRun::OUTCOME_ERROR, [
            'attempt' => 0,
            'error_class' => $lastError === null ? 'AllBackendsSkipped' : $lastError::class,
            'http_status' => 502,
            'conversation_id' => $conversationId,
        ]);

        throw $lastError instanceof LlmException
            ? $lastError
            : new LlmUnavailableException('All streaming backends failed.');
    }

    /**
     * The backend that actually served the last stream, or null if none did.
     *
     * The generator cannot return a value, so this is how the caller learns
     * which model produced the text it just wrote to the client. Reporting
     * "unknown" here would be a lie the UI would repeat to the visitor.
     */
    public function lastStreamingProvider(): ?LlmProvider
    {
        return $this->lastStreamingProvider;
    }

    // Stages

    /**
     * Applies input guardrails to every user turn. A violation propagates: it
     * is a client error, not a backend problem.
     */
    private function sanitize(LlmRequest $request): LlmRequest
    {
        $messages = array_map(function (array $message): array {
            if ($message['role'] !== 'user') {
                return $message;
            }

            $message['content'] = $this->guardrails->inspectInput($message['content']);

            return $message;
        }, $request->messages);

        return new LlmRequest(
            messages: $messages,
            system: $request->system,
            maxTokens: $request->maxTokens,
            temperature: $request->temperature,
            timeoutSeconds: $request->timeoutSeconds,
            metadata: $request->metadata,
        );
    }

    /**
     * One attempt against one backend, with output guardrails and accounting.
     */
    private function attempt(LlmProvider $provider, LlmRequest $request, ?int $conversationId): LlmResponse
    {
        $response = $provider->complete($request);

        try {
            $text = $this->guardrails->inspectOutput($response->text);
        } catch (GuardrailViolation $e) {
            $this->audit($provider->name(), $provider->model(), $request, LlmRun::OUTCOME_BLOCKED, [
                'latency_ms' => $response->latencyMs,
                'http_status' => $response->wasCached ? null : 200,
                'prompt_tokens' => $response->promptTokens,
                'completion_tokens' => $response->completionTokens,
                'error_class' => $e->errorClass(),
                'conversation_id' => $conversationId,
            ]);

            throw $e;
        }

        $this->audit($provider->name(), $provider->model(), $request, LlmRun::OUTCOME_SUCCESS, [
            'latency_ms' => $response->latencyMs,
            'http_status' => $response->wasCached ? null : 200,
            'prompt_tokens' => $response->promptTokens,
            'completion_tokens' => $response->completionTokens,
            'cost_usd' => $response->costUsd($provider->pricing()),
            'conversation_id' => $conversationId,
        ]);

        return new LlmResponse(
            text: $text,
            provider: $response->provider,
            model: $response->model,
            promptTokens: $response->promptTokens,
            completionTokens: $response->completionTokens,
            latencyMs: $response->latencyMs,
            wasCached: $response->wasCached,
            finishReason: $response->finishReason,
        );
    }

    /**
     * Looks for an identical, already-answered prompt across every backend.
     * Checking all keys rather than only the primary one means an answer
     * produced by the fallback is still found on the next request.
     *
     * @param  array<int, LlmProvider>  $providers
     */
    private function recall(LlmRequest $request, array $providers, ?int $conversationId): ?LlmResponse
    {
        $tenantId = $this->tenants->id();

        foreach ($providers as $provider) {
            $cached = $this->cache->peek(
                $this->cache->keyFor($request, $provider->name(), $provider->model(), $tenantId),
            );

            if ($cached === null) {
                continue;
            }

            $this->audit($provider->name(), $cached->model, $request, LlmRun::OUTCOME_CACHED, [
                'conversation_id' => $conversationId,
            ]);

            return $cached;
        }

        return null;
    }

    private function primeCache(LlmRequest $request, LlmProvider $provider, LlmResponse $response): void
    {
        $this->cache->remember(
            $this->cache->keyFor($request, $provider->name(), $provider->model(), $this->tenants->id()),
            static fn (): LlmResponse => $response,
        );
    }

    /**
     * Writes the audit row. Provider identity is nullable because some
     * failures happen before any backend has been chosen.
     *
     * Best-effort by design: the row exists so an outage can be investigated
     * after the fact, and losing one must never cost the visitor their answer.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function audit(?string $provider, ?string $model, LlmRequest $request, string $outcome, array $attributes = []): void
    {
        if ($outcome === LlmRun::OUTCOME_SUCCESS && ! config('llm.observability.log_successful_runs', true)) {
            return;
        }

        $tenantId = $this->tenants->id();

        if ($tenantId === null) {
            return;
        }

        BestEffort::write('llm.audit', fn (): LlmRun => LlmRun::query()->create(array_merge([
            'tenant_id' => $tenantId,
            'provider' => $provider ?? 'none',
            'model' => $model ?? 'none',
            'attempt' => 1,
            'outcome' => $outcome,
            'latency_ms' => 0,
            'prompt_tokens' => 0,
            'completion_tokens' => 0,
            'cost_usd' => 0,
            'was_cached' => $outcome === LlmRun::OUTCOME_CACHED,
        ], $attributes)));
    }

    /**
     * The error surfaced when no backend produced an answer.
     *
     * Typed as the base LlmException rather than LlmUnavailableException,
     * because by this point the chain has already exhausted its retries: the
     * caller repeating the request would only repeat the failure.
     *
     * @param  array<int, Throwable>  $failures
     */
    private function lastOf(array $failures): LlmException
    {
        $last = $failures === [] ? null : end($failures);

        if ($last instanceof LlmException) {
            return $last;
        }

        return new LlmException(
            message: $last?->getMessage() ?? 'Every LLM backend failed without a typed error.',
            httpStatus: 502,
            retryable: false,
        );
    }
}
