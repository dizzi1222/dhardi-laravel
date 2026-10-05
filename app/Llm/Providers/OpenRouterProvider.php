<?php

declare(strict_types=1);

namespace App\Llm\Providers;

use App\Llm\Contracts\LlmProvider;
use App\Llm\Contracts\LlmRequest;
use App\Llm\Contracts\LlmResponse;
use App\Llm\Exceptions\LlmBadRequestException;
use App\Llm\Exceptions\LlmUnavailableException;
use Generator;
use Illuminate\Support\Facades\Http;

/**
 * OpenRouter backend, speaking the OpenAI-compatible chat completions shape.
 *
 * This is the cheapest usable backend and the one already proven in
 * production on PTD-Talento, so it stays in the chain behind the primary model
 * as a working second tier rather than as theoretical redundancy.
 */
final readonly class OpenRouterProvider implements LlmProvider
{
    public function __construct(
        private ?string $apiKey,
        private string $model,
        private string $baseUrl,
        private float $inputPerMtok,
        private float $outputPerMtok,
    ) {}

    public function name(): string
    {
        return 'openrouter';
    }

    public function model(): string
    {
        return $this->model;
    }

    public function pricing(): array
    {
        return [
            'input_per_mtok' => $this->inputPerMtok,
            'output_per_mtok' => $this->outputPerMtok,
        ];
    }

    public function isAvailable(): bool
    {
        return filled($this->apiKey);
    }

    public function complete(LlmRequest $request): LlmResponse
    {
        $started = microtime(true);

        $messages = [];

        if (filled($request->system)) {
            $messages[] = ['role' => 'system', 'content' => $request->system];
        }

        foreach ($request->messages as $message) {
            $messages[] = $message;
        }

        $response = Http::withToken((string) $this->apiKey)
            ->timeout($request->timeoutSeconds)
            ->acceptJson()
            ->asJson()
            ->post($this->baseUrl, [
                'model' => $this->model,
                'messages' => $messages,
                'max_tokens' => $request->maxTokens,
                'temperature' => $request->temperature,
            ]);

        $status = $response->status();

        if ($status === 429 || $status >= 500) {
            $retryAfter = $response->header('Retry-After');

            throw new LlmUnavailableException(
                message: "OpenRouter responded {$status}.",
                httpStatus: $status,
                retryAfterSeconds: $retryAfter !== null ? (float) $retryAfter : null,
            );
        }

        if ($status >= 300) {
            throw new LlmBadRequestException(
                message: "OpenRouter rejected the request ({$status}): ".mb_substr($response->body(), 0, 300),
                httpStatus: $status,
            );
        }

        $payload = $response->json() ?? [];

        return new LlmResponse(
            text: trim((string) data_get($payload, 'choices.0.message.content', '')),
            provider: $this->name(),
            model: (string) ($payload['model'] ?? $this->model),
            promptTokens: (int) data_get($payload, 'usage.prompt_tokens', 0),
            completionTokens: (int) data_get($payload, 'usage.completion_tokens', 0),
            latencyMs: (int) round((microtime(true) - $started) * 1000),
            finishReason: (string) data_get($payload, 'choices.0.finish_reason', 'stop'),
        );
    }

    /**
     * @return Generator<int, string>
     */
    public function stream(LlmRequest $request): Generator
    {
        $response = $this->complete($request);

        foreach (mb_str_split($response->text, 24) as $chunk) {
            yield $chunk;
        }
    }
}
