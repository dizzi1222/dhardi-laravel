import Section from './Section';
import { useT } from '../lib/i18n';
import type { CertificationItem, StackProof } from '../types';

/**
 * Two blocks that answer the same question from different angles: the stack
 * that is evidenced by a repository, and the credentials that are not code.
 */
export function StackProofSection({ items }: { items: StackProof[] }) {
    const t = useT();

    return (
        <Section
            index="04"
            id="stack"
            eyebrow={t('ui.sections.stack.eyebrow')}
            heading={t('ui.sections.stack.heading')}
            subtitle={t('ui.sections.stack.subtitle')}
        >
            <ul className="grid gap-5 md:grid-cols-2">
                {items.map((item) => (
                    <li
                        key={item.href}
                        className="flex flex-col rounded-lg border border-rule bg-paper-raised p-5 sm:p-6"
                    >
                        <div className="flex items-start justify-between gap-4">
                            <h3 className="text-base font-semibold">{item.title}</h3>

                            <a
                                href={item.href}
                                target="_blank"
                                rel="noreferrer noopener"
                                aria-label={`${item.title} — ${t('ui.sections.projects.source')}`}
                                className="tabular shrink-0 text-xs text-ink-faint underline-offset-4 transition-colors hover:text-ink hover:underline"
                            >
                                {t('ui.sections.projects.source')}
                            </a>
                        </div>

                        <p className="mt-3 flex-1 text-sm leading-relaxed text-ink-soft">
                            {item.description}
                        </p>

                        <ul className="mt-5 flex flex-wrap gap-1.5">
                            {item.stack.map((tech) => (
                                <li key={tech} className="tag">
                                    {tech}
                                </li>
                            ))}
                        </ul>
                    </li>
                ))}
            </ul>
        </Section>
    );
}

export function CertificationsSection({ items }: { items: CertificationItem[] }) {
    const t = useT();

    return (
        <Section
            index="05"
            id="certifications"
            eyebrow={t('ui.sections.certifications.eyebrow')}
            heading={t('ui.sections.certifications.heading')}
            subtitle={t('ui.sections.certifications.subtitle')}
        >
            <ul className="divide-y divide-rule-soft border-y border-rule-soft">
                {items.map((certification) => {
                    const done = certification.status === 'completed';

                    return (
                        <li
                            key={certification.name}
                            className="flex flex-wrap items-baseline justify-between gap-x-6 gap-y-1 py-4"
                        >
                            <div className="min-w-0">
                                <p className="text-sm font-medium">
                                    {certification.url ? (
                                        <a
                                            href={certification.url}
                                            target="_blank"
                                            rel="noreferrer noopener"
                                            className="underline-offset-4 hover:underline"
                                        >
                                            {certification.name}
                                        </a>
                                    ) : (
                                        certification.name
                                    )}
                                </p>

                                {certification.issuer ? (
                                    <p className="mt-0.5 text-xs text-ink-mute">
                                        {certification.issuer}
                                    </p>
                                ) : null}

                                {certification.note ? (
                                    <p className="mt-2 max-w-2xl text-xs leading-relaxed text-ink-mute">
                                        {certification.note}
                                    </p>
                                ) : null}
                            </div>

                            <span
                                className={`tag shrink-0 ${
                                    done
                                        ? 'border-verify/40 bg-verify-soft text-verify'
                                        : 'border-warn/40 bg-warn-soft text-warn'
                                }`}
                            >
                                {certification.issued_year ? `${certification.issued_year} · ` : ''}
                                {certification.status_label}
                            </span>
                        </li>
                    );
                })}
            </ul>
        </Section>
    );
}