<?php

declare(strict_types=1);

namespace App\Llm\Reliability;

use Illuminate\Support\Sleep;

/**
 * Waits on the framework's sleeper, which resolves to real time in production
 * and can be faked by the framework in tests.
 */
final class FrameworkSleeper implements Sleeper
{
    public function sleep(float $seconds): void
    {
        // `Sleep::for()` takes the duration positionally and stores it as
        // "pending"; the terminal method decides the unit. So the value here is
        // seconds, and passing a named argument does not exist on this signature.
        Sleep::for($seconds)->seconds();
    }
}
