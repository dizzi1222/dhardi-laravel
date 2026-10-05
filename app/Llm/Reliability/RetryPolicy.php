<?php

declare(strict_types=1);

namespace App\Llm\Reliability;

use App\Llm\Exceptions\GuardrailViolation;
use Closure;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Retries a transient failure with capped exponential backoff and jitter.
 *
 * Two decisions matter more than the delay curve itself:
 *
 *  1. Only failures declared retryable are retried. A malformed request is
 *     retried forever otherwise, burning quota against a 400.
 *  2. A `Retry-After` from the provider wins over the computed curve. When a
 *     backend tells you when to come back, its answer is better informed than
 *     a local heuristic.
 */
final class RetryPolicy
{
    /** @var Closure(int): int */
    private readonly Closure $jitter;

    public function __construct(
        public int $maxAttempts = 3,
        public int $baseDelayMs = 250,
        public int $maxDelayMs = 4000,
        public float $multiplier = 2.0,
        private readonly ?Sleeper $sleeper = null,
        ?Closure $jitter = null,
        private readonly ?LoggerInterface $logger = null,
    ) {
        $this->jitter = $jitter ?? static fn (int $cap): int => random_int(0, max(0, $cap));
    }

    /**
     * Run an operation until it succeeds or the attempt budget is spent.
     *
     * @template T
     *
     * @param  Closure(): T  $operation
     * @return T
     *
     * @throws Throwable The last failure, once retries are exhausted.
     */
    public function execute(Closure $operation): mixed
    {
        $attempt = 0;

        while (true) {
            $attempt++;

            try {
                return $operation();
            } catch (Throwable $e) {
                if (! $this->shouldRetry($e, $attempt)) {
                    throw $e;
                }

                $delay = $this->delayMsFor($attempt, $this->retryAfterFrom($e));

                $this->logger?->warning('llm.retry', [
                    'attempt' => $attempt,
                    'max_attempts' => $this->maxAttempts,
                    'delay_ms' => $delay,
                    'error' => $e->getMessage(),
                ]);

                ($this->sleeper ?? app(Sleeper::class))->sleep($delay / 1000);
            }
        }
    }

    public function shouldRetry(Throwable $e, int $attempt): bool
    {
        if ($attempt >= $this->maxAttempts) {
            return false;
        }

        $retryable = property_exists($e, 'retryable')
            ? (bool) $e->retryable
            : ! $e instanceof GuardrailViolation;

        return $retryable;
    }

    /**
     * Exponential backoff with full jitter, clamped to the ceiling. A
     * provider-supplied `Retry-After` overrides the curve.
     */
    public function delayMsFor(int $attempt, ?float $retryAfterSeconds = null): int
    {
        if ($retryAfterSeconds !== null) {
            $cappedSeconds = min($retryAfterSeconds, $this->maxDelayMs / 1000);

            return max(0, (int) round($cappedSeconds * 1000));
        }

        $ceiling = min(
            (float) $this->baseDelayMs * ($this->multiplier ** max(0, $attempt - 1)),
            (float) $this->maxDelayMs,
        );

        // The jitter source is a Closure, so it has to be invoked as a
        // variable. Calling `$this->jitter(...)` would look for a method with
        // that name and fail at runtime.
        $jittered = ($this->jitter)((int) $ceiling);

        return max(0, min($jittered, (int) $ceiling));
    }

    private function retryAfterFrom(Throwable $e): ?float
    {
        return property_exists($e, 'retryAfterSeconds') ? $e->retryAfterSeconds : null;
    }
}
