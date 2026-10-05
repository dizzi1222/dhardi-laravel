import { useEffect, useRef, useState } from 'react';
import { useCompanion } from '../lib/useCompanion';
import { useList, useT } from '../lib/i18n';

function formatDuration(ms: number): string {
    return ms < 1000 ? `${ms} ms` : `${(ms / 1000).toFixed(2)} s`;
}

function formatCost(usd: number): string {
    if (usd === 0) {
        return '$0.00';
    }

    return usd < 0.01 ? `$${usd.toFixed(5)}` : `$${usd.toFixed(4)}`;
}

/**
 * The assistant, live.
 *
 * The provenance line under every answer is the part that matters: the model,
 * the latency and whether the reply came from cache are all shown, because an
 * assistant that hides which model answered is asking for trust it has not
 * earned.
 */
export default function AssistantSection() {
    const t = useT();
    const readList = useList();
    const suggestions = readList('ui.assistant.suggestions').filter(
        (item): item is string => typeof item === 'string',
    );

    const { turns, meta, state, error, send, reset } = useCompanion();

    const [draft, setDraft] = useState('');
    const transcript = useRef<HTMLDivElement>(null);

    const busy = state === 'streaming';
    const last = turns.at(-1);
    const waitingForFirstToken = busy && last?.role === 'assistant' && last.text === '';

    // Keep the newest text in view while tokens arrive.
    useEffect(() => {
        transcript.current?.scrollTo({ top: transcript.current.scrollHeight, behavior: 'smooth' });
    }, [turns]);

    function submit(event: React.FormEvent): void {
        event.preventDefault();

        const question = draft;

        setDraft('');
        void send(question);
    }

    return (
        <section id="assistant" className="rule scroll-mt-24 py-16 sm:py-24">
            <div className="shell grid gap-8 lg:grid-cols-[8rem_1fr]">
                <div className="lg:pt-1">
                    <span className="tabular text-sm text-ink-faint">06</span>
                </div>

                <div className="min-w-0">
                    <p className="eyebrow">{t('ui.assistant.eyebrow')}</p>
                    <h2 className="mt-3 text-2xl font-semibold sm:text-3xl">
                        {t('ui.assistant.heading')}
                    </h2>
                    <p className="mt-4 max-w-2xl text-base leading-relaxed text-ink-soft">
                        {t('ui.assistant.subtitle')}
                    </p>

                    <div className="mt-10 overflow-hidden rounded-lg border border-rule bg-paper-raised">
                        <div
                            ref={transcript}
                            className="scroll-quiet max-h-96 space-y-5 overflow-y-auto p-5 sm:p-6"
                            aria-live="polite"
                            aria-busy={busy}
                        >
                            {turns.length === 0 ? (
                                <p className="text-sm text-ink-mute">{t('ui.assistant.empty')}</p>
                            ) : null}

                            {turns.map((turn, index) => (
                                <div key={`${turn.role}-${index}`}>
                                    <p className="eyebrow mb-1.5">
                                        {turn.role === 'user' ? 'Du / You' : 'Assistent'}
                                    </p>
                                    <p className="text-sm leading-relaxed whitespace-pre-wrap text-ink">
                                        {turn.text}
                                        {waitingForFirstToken && index === turns.length - 1 ? (
                                            <span className="ml-0.5 inline-block animate-pulse">▍</span>
                                        ) : null}
                                    </p>
                                </div>
                            ))}
                        </div>

                        {meta ? (
                            <div className="flex flex-wrap gap-x-5 gap-y-1 border-t border-rule-soft bg-paper px-5 py-2.5 text-xs text-ink-mute">
                                <span>
                                    {t('ui.assistant.meta_provider')}:{' '}
                                    <span className="tabular text-ink-soft">
                                        {meta.provider}/{meta.model}
                                    </span>
                                </span>
                                <span>
                                    {t('ui.assistant.meta_latency')}:{' '}
                                    <span className="tabular text-ink-soft">
                                        {formatDuration(meta.latencyMs)}
                                    </span>
                                </span>
                                {meta.cached ? (
                                    <span className="text-verify">
                                        {t('ui.assistant.meta_cached')}
                                    </span>
                                ) : null}
                                <span>
                                    {t('ui.assistant.meta_cost')}:{' '}
                                    <span className="tabular text-ink-soft">
                                        {formatCost(meta.costUsd)}
                                    </span>
                                </span>
                            </div>
                        ) : null}

                        {error ? (
                            <p className="border-t border-rule-soft bg-signal-soft px-5 py-2.5 text-xs text-signal">
                                {error}
                            </p>
                        ) : null}

                        {turns.length > 0 ? (
                            <div className="border-t border-rule-soft px-5 py-2">
                                <button
                                    type="button"
                                    onClick={reset}
                                    className="text-xs text-ink-mute underline-offset-4 hover:text-ink hover:underline"
                                >
                                    {t('ui.assistant.clear')}
                                </button>
                            </div>
                        ) : (
                            <ul className="flex flex-wrap gap-2 border-t border-rule-soft p-4">
                                {suggestions.map((suggestion) => (
                                    <li key={suggestion}>
                                        <button
                                            type="button"
                                            onClick={() => void send(suggestion)}
                                            className="rounded-full border border-rule px-3 py-1.5 text-xs text-ink-soft transition-colors hover:border-ink hover:text-ink"
                                        >
                                            {suggestion}
                                        </button>
                                    </li>
                                ))}
                            </ul>
                        )}

                        <form onSubmit={submit} className="flex gap-2 border-t border-rule p-3">
                            <input
                                type="text"
                                value={draft}
                                onChange={(event) => setDraft(event.target.value)}
                                placeholder={t('ui.assistant.placeholder')}
                                maxLength={2000}
                                disabled={busy}
                                aria-label={t('ui.assistant.placeholder')}
                                className="min-w-0 flex-1 rounded-md border border-rule bg-paper px-3 py-2.5 text-sm placeholder:text-ink-faint focus:border-ink focus:outline-none disabled:opacity-60"
                            />

                            <button
                                type="submit"
                                disabled={busy || draft.trim() === ''}
                                className="shrink-0 rounded-md bg-ink px-4 py-2.5 text-sm font-medium text-paper transition-opacity hover:opacity-88 disabled:opacity-40"
                            >
                                {busy ? t('ui.assistant.sending') : t('ui.assistant.send')}
                            </button>
                        </form>
                    </div>

                    <p className="mt-4 max-w-2xl text-xs leading-relaxed text-ink-mute">
                        {t('ui.assistant.disclaimer')}
                    </p>
                </div>
            </div>
        </section>
    );
}