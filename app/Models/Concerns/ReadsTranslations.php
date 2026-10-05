<?php

declare(strict_types=1);

namespace App\Models\Concerns;

/**
 * Resolves a translated attribute for the active locale, falling back through
 * a configurable chain instead of returning null.
 *
 * A missing German translation must degrade to English rather than blank out
 * a section of the page for the reader who matters most.
 *
 * Two accessors rather than one, because the return types genuinely differ: a
 * summary is a string and a bullet list is an array. A single untyped getter
 * would have produced a `TypeError` the first time a list was requested
 * through it.
 */
trait ReadsTranslations
{
    /**
     * Translation rows for this model, keyed by locale.
     *
     * @return array<string, object>
     */
    abstract protected function translationMap(): array;

    /**
     * The locale to fall back to when the active one is unavailable.
     */
    abstract protected function fallbackLocale(): string;

    /**
     * A scalar translated field. Returns null when the stored value is not a
     * string, so a caller asking for the wrong field gets null rather than a
     * fatal error.
     */
    public function translate(string $field, ?string $locale = null): ?string
    {
        $value = $this->raw($field, $locale);

        return is_string($value) ? $value : null;
    }

    /**
     * A list translated field, empty when absent.
     *
     * @return array<int, string>
     */
    public function translateList(string $field, ?string $locale = null): array
    {
        /** @var array<int, string> $list */
        $list = (array) $this->raw($field, $locale);

        return array_values($list);
    }

    private function raw(string $field, ?string $locale = null): mixed
    {
        $locale ??= app()->getLocale();
        $map = $this->translationMap();

        $value = $map[$locale]->{$field} ?? null;

        if (blank($value) && $locale !== $this->fallbackLocale()) {
            $value = $map[$this->fallbackLocale()]->{$field} ?? null;
        }

        return $value;
    }
}
