<?php

declare(strict_types=1);

namespace App\Llm\Reliability;

use App\Llm\Exceptions\GuardrailViolation;

/**
 * Input and output boundary checks.
 *
 * Model output is untrusted input to everything downstream: it reaches a
 * browser, a database and, in this project's case, a hiring decision. These
 * checks are deliberately cheap and synchronous so they can run on every
 * request rather than only in tests.
 */
final class Guardrails
{
    public function __construct(
        private readonly int $maxInputChars = 2000,
        private readonly int $maxOutputChars = 8000,
        private readonly array $blockedInputFragments = [],
        private readonly array $blockedOutputFragments = [],
    ) {}

    /**
     * @throws GuardrailViolation
     */
    public function inspectInput(string $text): string
    {
        $clean = $this->stripControlCharacters(trim($text));

        if ($clean === '') {
            throw new GuardrailViolation('Empty prompt.', 'input.empty');
        }

        if (mb_strlen($clean) > $this->maxInputChars) {
            throw new GuardrailViolation(
                "Prompt exceeds {$this->maxInputChars} characters.",
                'input.too_long',
            );
        }

        foreach ($this->blockedInputFragments as $fragment) {
            if ($this->contains($clean, (string) $fragment)) {
                throw new GuardrailViolation(
                    "Prompt matched blocked fragment [{$fragment}].",
                    'input.blocked_fragment',
                );
            }
        }

        return $clean;
    }

    /**
     * @throws GuardrailViolation
     */
    public function inspectOutput(string $text): string
    {
        $clean = trim($text);

        if ($clean === '') {
            throw new GuardrailViolation('Empty answer.', 'output.empty');
        }

        if (mb_strlen($clean) > $this->maxOutputChars) {
            throw new GuardrailViolation(
                "Answer exceeds {$this->maxOutputChars} characters.",
                'output.too_long',
            );
        }

        foreach ($this->blockedOutputFragments as $fragment) {
            if ($this->contains($clean, (string) $fragment)) {
                throw new GuardrailViolation(
                    "Answer matched blocked fragment [{$fragment}].",
                    'output.blocked_fragment',
                );
            }
        }

        return $clean;
    }

    /**
     * Case-insensitive containment on folded text.
     *
     * `str_contains` is byte-based, so it misses a case-folded match whose
     * multi-byte form differs in length. Folding both sides keeps the check
     * correct for non-ASCII fragments.
     */
    private function contains(string $haystack, string $needle): bool
    {
        return str_contains(
            mb_strtolower($haystack),
            mb_strtolower($needle),
        );
    }

    /**
     * Drops control characters while preserving newlines and tabs, which are
     * meaningful in prose and would otherwise be silently mangled.
     */
    private function stripControlCharacters(string $text): string
    {
        return (string) preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text);
    }
}
