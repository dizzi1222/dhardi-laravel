<?php

declare(strict_types=1);

namespace App\Llm\Reliability;

/**
 * Indirection over time so that retry backoff can be asserted in a test suite
 * without the suite actually sleeping.
 */
interface Sleeper
{
    public function sleep(float $seconds): void;
}
