<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Runs a write that the request does not depend on.
 *
 * Two places in this application write to the database for reasons that are
 * about *observing* rather than *serving*:
 *
 *  - the audit row written for every model attempt,
 *  - the transcript of a conversation.
 *
 * Neither is part of the answer the visitor receives. On a platform with no
 * durable disk, or when a database is briefly unreachable, a failure to record
 * them must not turn a working answer into a 500. Losing an audit row is a
 * smaller problem than losing the feature, so these writes are best-effort by
 * design and the failure is logged rather than thrown.
 *
 * What is *not* best-effort is the read path: content, translations and the
 * tenant lookup all have to work, because without them there is no page.
 */
final class BestEffort
{
    /**
     * @param  string  $context  Short label used in the log line.
     * @param  callable(): mixed  $write
     * @return mixed|null The value written, or null when the write failed.
     */
    public static function write(string $context, callable $write): mixed
    {
        try {
            return $write();
        } catch (Throwable $e) {
            Log::warning('best_effort.failed', [
                'context' => $context,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
