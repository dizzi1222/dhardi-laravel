<?php

declare(strict_types=1);

namespace App\Llm\Exceptions;

/**
 * The backend is refusing traffic: rate limit, capacity, or a transport
 * failure that will plausibly resolve itself.
 */
final class LlmUnavailableException extends LlmException
{
    public function __construct(
        string $message,
        ?int $httpStatus = null,
        ?float $retryAfterSeconds = null,
    ) {
        parent::__construct($message, $httpStatus, retryable: true, retryAfterSeconds: $retryAfterSeconds);
    }
}
