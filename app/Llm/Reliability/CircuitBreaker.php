<?php

declare(strict_types=1);

namespace App\Llm\Reliability;

use App\Llm\Exceptions\LlmUnavailableException;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

/**
 * Stops hammering a backend that is already failing.
 *
 * Retries and fallbacks alone are not enough on their own: without a breaker,
 * every concurrent request keeps retrying a dead endpoint, and the instance
 * turns an upstream outage into a self-inflicted one.
 *
 * State lives in the shared cache rather than in the container, so it holds
 * across requests and across instances. Uses the store's own TTL to persist
 * `null`/`false` correctly.
 */
final class CircuitBreaker
{
    public const CLOSED = 'closed';

    public const OPEN = 'open';

    public const HALF_OPEN = 'half_open';

    public function __construct(
        private readonly CacheRepository $cache,
        private readonly string $key,
        private readonly int $failureThreshold = 5,
        private readonly int $cooldownSeconds = 30,
    ) {}

    public function state(): string
    {
        $failures = (int) $this->cache->get($this->failuresKey(), 0);

        if ($failures < $this->failureThreshold) {
            return self::CLOSED;
        }

        $openedAt = (int) $this->cache->get($this->openedKey(), 0);

        if ($openedAt === 0) {
            return self::CLOSED;
        }

        return (time() - $openedAt) >= $this->cooldownSeconds
            ? self::HALF_OPEN
            : self::OPEN;
    }

    /**
     * Whether a request may be attempted. In half-open state exactly one probe
     * is admitted, so a recovering backend is not immediately flooded.
     */
    public function allows(): bool
    {
        $state = $this->state();

        if ($state === self::CLOSED) {
            return true;
        }

        if ($state === self::OPEN) {
            return false;
        }

        // `add()` only succeeds when the key was absent, so the first caller to
        // arrive after the cooldown wins the single probe and everyone
        // concurrent with it is refused.
        return $this->cache->add($this->probeKey(), true, $this->cooldownSeconds);
    }

    public function recordSuccess(): void
    {
        $this->cache->forget($this->failuresKey());
        $this->cache->forget($this->openedKey());
        $this->cache->forget($this->probeKey());
    }

    public function recordFailure(): void
    {
        $failures = (int) $this->cache->get($this->failuresKey(), 0) + 1;

        $this->cache->put($this->failuresKey(), $failures, $this->cooldownSeconds * 4);

        if ($failures >= $this->failureThreshold) {
            $this->cache->put($this->openedKey(), time(), $this->cooldownSeconds * 4);
        }
    }

    /**
     * @throws LlmUnavailableException
     */
    public function reject(): never
    {
        throw new LlmUnavailableException(
            message: "Circuit breaker [{$this->key}] is open.",
            httpStatus: 503,
        );
    }

    private function failuresKey(): string
    {
        return "llm:breaker:{$this->key}:failures";
    }

    private function openedKey(): string
    {
        return "llm:breaker:{$this->key}:opened_at";
    }

    private function probeKey(): string
    {
        return "llm:breaker:{$this->key}:probe";
    }
}
