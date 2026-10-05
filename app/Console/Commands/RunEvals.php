<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Assistant\EvalReport;
use App\Assistant\EvalRunner;
use App\Models\LlmEvalCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Replays the golden dataset against the live chain.
 *
 * Exits non-zero when a case fails, which is the whole point: it makes the
 * command usable as a CI gate rather than as something a developer runs when
 * they already suspect a problem.
 */
final class RunEvals extends Command
{
    protected $signature = 'evals:run
        {--all : Include cases marked inactive}
        {--key= : Run a single case by its key}
        {--show : Print the full answer for each case}';

    protected $description = 'Run the assistant golden dataset against the live model chain';

    public function handle(EvalRunner $runner): int
    {
        $key = $this->option('key');

        if ($key !== null && LlmEvalCase::query()->where('key', $key)->doesntExist()) {
            $this->components->error("No eval case with key [{$key}].");

            return self::FAILURE;
        }

        $report = $runner->run(onlyActive: ! $this->option('all') && $key === null);

        if ($key !== null) {
            $report = $this->onlyCase($report, $key);
        }

        $this->newLine();

        foreach ($report->cases as $case) {
            $marker = $case['passed'] ? '<fg=green>PASS</>' : '<fg=red>FAIL</>';

            $this->line(sprintf(
                '  %s  %-24s %-16s %5d ms',
                $marker,
                $case['key'],
                $case['provider'].'/'.substr($case['model'], 0, 12),
                $case['latency_ms'],
            ));

            if ($this->option('show') || ! $case['passed']) {
                $this->line('        '.$this->preview($case));
            }
        }

        $this->newLine();

        $this->components->twoColumnDetail('cases', (string) ($report->passed + $report->failed));
        $this->components->twoColumnDetail('passed', (string) $report->passed);
        $this->components->twoColumnDetail('failed', (string) $report->failed);
        $this->components->twoColumnDetail('pass rate', number_format($report->passRate() * 100, 1).' %');
        $this->components->twoColumnDetail('total latency', $report->totalLatencyMs.' ms');

        if ($report->failed > 0) {
            $this->newLine();
            $this->components->error(
                $report->failed.' of '.($report->passed + $report->failed).' cases failed. '
                .'A prompt or model change most likely caused it.',
            );

            return self::FAILURE;
        }

        $this->newLine();
        $this->components->info('All cases passed.');

        return self::SUCCESS;
    }

    private function onlyCase(EvalReport $report, string $key): EvalReport
    {
        $cases = array_values(array_filter(
            $report->cases,
            static fn (array $case): bool => $case['key'] === $key,
        ));

        return new EvalReport(
            cases: $cases,
            passed: count(array_filter($cases, static fn (array $case): bool => $case['passed'])),
            failed: count(array_filter($cases, static fn (array $case): bool => ! $case['passed'])),
            totalLatencyMs: (int) array_sum(array_column($cases, 'latency_ms')),
        );
    }

    /**
     * The stored output of the most recent run of a case, for context when one
     * fails. Reading it rather than re-running keeps the failure report free.
     */
    private function preview(array $case): string
    {
        $output = DB::table('llm_eval_results')
            ->where('llm_eval_case_id', DB::table('llm_eval_cases')->where('key', $case['key'])->value('id'))
            ->latest('id')
            ->value('output');

        return trim(mb_substr((string) $output, 0, 220));
    }
}
