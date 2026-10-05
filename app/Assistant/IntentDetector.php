<?php

declare(strict_types=1);

namespace App\Assistant;

use Illuminate\Support\Facades\Lang;

/**
 * Classifies a visitor's question into one of the catalogued intents.
 *
 * Keywords and answers live in the same lang entry on purpose: adding a new
 * topic means editing one file, and the routing decision can never drift from
 * the answer it routes to.
 */
final class IntentDetector
{
    public const FALLBACK = 'fallback';

    public function detect(string $question): string
    {
        $haystack = mb_strtolower($question);

        foreach ($this->intents() as $intent) {
            foreach ((array) ($intent['keywords'] ?? []) as $keyword) {
                if (str_contains($haystack, mb_strtolower((string) $keyword))) {
                    return (string) ($intent['key'] ?? self::FALLBACK);
                }
            }
        }

        return self::FALLBACK;
    }

    /**
     * @return array<int, array{key?: string, keywords?: array<int, string>, answer?: string}>
     */
    private function intents(): array
    {
        return (array) Lang::get('assistant.intents', []);
    }
}
