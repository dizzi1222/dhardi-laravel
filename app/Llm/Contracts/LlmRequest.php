<?php

declare(strict_types=1);

namespace App\Llm\Contracts;

/**
 * A single turn handed to a model.
 */
final readonly class LlmRequest
{
    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public array $messages,
        public ?string $system = null,
        public int $maxTokens = 700,
        public float $temperature = 0.2,
        public float $timeoutSeconds = 15.0,
        public array $metadata = [],
    ) {}

    /**
     * Content of the most recent user turn.
     */
    public function lastUserMessage(): string
    {
        for ($index = count($this->messages) - 1; $index >= 0; $index--) {
            if ($this->messages[$index]['role'] === 'user') {
                return $this->messages[$index]['content'];
            }
        }

        return '';
    }

    /**
     * Stable fingerprint of everything that would change the answer. Used as
     * the cache key, so any field that affects output has to be part of it.
     *
     * @return array<string, mixed>
     */
    public function fingerprint(): array
    {
        return [
            'messages' => $this->messages,
            'system' => $this->system,
            'maxTokens' => $this->maxTokens,
            'temperature' => $this->temperature,
        ];
    }
}
