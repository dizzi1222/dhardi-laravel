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
use Illuminate\Support\Facades\Log;

/**
 * Anthropic Messages API backend.
 *
 * Translates the vendor's transport and error semantics into the gateway's
 * vocabulary. It deliberately knows nothing about caching or retries: those
 * decisions belong to the layer above so that every backend inherits them.
 */
final readonly class AnthropicProvider implements LlmProvider
{
    public function __construct(
        private ?string $apiKey,
        private string $model,
        private string $baseUrl,
        private string $version,
        private float $inputPerMtok,
        private float $outputPerMtok,
    ) {}

    public function name(): string
    {
        return 'anthropic';
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

        $response = $this->send($request, stream: false);

        $this->assertUsable($response->status(), $response->header('Retry-After'), $response->body());

        $payload = $response->json() ?? [];

        $text = $this->textFrom($payload);

        return new LlmResponse(
            text: $text,
            provider: $this->name(),
            model: (string) ($payload['model'] ?? $this->model),
            promptTokens: (int) data_get($payload, 'usage.input_tokens', 0),
            completionTokens: (int) data_get($payload, 'usage.output_tokens', 0),
            latencyMs: (int) round((microtime(true) - $started) * 1000),
            finishReason: (string) ($payload['stop_reason'] ?? 'stop'),
        );
    }

    /**
     * Server-sent events, parsed incrementally. Yields text deltas only; the
     * caller decides how to render them.
     *
     * @return Generator<int, string>
     */
    public function stream(LlmRequest $request): Generator
    {
        $response = $this->send($request, stream: true);

        $this->assertUsable($response->status(), $response->header('Retry-After'), $response->body());

        $body = $response->toPsrResponse()->getBody();
        $buffer = '';

        while (! $body->eof()) {
            $buffer .= $body->read(8192);

            while (($newline = strpos($buffer, "\n")) !== false) {
                $line = substr($buffer, 0, $newline);
                $buffer = substr($buffer, $newline + 1);

                $delta = $this->deltaFromLine(trim($line));

                if ($delta !== null) {
                    yield $delta;
                }
            }
        }
    }

    // Transport

    private function send(LlmRequest $request, bool $stream)
    {
        $payload = [
            'model' => $this->model,
            'max_tokens' => $request->maxTokens,
            'temperature' => $request->temperature,
            'messages' => array_map(
                static fn (array $message): array => [
                    'role' => $message['role'],
                    'content' => $message['content'],
                ],
                $request->messages,
            ),
        ];

        if (filled($request->system)) {
            // The system prompt is the largest stable block in this feature, so
            // it is the one worth caching at the provider.
            $payload['system'] = [[
                'type' => 'text',
                'text' => $request->system,
                'cache_control' => ['type' => 'ephemeral'],
            ]];
        }

        if ($stream) {
            $payload['stream'] = true;
        }

        $options = $stream ? ['stream' => true, 'read_timeout' => $request->timeoutSeconds] : [];

        return Http::withToken((string) $this->apiKey, 'x-api-key')
            ->withHeaders(['anthropic-version' => $this->version])
            ->timeout($request->timeoutSeconds)
            ->withOptions($options)
            ->acceptJson()
            ->asJson()
            ->post($this->baseUrl, $payload);
    }

    /**
     * Maps transport status onto the gateway's retry contract: 429 and 5xx are
     * transient, everything else in the 4xx range is the caller's fault.
     */
    private function assertUsable(int $status, ?string $retryAfter, string $body): void
    {
        if ($status >= 200 && $status < 300) {
            return;
        }

        Log::warning('anthropic.error', ['status' => $status, 'body' => mb_substr($body, 0, 400)]);

        if ($status === 429 || $status >= 500) {
            throw new LlmUnavailableException(
                message: "Anthropic responded {$status}.",
                httpStatus: $status,
                retryAfterSeconds: $retryAfter !== null ? (float) $retryAfter : null,
            );
        }

        throw new LlmBadRequestException(
            message: "Anthropic rejected the request ({$status}): ".mb_substr($body, 0, 300),
            httpStatus: $status,
        );
    }

    private function textFrom(array $payload): string
    {
        $parts = array_map(
            static fn (array $block): string => (string) ($block['text'] ?? ''),
            (array) ($payload['content'] ?? []),
        );

        return trim(implode('', $parts));
    }

    private function deltaFromLine(string $line): ?string
    {
        if (! str_starts_with($line, 'data:')) {
            return null;
        }

        $data = trim(substr($line, 5));

        if ($data === '' || $data === '[DONE]') {
            return null;
        }

        $decoded = json_decode($data, true);

        if (! is_array($decoded)) {
            return null;
        }

        if (($decoded['type'] ?? null) === 'error') {
            throw new LlmUnavailableException(
                message: 'Anthropic stream reported an error: '.mb_substr($data, 0, 300),
                httpStatus: 502,
            );
        }

        $delta = $decoded['delta']['text'] ?? null;

        return is_string($delta) && $delta !== '' ? $delta : null;
    }
}
