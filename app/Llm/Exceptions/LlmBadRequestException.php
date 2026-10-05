<?php

declare(strict_types=1);

namespace App\Llm\Exceptions;

/**
 * The request was rejected as malformed. Never worth retrying unchanged.
 */
final class LlmBadRequestException extends LlmException
{
    public function __construct(string $message, ?int $httpStatus = 400)
    {
        parent::__construct($message, $httpStatus, retryable: false);
    }
}
