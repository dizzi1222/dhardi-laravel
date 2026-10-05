<?php

declare(strict_types=1);

namespace App\Assistant;

use App\Llm\Contracts\LlmRequest;
use App\Llm\Contracts\LlmResponse;
use App\Llm\Exceptions\LlmException;
use App\Llm\LlmGateway;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Project;
use App\Support\BestEffort;
use Generator;
use Illuminate\Support\Str;

/**
 * The companion as a product surface: history, prompt assembly, persistence and
 * accounting. Transport-agnostic, so the same logic serves the blocking
 * endpoint and the stream.
 *
 * Grounding data comes from the same tables the portfolio renders from, so the
 * assistant cannot describe a project the site does not show, and a content
 * edit needs no second update in a prompt file.
 */
final class CompanionService
{
    private const HISTORY_LIMIT = 8;

    private ?Conversation $activeConversation = null;

    private ?CompanionAnswer $pendingStream = null;

    public function __construct(
        private readonly LlmGateway $gateway,
        private readonly IntentDetector $intents,
    ) {}

    /**
     * @throws LlmException
     */
    public function ask(string $question, ?string $conversationPublicId = null): CompanionAnswer
    {
        $conversation = $this->resolveConversation($conversationPublicId, $question);
        $request = $this->buildRequest($conversation, $question);

        $response = $this->gateway->complete($request, $conversation->getKey());

        $answer = $this->toAnswer($response, $conversation);

        $this->persist($conversation, $answer);

        return $answer;
    }

    /**
     * Streaming variant. Yields text deltas and accumulates them internally, so
     * the transcript and the accounting can be finalised once the last byte has
     * reached the client.
     *
     * @return Generator<int, string>
     */
    public function stream(string $question, ?string $conversationPublicId = null): Generator
    {
        $conversation = $this->resolveConversation($conversationPublicId, $question);
        $this->activeConversation = $conversation;
        $this->pendingStream = null;

        $request = $this->buildRequest($conversation, $question);
        $started = microtime(true);

        $buffer = '';

        foreach ($this->gateway->stream($request, $conversation->getKey()) as $delta) {
            $buffer .= $delta;

            yield $delta;
        }

        $served = $this->gateway->lastStreamingProvider();

        $this->pendingStream = new CompanionAnswer(
            text: trim($buffer),
            provider: $served?->name() ?? 'none',
            model: $served?->model() ?? 'none',
            intent: (string) ($conversation->intent ?? IntentDetector::FALLBACK),
            conversationId: (string) $conversation->public_id,
            latencyMs: (int) round((microtime(true) - $started) * 1000),
            cached: false,
            costUsd: 0.0,
            promptTokens: 0,
            completionTokens: (int) ceil(mb_strlen($buffer) / 4),
        );
    }

    /**
     * Persists a fully streamed answer and returns it, so the caller can close
     * the response with an honest summary of what was produced.
     */
    public function finaliseStream(): ?CompanionAnswer
    {
        $answer = $this->pendingStream;

        if ($answer === null || $this->activeConversation === null) {
            return null;
        }

        $this->persist($this->activeConversation, $answer);

        $this->pendingStream = null;
        $this->activeConversation = null;

        return $answer;
    }

    // Assembly

    private function resolveConversation(?string $publicId, string $question): Conversation
    {
        $locale = app()->getLocale();

        $conversation = $publicId === null
            ? null
            : Conversation::query()->where('public_id', $publicId)->first();

        // A conversation is only continued in the language it started in;
        // splicing transcripts across locales produces incoherent history.
        if ($conversation === null || $conversation->locale !== $locale) {
            $conversation = $this->openConversation($locale, $question);
        } elseif ($conversation->intent === null) {
            BestEffort::write('conversation.intent', function () use ($conversation, $question): void {
                $conversation->update(['intent' => $this->intents->detect($question)]);
            });
        }

        $this->record($conversation, [
            'role' => 'user',
            'content' => mb_substr($question, 0, 4000),
        ]);

        return $conversation;
    }

    /**
     * A conversation row is required by the persistence path, so this one is
     * not best-effort. When the database cannot take it — a platform with no
     * durable disk, for instance — an unsaved stand-in keeps the feature working
     * for the length of the request, and the caller still gets an answer.
     */
    private function openConversation(string $locale, string $question): Conversation
    {
        $saved = BestEffort::write(
            'conversation.open',
            fn (): Conversation => Conversation::query()->create([
                'locale' => $locale,
                'intent' => $this->intents->detect($question),
            ]),
        );

        if ($saved instanceof Conversation) {
            return $saved;
        }

        return new Conversation([
            'locale' => $locale,
            'intent' => $this->intents->detect($question),
            'public_id' => (string) Str::uuid(),
        ]);
    }

    private function buildRequest(Conversation $conversation, string $question): LlmRequest
    {
        $messages = $conversation->messages()
            ->latest('id')
            ->limit(self::HISTORY_LIMIT)
            ->get(['role', 'content'])
            ->reverse()
            ->map(static fn (Message $message): array => [
                'role' => $message->role,
                'content' => $message->content,
            ])
            ->all();

        return new LlmRequest(
            messages: $messages,
            system: $this->systemPrompt(),
            maxTokens: 600,
            // Low on purpose: this answers factual questions about a CV, so
            // variance buys nothing and costs reproducibility.
            temperature: 0.1,
            timeoutSeconds: 15.0,
        );
    }

    /**
     * Assembled from live content rather than transcribed into a prompt file.
     */
    private function systemPrompt(): string
    {
        $projects = Project::query()
            ->where('is_featured', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Project $project): string => '- '.$project->name.' ('.$project->year.')')
            ->implode("\n");

        return (string) trans('assistant.system', [
            'projects' => $projects,
            'stack' => (string) trans('assistant.stack_line'),
            'maxChars' => (int) config('llm.guardrails.max_output_chars'),
        ]);
    }

    private function persist(Conversation $conversation, CompanionAnswer $answer): void
    {
        $this->record($conversation, [
            'role' => 'assistant',
            'content' => $answer->text,
            'meta' => [
                'provider' => $answer->provider,
                'model' => $answer->model,
                'cached' => $answer->cached,
            ],
        ]);
    }

    /**
     * Appends a transcript row, tolerating a database that cannot take it.
     *
     * The conversation id is null on an unsaved stand-in, which is exactly the
     * condition under which the write is skipped rather than attempted.
     */
    private function record(Conversation $conversation, array $message): void
    {
        $conversationId = $conversation->getKey();

        if ($conversationId === null) {
            return;
        }

        BestEffort::write('conversation.message', fn (): Message => Message::query()->create(
            array_merge($message, ['conversation_id' => $conversationId]),
        ));
    }

    private function toAnswer(LlmResponse $response, Conversation $conversation): CompanionAnswer
    {
        return new CompanionAnswer(
            text: $response->text,
            provider: $response->provider,
            model: $response->model,
            intent: (string) ($conversation->intent ?? IntentDetector::FALLBACK),
            conversationId: (string) $conversation->public_id,
            latencyMs: $response->latencyMs,
            cached: $response->wasCached,
            costUsd: $response->costUsd($this->pricingFor($response->provider)),
            promptTokens: $response->promptTokens,
            completionTokens: $response->completionTokens,
        );
    }

    /**
     * @return array{input_per_mtok?: float, output_per_mtok?: float}
     */
    private function pricingFor(string $provider): array
    {
        $map = [
            'anthropic' => 'llm.providers.anthropic',
            'openrouter' => 'llm.providers.openrouter',
        ];

        $key = $map[$provider] ?? null;

        return $key === null ? [] : (array) config($key);
    }
}
