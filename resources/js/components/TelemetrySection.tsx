import { useEffect, useState } from 'react';
import { useT } from '../lib/i18n';
import type { Telemetry } from '../types';

function percent(value: number): string {
    return `${(value * 100).toFixed(1)} %`;
}

function shortDate(value: string | null): string {
    if (value === null) {
        return '—';
    }

    const parsed = new Date(value);

    return Number.isNaN(parsed.getTime()) ? '—' : parsed.toISOString().slice(0, 16).replace('T', ' ');
}

/**
 * Operational figures, straight from the run table.
 *
 * Deliberately read-only: re-running the golden dataset costs money, so it
 * happens on a schedule via `php artisan evals:run`, not because a visitor
 * opened a page.
 */
export default function TelemetrySection() {
    const t = useT();

    const [data, setData] = useState<Telemetry | null>(null);
    const [failed, setFailed] = useState(false);

    useEffect(() => {
        const controller = new AbortController();

        fetch('/api/assistant/telemetry', {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            signal: controller.signal,
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                return response.json() as Promise<Telemetry>;
            })
            .then(setData)
            .catch(() => {
                if (!controller.signal.aborted) {
                    setFailed(true);
                }
            });

        return () => controller.abort();
    }, []);

    const metrics: Array<[string, string]> = data
        ? [
            [t('ui.telemetry.metrics.success'), percent(data.rates.success ?? 0)],
            [t('ui.telemetry.metrics.cache_hit'), percent(data.rates.cache_hit ?? 0)],
            [t('ui.telemetry.metrics.blocked'), percent(data.rates.blocked ?? 0)],
            [t('ui.telemetry.metrics.fallback'), percent(data.rates.fallback ?? 0)],
            [t('ui.telemetry.metrics.p50'), `${data.latency.p50_ms ?? 0} ms`],
            [t('ui.telemetry.metrics.p95'), `${data.latency.p95_ms ?? 0} ms`],
            [t('ui.telemetry.metrics.max'), `${data.latency.max_ms ?? 0} ms`],
            [t('ui.telemetry.metrics.cost_total'), `$${Number(data.cost.total_usd ?? 0).toFixed(5)}`],
        ]
        : [];

    return (
        <section id="telemetry" className="rule scroll-mt-24 py-16 sm:py-20">
            <div className="shell">
                <p className="eyebrow">{t('ui.telemetry.eyebrow')}</p>
                <h2 className="mt-3 text-xl font-semibold sm:text-2xl">
                    {t('ui.telemetry.heading')}
                </h2>
                <p className="mt-3 max-w-2xl text-sm leading-relaxed text-ink-soft">
                    {t('ui.telemetry.subtitle')}
                </p>

                {failed ? (
                    <p className="mt-6 text-sm text-signal">{t('ui.common.loading')}</p>
                ) : null}

                {!data && !failed ? (
                    <div className="mt-6 grid grid-cols-2 gap-px overflow-hidden rounded-lg border border-rule bg-rule sm:grid-cols-4">
                        {Array.from({ length: 8 }).map((_, index) => (
                            <div key={index} className="h-20 animate-pulse bg-paper-raised" />
                        ))}
                    </div>
                ) : null}

                {data && data.window.total_runs === 0 ? (
                    <p className="mt-6 text-sm text-ink-mute">{t('ui.telemetry.empty')}</p>
                ) : null}

                {data && data.window.total_runs > 0 ? (
                    <>
                        <div className="mt-6 grid grid-cols-2 gap-px overflow-hidden rounded-lg border border-rule bg-rule sm:grid-cols-4">
                            {metrics.map(([label, value]) => (
                                <div key={label} className="bg-paper-raised p-4">
                                    <p className="eyebrow">{label}</p>
                                    <p className="tabular mt-1.5 text-lg font-semibold">{value}</p>
                                </div>
                            ))}
                        </div>

                        <div className="mt-4 grid gap-4 lg:grid-cols-2">
                            <div className="rounded-lg border border-rule bg-paper-raised p-5">
                                <p className="text-sm font-medium">{t('ui.telemetry.evals.heading')}</p>
                                <p className="mt-2 text-xs leading-relaxed text-ink-mute">
                                    {t('ui.telemetry.evals.note')}
                                </p>

                                <dl className="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
                                    <div>
                                        <dt className="eyebrow">
                                            {t('ui.telemetry.evals.cases')}
                                        </dt>
                                        <dd className="tabular mt-1 text-sm font-medium">
                                            {data.evals.cases}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt className="eyebrow">
                                            {t('ui.telemetry.evals.measured')}
                                        </dt>
                                        <dd className="tabular mt-1 text-sm font-medium">
                                            {data.evals.measured}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt className="eyebrow">
                                            {t('ui.telemetry.evals.pass_rate')}
                                        </dt>
                                        <dd className="tabular mt-1 text-sm font-medium">
                                            {data.evals.pass_rate === null
                                                ? '—'
                                                : percent(data.evals.pass_rate)}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt className="eyebrow">
                                            {t('ui.telemetry.evals.last_run')}
                                        </dt>
                                        <dd className="tabular mt-1 text-sm font-medium">
                                            {shortDate(data.evals.last_run_at)}
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            <div className="rounded-lg border border-rule bg-paper-raised p-5">
                                <p className="text-sm font-medium">
                                    {t('ui.telemetry.runs')}:{' '}
                                    <span className="tabular">{data.window.total_runs}</span>
                                    <span className="ml-2 text-xs text-ink-mute">
                                        {t('ui.telemetry.since')} {shortDate(data.window.since)}
                                    </span>
                                </p>

                                <ul className="mt-4 space-y-2">
                                    {Object.entries(data.by_provider).map(([provider, stats]) => (
                                        <li
                                            key={provider}
                                            className="flex items-baseline justify-between gap-4 text-sm"
                                        >
                                            <span className="tabular text-ink-soft">{provider}</span>
                                            <span className="tabular text-xs text-ink-mute">
                                                {stats.runs} · {percent(stats.success_rate)}
                                            </span>
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        </div>
                    </>
                ) : null}
            </div>
        </section>
    );
}