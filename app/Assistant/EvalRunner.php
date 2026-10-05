<?php

declare(strict_types=1);

namespace App\Assistant;

use App\Llm\Contracts\LlmRequest;
use App\Llm\LlmGateway;
use App\Models\LlmEvalCase;
use App\Models\LlmEvalResult;

/**
 * Replays the golden dataset against the live chain.
 *
 * A model change, a prompt change or a provider rotation is a silent
 * regression until something measures it. This turns "the assistant still
 * behaves" from a feeling into a command that exits non-zero, which is what
 * makes it usable in CI rather than as a console curiosity.
 *
 * Results are written per case so that two runs can be compared over time.
 */
final class EvalRunner
{
    public function __construct(
        private readonly LlmGateway $gateway,
        private readonly IntentDetector $intents,
    ) {}

    public function run(bool $onlyActive = true): EvalReport
    {
        $query = LlmEvalCase::query()->orderBy('id');

        if ($onlyActive) {
            $query->where('is_active', true);
        }

        $cases = [];
        $passed = 0;
        $failed = 0;
        $latency = 0;

        foreach ($query->get() as $case) {
            $result = $this->evaluate($case);

            $cases[] = $result;
            $latency += $result['latency_ms'];

            $result['passed'] ? $passed++ : $failed++;
        }

        return new EvalReport(
            cases: $cases,
            passed: $passed,
            failed: $failed,
            totalLatencyMs: $latency,
        );
    }

    /**
     * @return array{key: string, passed: bool, latency_ms: int, provider: string, model: string}
     */
    private function evaluate(LlmEvalCase $case): array
    {
        $previousLocale = app()->getLocale();

        app()->setLocale($case->locale);

        try {
            $response = $this->gateway->complete(new LlmRequest(
                messages: [['role' => 'user', 'content' => $case->prompt]],
                system: (string) trans('assistant.system', [
                    'projects' => '',
                    'stack' => (string) trans('assistant.stack_line'),
                    'maxChars' => (int) config('llm.guardrails.max_output_chars'),
                ]),
                maxTokens: 400,
                temperature: 0.0,
            ));

            $passed = $case->passes($response->text);

            LlmEvalResult::query()->create([
                'llm_eval_case_id' => $case->getKey(),
                'provider' => $response->provider,
                'model' => $response->model,
                'passed' => $passed,
                'latency_ms' => $response->latencyMs,
                'output' => mb_substr($response->text, 0, 2000),
            ]);

            return [
                'key' => $case->key,
                'passed' => $passed,
                'latency_ms' => $response->latencyMs,
                'provider' => $response->provider,
                'model' => $response->model,
            ];
        } finally {
            app()->setLocale($previousLocale);
        }
    }
}
