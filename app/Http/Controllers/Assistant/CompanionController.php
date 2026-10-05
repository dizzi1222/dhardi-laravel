<?php

declare(strict_types=1);

namespace App\Http\Controllers\Assistant;

use App\Assistant\CompanionService;
use App\Http\Controllers\Controller;
use App\Http\Requests\AskCompanionRequest;
use App\Llm\Exceptions\GuardrailViolation;
use App\Llm\Exceptions\LlmUnavailableException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * The visitor-facing assistant endpoints.
 *
 * Both transports share one service. The blocking endpoint exists because it is
 * trivially testable and trivially comparable; the stream exists because a
 * first token arriving in 300ms and a paragraph arriving in 3s are different
 * products.
 */
final class CompanionController extends Controller
{
    public function __construct(private readonly CompanionService $companion) {}

    public function store(AskCompanionRequest $request): JsonResponse
    {
        try {
            $answer = $this->companion->ask(
                question: (string) $request->validated('question'),
                conversationPublicId: $request->validated('conversation_id'),
            );
        } catch (GuardrailViolation $e) {
            return response()->json([
                'message' => trans('assistant.error.blocked', ['rule' => $e->rule]),
                'rule' => $e->rule,
            ], 422);
        } catch (LlmUnavailableException) {
            return response()->json([
                'message' => trans('assistant.error.unavailable'),
            ], 503);
        }

        return response()->json($answer->toArray());
    }

    /**
     * Server-sent events.
     *
     * `X-Accel-Buffering: no` is not decorative: without it a proxy in front of
     * the app will happily hold the whole body and deliver one chunk at the
     * end, which turns a streaming endpoint into a blocking one while every
     * line of the code still looks correct.
     */
    public function stream(AskCompanionRequest $request): StreamedResponse
    {
        $question = (string) $request->validated('question');
        $conversationId = $request->validated('conversation_id');

        return response()->stream(function () use ($question, $conversationId): void {
            $this->emit('meta', ['type' => 'start']);

            try {
                foreach ($this->companion->stream($question, $conversationId) as $delta) {
                    $this->emit('delta', ['text' => $delta]);
                }

                $answer = $this->companion->finaliseStream();

                if ($answer !== null) {
                    $this->emit('done', $answer->toArray());
                }
            } catch (GuardrailViolation $e) {
                $this->emit('error', [
                    'message' => trans('assistant.error.blocked', ['rule' => $e->rule]),
                    'rule' => $e->rule,
                ]);
            } catch (LlmUnavailableException) {
                $this->emit('error', ['message' => trans('assistant.error.unavailable')]);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream; charset=utf-8',
            'Cache-Control' => 'no-cache, no-store, no-transform',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function emit(string $event, array $payload): void
    {
        echo 'event: '.$event."\n";
        echo 'data: '.json_encode($payload, JSON_UNESCAPED_UNICODE)."\n\n";

        if (ob_get_level() > 0) {
            ob_flush();
        }

        flush();
    }
}
