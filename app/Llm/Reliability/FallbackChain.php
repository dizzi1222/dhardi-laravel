<?php

declare(strict_types=1);

namespace App\Llm\Reliability;

use App\Llm\Contracts\LlmProvider;

/**
 * An ordered list of backends, tried in order.
 *
 * Ordering encodes preference: the strongest model first, a cheaper one second,
 * and a deterministic local responder last so that a user-visible feature
 * degrades into a lesser answer rather than into an error page.
 */
final readonly class FallbackChain
{
    /** @var array<int, LlmProvider> */
    private array $providers;

    /**
     * @param  array<int, LlmProvider>  $providers
     */
    public function __construct(array $providers)
    {
        $this->providers = array_values($providers);
    }

    /**
     * @return array<int, LlmProvider>
     */
    public function all(): array
    {
        return $this->providers;
    }

    /**
     * Backends that report themselves usable, in order.
     *
     * @return array<int, LlmProvider>
     */
    public function available(): array
    {
        return array_values(array_filter(
            $this->providers,
            static fn (LlmProvider $provider): bool => $provider->isAvailable(),
        ));
    }
}
