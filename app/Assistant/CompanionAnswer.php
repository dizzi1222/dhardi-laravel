<?php

declare(strict_types=1);

namespace App\Assistant;

/**
 * The answer plus everything needed to show it honestly in the UI.
 *
 * The provider and model are surfaced rather than hidden: an assistant that
 * will not say which model answered is harder to trust than one that will.
 */
final readonly class CompanionAnswer
{
    public function __construct(
        public string $text,
        public string $provider,
        public string $model,
        public string $intent,
        public string $conversationId,
        public int $latencyMs,
        public bool $cached,
        public float $costUsd,
        public int $promptTokens,
        public int $completionTokens,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'text' => $this->text,
            'provider' => $this->provider,
            'model' => $this->model,
            'intent' => $this->intent,
            'conversation_id' => $this->conversationId,
            'latency_ms' => $this->latencyMs,
            'cached' => $this->cached,
            'cost_usd' => $this->costUsd,
            'prompt_tokens' => $this->promptTokens,
            'completion_tokens' => $this->completionTokens,
        ];
    }
}
