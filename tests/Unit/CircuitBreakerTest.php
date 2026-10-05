<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Llm\Exceptions\LlmUnavailableException;
use App\Llm\Reliability\CircuitBreaker;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * The breaker's job is to stop a failing backend from being hammered, and to
 * let exactly one probe through once it recovers. Without the probe, either the
 * backend stays dead forever or is flooded the instant it returns.
 */
final class CircuitBreakerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    private function breaker(int $threshold = 3, int $cooldown = 30): CircuitBreaker
    {
        return new CircuitBreaker(
            cache: Cache::store('array'),
            key: 'test-'.uniqid(),
            failureThreshold: $threshold,
            cooldownSeconds: $cooldown,
        );
    }

    public function test_a_fresh_breaker_is_closed_and_allows_requests(): void
    {
        $breaker = $this->breaker();

        self::assertSame(CircuitBreaker::CLOSED, $breaker->state());
        self::assertTrue($breaker->allows());
    }

    public function test_it_stays_closed_below_the_threshold(): void
    {
        $breaker = $this->breaker(threshold: 3);

        $breaker->recordFailure();
        $breaker->recordFailure();

        self::assertSame(CircuitBreaker::CLOSED, $breaker->state());
        self::assertTrue($breaker->allows());
    }

    public function test_it_opens_once_the_threshold_is_reached(): void
    {
        $breaker = $this->breaker(threshold: 3);

        for ($index = 0; $index < 3; $index++) {
            $breaker->recordFailure();
        }

        self::assertSame(CircuitBreaker::OPEN, $breaker->state());
        self::assertFalse($breaker->allows());
    }

    public function test_a_success_resets_the_failure_count(): void
    {
        $breaker = $this->breaker(threshold: 3);

        $breaker->recordFailure();
        $breaker->recordFailure();
        $breaker->recordSuccess();
        $breaker->recordFailure();

        self::assertSame(CircuitBreaker::CLOSED, $breaker->state());
    }

    public function test_it_becomes_half_open_after_the_cooldown(): void
    {
        $breaker = $this->breaker(threshold: 1, cooldown: 30);

        $breaker->recordFailure();

        self::assertSame(CircuitBreaker::OPEN, $breaker->state());

        // Rewind the recorded open time instead of waiting 30 real seconds.
        $this->rewindOpenTime($breaker, seconds: 31);

        self::assertSame(CircuitBreaker::HALF_OPEN, $breaker->state());
    }

    public function test_half_open_admits_exactly_one_probe(): void
    {
        $breaker = $this->breaker(threshold: 1, cooldown: 30);

        $breaker->recordFailure();
        $this->rewindOpenTime($breaker, seconds: 31);

        self::assertSame(CircuitBreaker::HALF_OPEN, $breaker->state());
        self::assertTrue($breaker->allows(), 'The first probe must be admitted.');
        self::assertFalse($breaker->allows(), 'A second concurrent probe must be refused.');
    }

    public function test_a_successful_probe_closes_the_breaker(): void
    {
        $breaker = $this->breaker(threshold: 1, cooldown: 30);

        $breaker->recordFailure();
        $this->rewindOpenTime($breaker, seconds: 31);
        $breaker->allows();
        $breaker->recordSuccess();

        self::assertSame(CircuitBreaker::CLOSED, $breaker->state());
        self::assertTrue($breaker->allows());
    }

    public function test_a_failed_probe_reopens_the_breaker(): void
    {
        $breaker = $this->breaker(threshold: 1, cooldown: 30);

        $breaker->recordFailure();
        $this->rewindOpenTime($breaker, seconds: 31);
        $breaker->allows();
        $breaker->recordFailure();

        self::assertSame(CircuitBreaker::OPEN, $breaker->state());
        self::assertFalse($breaker->allows());
    }

    public function test_rejecting_throws_an_unavailable_error(): void
    {
        $this->expectException(LlmUnavailableException::class);

        $this->breaker()->reject();
    }

    /**
     * Moves the recorded open timestamp backwards so the cooldown can be
     * observed without sleeping through it.
     */
    private function rewindOpenTime(CircuitBreaker $breaker, int $seconds): void
    {
        $key = 'llm:breaker:'.$this->keyOf($breaker).':opened_at';

        Cache::store('array')->put($key, time() - $seconds, 120);
    }

    private function keyOf(CircuitBreaker $breaker): string
    {
        $property = new \ReflectionProperty(CircuitBreaker::class, 'key');
        $property->setAccessible(true);

        return (string) $property->getValue($breaker);
    }
}
