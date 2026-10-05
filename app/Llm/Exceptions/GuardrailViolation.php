<?php

declare(strict_types=1);

namespace App\Llm\Exceptions;

/**
 * A request or answer was rejected by a guardrail.
 *
 * Never retryable: the same input will be rejected identically on every
 * attempt, and retrying a refusal is how a safety control turns into a
 * reliability problem.
 */
final class GuardrailViolation extends LlmException
{
    public function __construct(
        string $message,
        public readonly string $rule = 'unknown',
    ) {
        parent::__construct($message, httpStatus: 422, retryable: false);
    }

    public function errorClass(): string
    {
        return 'GuardrailViolation:'.$this->rule;
    }
}
