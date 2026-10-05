<?php

declare(strict_types=1);

namespace App\Llm\Contracts;

/**
 * A completed model call, with the accounting needed to bill and to regress.
 */
final readonly class LlmResponse
{
    public function __construct(
        public string $text,
        public string $provider,
        public string $model,
        public int $promptTokens = 0,
        public int $completionTokens = 0,
        public int $latencyMs = 0,
        public bool $wasCached = false,
        public string $finishReason = 'stop',
    ) {}

    /**
     * Cost in USD, derived from the provider's per-million-token pricing.
     *
     * @param  array{input_per_mtok?: float, output_per_mtok?: float}  $pricing
     */
    public function costUsd(array $pricing): float
    {
        $input = ($this->promptTokens / 1_000_000) * ($pricing['input_per_mtok'] ?? 0);
        $output = ($this->completionTokens / 1_000_000) * ($pricing['output_per_mtok'] ?? 0);

        return round($input + $output, 6);
    }

    /**
     * The same answer marked as served from cache, for a uniform audit trail.
     */
    public function asCached(): self
    {
        return new self(
            text: $this->text,
            provider: $this->provider,
            model: $this->model,
            promptTokens: $this->promptTokens,
            completionTokens: $this->completionTokens,
            latencyMs: $this->latencyMs,
            wasCached: true,
            finishReason: $this->finishReason,
        );
    }
}
