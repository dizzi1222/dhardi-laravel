import Section from './Section';
import { useT } from '../lib/i18n';
import type { FitItem } from '../types';

interface FitSectionProps {
    items: FitItem[];
    intro: string;
}

/**
 * The requirement mapping, including the two items that are only partly met.
 *
 * Showing the gaps next to the evidence is a deliberate choice. A reader who
 * finds the German level overstated here will assume everything else is too —
 * and they would be right to.
 */
export default function FitSection({ items, intro }: FitSectionProps) {
    const t = useT();

    return (
        <Section
            index="01"
            id="fit"
            eyebrow={t('ui.sections.fit.eyebrow')}
            heading={t('ui.sections.fit.heading')}
            subtitle={intro}
        >
            <ul className="space-y-4">
                {items.map((item) => {
                    const addressed = item.status === 'addressed';

                    return (
                        <li
                            key={item.requirement}
                            className="rounded-lg border border-rule bg-paper-raised p-5 sm:p-6"
                        >
                            <div className="flex flex-wrap items-start justify-between gap-x-6 gap-y-2">
                                <h3 className="max-w-2xl text-base font-medium text-ink">
                                    {item.requirement}
                                </h3>

                                <span
                                    className={`tag shrink-0 ${
                                        addressed
                                            ? 'border-verify/40 bg-verify-soft text-verify'
                                            : 'border-warn/40 bg-warn-soft text-warn'
                                    }`}
                                >
                                    {t(`fit.legend.${item.status}`)}
                                </span>
                            </div>

                            <p className="mt-3 max-w-3xl text-sm leading-relaxed text-ink-soft">
                                {item.evidence}
                            </p>

                            {item.proof.length > 0 ? (
                                <dl className="mt-4 flex flex-wrap gap-x-8 gap-y-3">
                                    {item.proof.map((entry) => (
                                        <div key={entry.label}>
                                            <dt className="eyebrow">{entry.label}</dt>
                                            <dd className="tabular mt-1 text-sm font-medium">
                                                {entry.value}
                                            </dd>
                                        </div>
                                    ))}
                                </dl>
                            ) : null}
                        </li>
                    );
                })}
            </ul>
        </Section>
    );
}