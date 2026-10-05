<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Llm\Exceptions\GuardrailViolation;
use App\Llm\Exceptions\LlmBadRequestException;
use App\Llm\Exceptions\LlmUnavailableException;
use App\Llm\Reliability\RetryPolicy;
use App\Llm\Reliability\Sleeper;
use Tests\TestCase;

/**
 * The backoff curve is asserted without the suite ever sleeping: the sleeper is
 * injected and records what it was asked to wait for.
 */
final class RetryPolicyTest extends TestCase
{
    private array $slept = [];

    private function policy(int $maxAttempts = 3, int $maxDelayMs = 4000): RetryPolicy
    {
        $this->slept = [];

        // Deterministic jitter: return the ceiling, so the curve is exact.
        return new RetryPolicy(
            maxAttempts: $maxAttempts,
            baseDelayMs: 250,
            maxDelayMs: $maxDelayMs,
            multiplier: 2.0,
            sleeper: new class($this->slept) implements Sleeper
            {
                public function __construct(private array &$log) {}

                public function sleep(float $seconds): void
                {
                    $this->log[] = $seconds;
                }
            },
            jitter: static fn (int $cap): int => $cap,
        );
    }

    public function test_it_returns_the_first_successful_result_without_sleeping(): void
    {
        $result = $this->policy()->execute(static fn (): string => 'ok');

        self::assertSame('ok', $result);
        self::assertSame([], $this->slept, 'A success on the first attempt must not wait.');
    }

    public function test_it_retries_a_transient_failure_and_eventually_succeeds(): void
    {
        $attempts = 0;

        $result = $this->policy()->execute(function () use (&$attempts): string {
            $attempts++;

            if ($attempts < 3) {
                throw new LlmUnavailableException('rate limited', 429);
            }

            return 'recovered';
        });

        self::assertSame('recovered', $result);
        self::assertSame(3, $attempts);
        self::assertCount(2, $this->slept);
    }

    public function test_it_gives_up_after_the_attempt_budget_and_rethrows(): void
    {
        $this->expectException(LlmUnavailableException::class);

        $this->policy(maxAttempts: 3)->execute(static function (): never {
            throw new LlmUnavailableException('still down', 503);
        });
    }

    public function test_it_does_not_retry_a_non_retryable_failure(): void
    {
        $attempts = 0;

        try {
            $this->policy()->execute(function () use (&$attempts): never {
                $attempts++;

                throw new LlmBadRequestException('malformed request', 400);
            });
        } catch (LlmBadRequestException) {
            // Expected.
        }

        self::assertSame(1, $attempts, 'A 400 is the caller’s fault and must not be retried.');
        self::assertSame([], $this->slept);
    }

    public function test_it_never_retries_a_guardrail_violation(): void
    {
        $attempts = 0;

        try {
            $this->policy()->execute(function () use (&$attempts): never {
                $attempts++;

                throw new GuardrailViolation('blocked', 'input.blocked_fragment');
            });
        } catch (GuardrailViolation) {
            // Expected: refusing is a correct outcome, not an outage.
        }

        self::assertSame(1, $attempts);
    }

    public function test_it_retries_an_untyped_exception_once(): void
    {
        $attempts = 0;

        try {
            $this->policy(maxAttempts: 2)->execute(function () use (&$attempts): never {
                $attempts++;

                throw new \RuntimeException('connection reset');
            });
        } catch (\RuntimeException) {
            // Expected.
        }

        self::assertSame(2, $attempts);
    }

    public function test_the_backoff_is_exponential(): void
    {
        $policy = $this->policy();

        self::assertSame(250, $policy->delayMsFor(1));
        self::assertSame(500, $policy->delayMsFor(2));
        self::assertSame(1000, $policy->delayMsFor(3));
        self::assertSame(2000, $policy->delayMsFor(4));
    }

    public function test_the_backoff_is_capped(): void
    {
        self::assertSame(4000, $this->policy()->delayMsFor(12));
    }

    public function test_jitter_never_exceeds_the_ceiling(): void
    {
        $policy = new RetryPolicy(
            maxAttempts: 3,
            baseDelayMs: 250,
            maxDelayMs: 4000,
            multiplier: 2.0,
            sleeper: $this->sleeper(),
            jitter: static fn (int $cap): int => $cap * 5,
        );

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            self::assertLessThanOrEqual(4000, $policy->delayMsFor($attempt));
        }
    }

    public function test_a_provider_retry_after_header_overrides_the_curve(): void
    {
        $policy = $this->policy();

        // A backend that knows when it will accept traffic again is better
        // informed than a local heuristic.
        self::assertSame(1500, $policy->delayMsFor(1, retryAfterSeconds: 1.5));
    }

    public function test_a_retry_after_beyond_the_ceiling_is_clamped(): void
    {
        self::assertSame(4000, $this->policy()->delayMsFor(1, retryAfterSeconds: 30.0));
    }

    private function sleeper(): Sleeper
    {
        return new class implements Sleeper
        {
            public function sleep(float $seconds): void {}
        };
    }
}
