import type { ReactNode } from 'react';

interface SectionProps {
    /** Two-digit index rendered in the margin, like a printed document. */
    index: string;
    id: string;
    eyebrow: string;
    heading: string;
    subtitle?: string;
    children: ReactNode;
}

/**
 * The shared frame for every section.
 *
 * One component owns the spacing, the rule and the numbering so that the
 * rhythm stays identical down the page. Consistency here is most of what makes
 * a long single page feel deliberate rather than exhausting.
 */
export default function Section({
    index,
    id,
    eyebrow,
    heading,
    subtitle,
    children,
}: SectionProps) {
    return (
        <section id={id} className="rule scroll-mt-24 py-16 sm:py-24">
            <div className="shell grid gap-8 lg:grid-cols-[8rem_1fr]">
                <div className="lg:pt-1">
                    <span className="tabular text-sm text-ink-faint">{index}</span>
                </div>

                <div className="min-w-0">
                    <p className="eyebrow">{eyebrow}</p>
                    <h2 className="mt-3 text-2xl font-semibold sm:text-3xl">{heading}</h2>

                    {subtitle ? (
                        <p className="mt-4 max-w-2xl text-base leading-relaxed text-ink-soft">{subtitle}</p>
                    ) : null}

                    <div className="mt-10">{children}</div>
                </div>
            </div>
        </section>
    );
}