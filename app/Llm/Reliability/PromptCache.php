<?php

declare(strict_types=1);

namespace App\Llm\Reliability;

use App\Llm\Contracts\LlmRequest;
use App\Llm\Contracts\LlmResponse;
use Closure;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

/**
 * Short-lived memoisation of identical prompts.
 *
 * Scoped per tenant and per provider, so one customer's cache can never serve
 * another's answer, and a model change never serves stale text from a
 * different model. The key is derived from the full request fingerprint
 * rather than from the user input alone, so a change to the system prompt
 * invalidates correctly.
 */
final readonly class PromptCache
{
    public function __construct(
        private CacheRepository $cache,
        private int $ttlSeconds = 900,
        private bool $enabled = true,
    ) {}

    public function keyFor(LlmRequest $request, string $provider, string $model, ?int $tenantId): string
    {
        return 'llm:cache:'.implode(':', [
            't'.($tenantId ?? 0),
            $provider,
            $model,
            hash('sha256', (string) json_encode($request->fingerprint())),
        ]);
    }

    /**
     * Read a cached response without populating the cache on a miss.
     */
    public function peek(string $key): ?LlmResponse
    {
        /** @var array{text: string, provider: string, model: string, prompt_tokens: int, completion_tokens: int, finish_reason: string}|null $hit */
        $hit = $this->cache->get($key);

        if (! is_array($hit)) {
            return null;
        }

        return (new LlmResponse(
            text: $hit['text'],
            provider: $hit['provider'],
            model: $hit['model'],
            promptTokens: $hit['prompt_tokens'],
            completionTokens: $hit['completion_tokens'],
            finishReason: $hit['finish_reason'] ?? 'stop',
        ))->asCached();
    }

    /**
     * @template T of LlmResponse
     *
     * @param  Closure(): T  $producer
     * @return T
     */
    public function remember(string $key, Closure $producer): LlmResponse
    {
        if (! $this->enabled) {
            return $producer();
        }

        $hit = $this->peek($key);

        if ($hit !== null) {
            return $hit;
        }

        $response = $producer();

        $this->cache->put($key, [
            'text' => $response->text,
            'provider' => $response->provider,
            'model' => $response->model,
            'prompt_tokens' => $response->promptTokens,
            'completion_tokens' => $response->completionTokens,
            'finish_reason' => $response->finishReason,
        ], $this->ttlSeconds);

        return $response;
    }

    public function forget(string $key): void
    {
        $this->cache->forget($key);
    }
}
