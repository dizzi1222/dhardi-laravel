<?php

declare(strict_types=1);

namespace App\Llm\Exceptions;

use RuntimeException;

/**
 * Base class for every failure raised inside the gateway.
 *
 * Carries whether retrying is worthwhile, because that decision differs by
 * cause: a 429 or a 5xx is transient, a 400 means the request itself is wrong
 * and retrying it only wastes quota.
 */
class LlmException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?int $httpStatus = null,
        public readonly bool $retryable = false,
        public readonly ?float $retryAfterSeconds = null,
    ) {
        parent::__construct($message);
    }

    /**
     * Short label written to `llm_runs.error_class`.
     */
    public function errorClass(): string
    {
        return class_basename($this);
    }
}
