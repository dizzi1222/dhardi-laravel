<?php

declare(strict_types=1);

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Controller;
use App\Models\LlmEvalCase;
use App\Models\LlmRun;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

/**
 * Live operational figures for the model layer.
 *
 * Every number here is a query against `llm_runs`, the same table the gateway
 * writes on every attempt. Nothing is hard-coded and nothing is estimated, so
 * the panel cannot drift from what actually happened — which is the only
 * reason to show it at all.
 *
 * Re-running the golden dataset is deliberately *not* part of a page load:
 * that is an explicit `php artisan evals:run`, because a telemetry endpoint
 * that spends money on every request is a bug, not a feature.
 */
final class ReliabilityController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $runs = LlmRun::query()->get([
            'provider', 'outcome', 'latency_ms', 'cost_usd', 'was_cached', 'created_at',
        ]);

        $total = $runs->count();

        $latencies = $runs
            ->pluck('latency_ms')
            ->sort()
            ->values();

        return response()->json([
            'window' => [
                'total_runs' => $total,
                'since' => $runs->min('created_at'),
            ],

            'rates' => [
                'success' => $this->rate($runs, fn (LlmRun $r) => $r->outcome === LlmRun::OUTCOME_SUCCESS),
                'cache_hit' => $this->rate($runs, fn (LlmRun $r) => $r->was_cached),
                'blocked' => $this->rate($runs, fn (LlmRun $r) => $r->outcome === LlmRun::OUTCOME_BLOCKED),
                'fallback' => $this->rate(
                    $runs,
                    fn (LlmRun $r) => $r->provider !== 'anthropic' && $r->provider !== 'none',
                ),
            ],

            'latency' => [
                'p50_ms' => $this->percentile($latencies, 0.50),
                'p95_ms' => $this->percentile($latencies, 0.95),
                'max_ms' => $latencies->max(),
            ],

            'cost' => [
                'total_usd' => round((float) $runs->sum('cost_usd'), 6),
                'avg_usd' => $total === 0 ? 0.0 : round((float) $runs->avg('cost_usd'), 6),
            ],

            'by_provider' => $runs
                ->groupBy('provider')
                ->map(fn ($group, $provider) => [
                    'runs' => $group->count(),
                    'success_rate' => round(
                        $group->where('outcome', LlmRun::OUTCOME_SUCCESS)->count() / max(1, $group->count()),
                        4,
                    ),
                ])
                ->all(),

            'evals' => $this->latestEvalSummary(),
        ]);
    }

    /**
     * Most recent stored result per golden case, plus the pass rate of the run
     * they belong to. Reading history rather than recomputing keeps the endpoint
     * read-only and free.
     *
     * @return array<string, mixed>
     */
    private function latestEvalSummary(): array
    {
        $cases = LlmEvalCase::query()
            ->with(['results' => fn ($query) => $query->latest('id')->limit(1)])
            ->get();

        $results = $cases->map(fn (LlmEvalCase $case) => $case->results->first())->filter();

        return [
            'cases' => $cases->count(),
            'measured' => $results->count(),
            'pass_rate' => $results->isEmpty()
                ? null
                : round($results->filter(fn ($r) => (bool) $r->passed)->count() / $results->count(), 4),
            'last_run_at' => $results->max('created_at'),
        ];
    }

    /**
     * @param  Collection<int, LlmRun>  $runs
     * @param  callable(LlmRun): bool  $predicate
     */
    private function rate(Collection $runs, callable $predicate): float
    {
        if ($runs->isEmpty()) {
            return 0.0;
        }

        return round($runs->filter($predicate)->count() / $runs->count(), 4);
    }

    /**
     * Nearest-rank percentile over an already sorted collection.
     *
     * @param  Collection<int, int>  $sorted
     */
    private function percentile(Collection $sorted, float $quantile): int
    {
        if ($sorted->isEmpty()) {
            return 0;
        }

        $index = (int) ceil($quantile * $sorted->count()) - 1;

        return (int) $sorted->max(0, min($index, $sorted->count() - 1));
    }
}
