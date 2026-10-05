<?php

declare(strict_types=1);

namespace App\Assistant;

/**
 * Outcome of a golden-dataset run.
 */
final readonly class EvalReport
{
    /**
     * @param  array<int, array{key: string, passed: bool, latency_ms: int, provider: string, model: string}>  $cases
     */
    public function __construct(
        public array $cases,
        public int $passed,
        public int $failed,
        public int $totalLatencyMs,
    ) {}

    public function passRate(): float
    {
        $total = $this->passed + $this->failed;

        return $total === 0 ? 0.0 : round($this->passed / $total, 4);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'passed' => $this->passed,
            'failed' => $this->failed,
            'pass_rate' => $this->passRate(),
            'total_latency_ms' => $this->totalLatencyMs,
            'cases' => $this->cases,
        ];
    }
}
