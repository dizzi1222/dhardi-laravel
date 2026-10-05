import Section from './Section';
import { useT } from '../lib/i18n';
import type { ExperienceItem } from '../types';

interface ExperienceSectionProps {
    items: ExperienceItem[];
}

function formatRange(item: ExperienceItem, currentLabel: string): string {
    const start = item.start_date.slice(0, 7);

    return `${start} — ${item.end_date ? item.end_date.slice(0, 7) : currentLabel}`;
}

/**
 * Roles in reverse chronological order.
 *
 * The highlights carry the weight here rather than a job description: what was
 * actually owned is what a hiring manager wants to interrogate.
 */
export default function ExperienceSection({ items }: ExperienceSectionProps) {
    const t = useT();
    const currentLabel = t('ui.sections.experience.current');

    return (
        <Section
            index="02"
            id="experience"
            eyebrow={t('ui.sections.experience.eyebrow')}
            heading={t('ui.sections.experience.heading')}
            subtitle={t('ui.sections.experience.subtitle')}
        >
            <ol className="space-y-10">
                {items.map((item) => (
                    <li key={item.slug} className="relative pl-6 sm:pl-8">
                        <span
                            aria-hidden="true"
                            className="absolute left-0 top-1.5 size-2 rounded-full bg-signal"
                        />

                        <div className="flex flex-wrap items-baseline justify-between gap-x-6 gap-y-1">
                            <h3 className="text-lg font-semibold">{item.role}</h3>

                            <span className="tabular text-xs text-ink-faint">
                                {formatRange(item, currentLabel)} ·{' '}
                                {item.duration_months} {t('ui.sections.experience.months')}
                            </span>
                        </div>

                        <p className="mt-1 text-sm text-ink-soft">
                            {item.organisation_url ? (
                                <a
                                    href={item.organisation_url}
                                    target="_blank"
                                    rel="noreferrer noopener"
                                    className="underline-offset-4 hover:underline"
                                >
                                    {item.organisation}
                                </a>
                            ) : (
                                item.organisation
                            )}

                            {item.location ? ` · ${item.location}` : ''}
                            {item.is_remote ? ` · ${t('ui.sections.experience.remote')}` : ''}
                            {item.employment_type ? ` · ${item.employment_type}` : ''}
                        </p>

                        {item.summary ? (
                            <p className="mt-4 max-w-3xl text-sm leading-relaxed text-ink-soft">
                                {item.summary}
                            </p>
                        ) : null}

                        {item.highlights.length > 0 ? (
                            <>
                                <p className="eyebrow mt-6">
                                    {t('ui.sections.experience.highlights')}
                                </p>
                                <ul className="mt-2 space-y-2">
                                    {item.highlights.map((highlight) => (
                                        <li
                                            key={highlight}
                                            className="flex gap-3 text-sm leading-relaxed text-ink-soft"
                                        >
                                            <span
                                                aria-hidden="true"
                                                className="mt-2 size-1 shrink-0 rounded-full bg-ink-faint"
                                            />
                                            <span>{highlight}</span>
                                        </li>
                                    ))}
                                </ul>
                            </>
                        ) : null}

                        {item.stack.length > 0 ? (
                            <ul className="mt-5 flex flex-wrap gap-1.5">
                                {item.stack.map((tech) => (
                                    <li key={tech} className="tag">
                                        {tech}
                                    </li>
                                ))}
                            </ul>
                        ) : null}
                    </li>
                ))}
            </ol>
        </Section>
    );
}