<?php

declare(strict_types=1);

namespace App\Llm\Reliability;

use Illuminate\Contracts\Cache\Repository as CacheRepository;

/**
 * Hands out one circuit breaker per backend, memoised per instance.
 *
 * Breakers must not be resolved fresh per request or their state resets every
 * time, which is precisely the failure the breaker exists to prevent.
 */
final class BreakerRegistry
{
    /** @var array<string, CircuitBreaker> */
    private array $breakers = [];

    public function __construct(
        private readonly CacheRepository $cache,
        private readonly int $failureThreshold = 5,
        private readonly int $cooldownSeconds = 30,
    ) {}

    public function for(string $provider): CircuitBreaker
    {
        return $this->breakers[$provider] ??= new CircuitBreaker(
            cache: $this->cache,
            key: $provider,
            failureThreshold: $this->failureThreshold,
            cooldownSeconds: $this->cooldownSeconds,
        );
    }
}
