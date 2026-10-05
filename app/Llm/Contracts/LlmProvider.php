<?php

declare(strict_types=1);

namespace App\Llm\Contracts;

use App\Llm\Exceptions\LlmException;
use Generator;

/**
 * A model backend. Implementations translate a request to one vendor and back,
 * and are responsible only for that translation: caching, retries, fallbacks
 * and circuit breaking live in the gateway above.
 */
interface LlmProvider
{
    /**
     * Short stable identifier, recorded on every run.
     */
    public function name(): string;

    /**
     * Default model identifier for this backend.
     */
    public function model(): string;

    /**
     * Price list used for cost accounting.
     *
     * @return array{input_per_mtok: float, output_per_mtok: float}
     */
    public function pricing(): array;

    /**
     * Whether this backend can serve traffic at all. A backend without
     * credentials reports false and is skipped by the fallback chain.
     */
    public function isAvailable(): bool;

    /**
     * Execute a single non-streaming completion.
     *
     * @throws LlmException
     */
    public function complete(LlmRequest $request): LlmResponse;

    /**
     * Execute a streaming completion, yielding text deltas.
     *
     * @return Generator<int, string>
     *
     * @throws LlmException
     */
    public function stream(LlmRequest $request): Generator;
}
