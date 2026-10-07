import { Head } from '@inertiajs/react';

/**
 * The printable CV.
 *
 * Rendered as text rather than as an image so it can be selected, searched,
 * translated and read aloud. The sibling PDF cannot: it was produced by a
 * vector editor that converted the text to outlines, which is why a PDF viewer
 * finds almost nothing in it.
 *
 * The layout is a print stylesheet rather than utility classes alone, because
 * the point of this page is what comes out of a printer.
 */

interface Link {
    label: string;
    href: string;
}

interface PortfolioItem extends Link {
    name: string;
    description: string;
}

interface LanguageItem {
    label: string;
    level: string;
}

interface ExperienceItem {
    org: string;
    role: string;
    period: string | null;
    bullets: string[];
}

interface EducationItem {
    org: string;
    detail: string;
    period: string | null;
}

interface Props {
    meta: { title: string; description: string };
    cv: {
        role: string;
        location: string;
        availability: string;
        print: string;
        pdf_original: string;
        notice_title: string;
        notice_body: string;
        sections: {
            profil: { title: string; body: string };
            stack: { title: string; items: string[] };
            idiomas: { title: string; items: LanguageItem[] };
            enlaces: { title: string; items: Link[] };
            portfolio: { title: string; items: PortfolioItem[] };
            experiencia: { title: string; items: ExperienceItem[] };
            educacion: { title: string; items: EducationItem[] };
        };
        footer_note: string;
    };
    displayName: string;
    email: string;
    phone: string;
}

export default function Lebenslauf({ cv, meta, displayName, email, phone }: Props) {
    const sections = cv.sections;

    return (
        <>
            <Head title={meta.title}>
                <meta name="description" content={meta.description} />
            </Head>

            {/*
                Hidden on screen, visible in print. A print stylesheet cannot
                add a control the layout does not already have, so the button
                lives here rather than in a floating toolbar that would then
                need hiding.
            */}
            <div className="mx-auto max-w-3xl px-6 pt-10 print:hidden">
                <div className="flex flex-wrap items-center gap-3">
                    <button
                        type="button"
                        onClick={() => window.print()}
                        className="inline-flex items-center gap-2 rounded-full bg-ink px-5 py-2.5 text-sm font-medium text-paper transition-opacity hover:opacity-88"
                    >
                        {cv.print}
                    </button>

                    <a
                        href="/cv.pdf"
                        className="text-sm text-ink-mute underline-offset-4 hover:text-ink hover:underline"
                    >
                        {cv.pdf_original}
                    </a>
                </div>
            </div>

            <main className="mx-auto max-w-3xl px-6 py-12 print:px-0 print:py-0">
                <header className="border-b border-rule pb-6">
                    <h1 className="text-2xl font-semibold tracking-tight">{displayName}</h1>
                    <p className="mt-1 text-sm text-ink-mute">{cv.role}</p>

                    <div className="tabular mt-3 flex flex-wrap gap-x-5 gap-y-1 text-sm text-ink-soft">
                        <a className="hover:text-signal" href={`mailto:${email}`}>
                            {email}
                        </a>
                        <span>{phone}</span>
                        <span>{cv.location}</span>
                    </div>

                    <p className="mt-2 text-sm text-verify">{cv.availability}</p>
                </header>

                {/*
                    Stated once, in place, rather than silently reconciling the
                    original's stack list against what the repository shows.
                */}
                <aside className="mt-6 border-l-2 border-signal bg-signal-soft/40 px-4 py-3 print:border-signal">
                    <p className="text-sm font-medium text-ink">{cv.notice_title}</p>
                    <p className="mt-1 text-sm leading-relaxed text-ink-soft">{cv.notice_body}</p>
                </aside>

                <Section title={sections.profil.title}>
                    <p className="text-sm leading-relaxed text-ink-soft">{sections.profil.body}</p>
                </Section>

                <Section title={sections.stack.title}>
                    <ul className="flex flex-wrap gap-1.5">
                        {sections.stack.items.map((tech) => (
                            <li key={tech} className="tag">
                                {tech}
                            </li>
                        ))}
                    </ul>
                </Section>

                <Section title={sections.idiomas.title}>
                    <dl className="flex flex-wrap gap-x-8 gap-y-2">
                        {sections.idiomas.items.map((language) => (
                            <div key={language.label} className="flex items-baseline gap-2">
                                <dt className="text-sm font-medium">{language.label}</dt>
                                <dd className="text-sm text-ink-mute">{language.level}</dd>
                            </div>
                        ))}
                    </dl>
                </Section>

                <Section title={sections.experiencia.title}>
                    <div className="space-y-6">
                        {sections.experiencia.items.map((job) => (
                            <div key={`${job.org}-${job.role}`} className="break-inside-avoid">
                                <div className="flex flex-wrap items-baseline justify-between gap-x-4">
                                    <h3 className="text-sm font-semibold">{job.org}</h3>
                                    {job.period ? (
                                        <span className="tabular text-xs text-ink-faint">
                                            {job.period}
                                        </span>
                                    ) : null}
                                </div>

                                <p className="mt-0.5 text-sm text-ink-mute">{job.role}</p>

                                {job.bullets.length > 0 ? (
                                    <ul className="mt-2 space-y-1">
                                        {job.bullets.map((bullet) => (
                                            <li key={bullet} className="flex gap-2 text-sm leading-relaxed text-ink-soft">
                                                <span
                                                    aria-hidden="true"
                                                    className="mt-2 size-1 shrink-0 rounded-full bg-ink-faint"
                                                />
                                                <span>{bullet}</span>
                                            </li>
                                        ))}
                                    </ul>
                                ) : null}
                            </div>
                        ))}
                    </div>
                </Section>

                <Section title={sections.portfolio.title}>
                    <ul className="space-y-2">
                        {sections.portfolio.items.map((item) => (
                            <li key={item.name}>
                                <a
                                    href={item.href}
                                    target="_blank"
                                    rel="noreferrer noopener"
                                    className="text-sm font-medium underline-offset-4 hover:underline"
                                >
                                    {item.name}
                                </a>
                                <span className="text-sm text-ink-mute"> — {item.description}</span>
                            </li>
                        ))}
                    </ul>
                </Section>

                <Section title={sections.educacion.title}>
                    <div className="space-y-4">
                        {sections.educacion.items.map((entry) => (
                            <div key={entry.org} className="break-inside-avoid">
                                <h3 className="text-sm font-semibold">{entry.org}</h3>
                                <p className="mt-0.5 text-sm text-ink-mute">{entry.detail}</p>
                            </div>
                        ))}
                    </div>
                </Section>

                <Section title={sections.enlaces.title}>
                    <ul className="flex flex-wrap gap-x-5 gap-y-1">
                        {sections.enlaces.items.map((link) => (
                            <li key={link.label}>
                                <a
                                    href={link.href}
                                    target={link.href.startsWith('mailto:') ? undefined : '_blank'}
                                    rel="noreferrer noopener"
                                    className="text-sm text-ink-soft underline-offset-4 hover:text-signal hover:underline"
                                >
                                    {link.label}
                                </a>
                            </li>
                        ))}
                    </ul>
                </Section>

                <footer className="mt-8 border-t border-rule pt-4">
                    <p className="text-xs text-ink-mute">{cv.footer_note}</p>
                </footer>
            </main>
        </>
    );
}

function Section({ title, children }: { title: string; children: React.ReactNode }) {
    return (
        <section className="mt-8">
            <h2 className="eyebrow mb-3">{title}</h2>
            {children}
        </section>
    );
}