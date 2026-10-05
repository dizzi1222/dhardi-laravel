<?php

declare(strict_types=1);

namespace App\Llm\Providers;

use App\Llm\Contracts\LlmProvider;
use App\Llm\Contracts\LlmRequest;
use App\Llm\Contracts\LlmResponse;
use Generator;
use Illuminate\Support\Facades\Lang;

/**
 * Always-available responder with no network dependency.
 *
 * It exists so the assistant has a floor rather than a cliff: with every paid
 * backend down, a visitor still gets a useful answer assembled from the site's
 * own content instead of an error. Because it needs no credentials it is the
 * last link of the chain, and because its output is a pure function of the
 * prompt it is also what the test suite runs against.
 *
 * Answers are resolved from the active locale at call time rather than being
 * injected once, so switching language mid-conversation is respected.
 */
final class DeterministicProvider implements LlmProvider
{
    public function name(): string
    {
        return 'deterministic';
    }

    public function model(): string
    {
        return 'rule-based-v1';
    }

    public function pricing(): array
    {
        return ['input_per_mtok' => 0.0, 'output_per_mtok' => 0.0];
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function complete(LlmRequest $request): LlmResponse
    {
        $started = microtime(true);

        $question = $request->lastUserMessage();
        $text = $this->respondTo($question);

        return new LlmResponse(
            text: $text,
            provider: $this->name(),
            model: $this->model(),
            promptTokens: (int) ceil(mb_strlen($question) / 4),
            completionTokens: (int) ceil(mb_strlen($text) / 4),
            latencyMs: (int) round((microtime(true) - $started) * 1000),
        );
    }

    /**
     * @return Generator<int, string>
     */
    public function stream(LlmRequest $request): Generator
    {
        foreach (mb_str_split($this->respondTo($request->lastUserMessage()), 24) as $chunk) {
            yield $chunk;
        }
    }

    private function respondTo(string $question): string
    {
        $haystack = mb_strtolower($question);
        // `Lang::get()` takes an array of replacements as its second argument,
        // so it is left out here rather than defaulted to a string.
        $intents = Lang::get('assistant.intents', []);

        foreach ($intents as $intent) {
            foreach ((array) ($intent['keywords'] ?? []) as $keyword) {
                if (str_contains($haystack, mb_strtolower((string) $keyword))) {
                    return (string) ($intent['answer'] ?? '');
                }
            }
        }

        return (string) Lang::get('assistant.intents_fallback', []);
    }
}
