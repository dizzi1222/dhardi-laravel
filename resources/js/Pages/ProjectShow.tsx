import { Head, Link } from '@inertiajs/react';
import SiteFooter from '../components/SiteFooter';
import SiteHeader from '../components/SiteHeader';
import { useT } from '../lib/i18n';
import type { ProjectShowProps } from '../types';

/**
 * A single project in depth.
 *
 * Structured as problem, approach, outcome — the three questions a technical
 * interviewer actually asks, and the shape most portfolio projects avoid.
 */
export default function ProjectShow({ project, all, meta }: ProjectShowProps) {
    const t = useT();

    const blocks = [
        { key: 'problem', heading: t('ui.sections.projects.problem'), body: project.problem },
        { key: 'approach', heading: t('ui.sections.projects.approach'), body: project.approach },
    ].filter((block) => block.body !== null);

    const metrics = Object.entries(project.metrics);

    return (
        <>
            <Head title={meta.title}>
                <meta name="description" content={meta.description} />
            </Head>

            <SiteHeader />

            <main id="main" className="shell pt-12 pb-16">
                <Link
                    href="/"
                    className="text-sm text-ink-mute underline-offset-4 hover:text-ink hover:underline"
                >
                    ← {t('ui.project.back')}
                </Link>

                <header className="mt-8 max-w-3xl">
                    <h1 className="text-3xl font-semibold sm:text-4xl">{project.name}</h1>

                    <p className="tabular mt-3 text-sm text-ink-mute">
                        {project.year}
                        {project.role ? ` · ${project.role}` : ''}
                    </p>

                    {project.summary ? (
                        <p className="mt-6 text-lg leading-relaxed text-ink-soft">{project.summary}</p>
                    ) : null}

                    <div className="mt-8 flex flex-wrap gap-x-6 gap-y-2 text-sm">
                        {project.live_url ? (
                            <a
                                href={project.live_url}
                                target="_blank"
                                rel="noreferrer noopener"
                                className="underline-offset-4 hover:underline"
                            >
                                {t('ui.sections.projects.live')}
                            </a>
                        ) : null}

                        {project.repo_url ? (
                            <a
                                href={project.repo_url}
                                target="_blank"
                                rel="noreferrer noopener"
                                className="text-ink-mute underline-offset-4 hover:text-ink hover:underline"
                            >
                                {t('ui.sections.projects.source')}
                            </a>
                        ) : null}
                    </div>
                </header>

                {metrics.length > 0 ? (
                    <dl className="mt-12 grid grid-cols-2 gap-px overflow-hidden rounded-lg border border-rule bg-rule sm:grid-cols-4">
                        {metrics.map(([key, value]) => (
                            <div key={key} className="bg-paper-raised p-4">
                                <dt className="eyebrow">{t(`ui.metrics.${key}`)}</dt>
                                <dd className="tabular mt-1.5 text-lg font-semibold">{value}</dd>
                            </div>
                        ))}
                    </dl>
                ) : null}

                <div className="mt-12 max-w-3xl space-y-10">
                    {blocks.map((block) => (
                        <section key={block.key}>
                            <h2 className="text-xl font-semibold">{block.heading}</h2>
                            <p className="mt-4 text-base leading-relaxed text-ink-soft">{block.body}</p>
                        </section>
                    ))}

                    {project.highlights.length > 0 ? (
                        <section>
                            <h2 className="text-xl font-semibold">
                                {t('ui.sections.projects.highlights')}
                            </h2>
                            <ul className="mt-4 space-y-3">
                                {project.highlights.map((highlight) => (
                                    <li
                                        key={highlight}
                                        className="flex gap-3 text-base leading-relaxed text-ink-soft"
                                    >
                                        <span
                                            aria-hidden="true"
                                            className="mt-2.5 size-1 shrink-0 rounded-full bg-signal"
                                        />
                                        <span>{highlight}</span>
                                    </li>
                                ))}
                            </ul>
                        </section>
                    ) : null}

                    {project.stack.length > 0 ? (
                        <section>
                            <h2 className="text-xl font-semibold">Stack</h2>
                            <ul className="mt-4 flex flex-wrap gap-1.5">
                                {project.stack.map((tech) => (
                                    <li key={tech} className="tag">
                                        {tech}
                                    </li>
                                ))}
                            </ul>
                        </section>
                    ) : null}
                </div>

                {all.length > 0 ? (
                    <aside className="mt-16 border-t border-rule pt-10">
                        <h2 className="eyebrow">{t('ui.project.other')}</h2>
                        <ul className="mt-4 grid gap-3 sm:grid-cols-2">
                            {all.map((other) => (
                                <li key={other.slug}>
                                    <Link
                                        href={`/projekt/${other.slug}`}
                                        className="text-sm underline-offset-4 hover:underline"
                                    >
                                        <span className="tabular text-xs text-ink-faint">
                                            {other.year}
                                        </span>{' '}
                                        {other.name}
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    </aside>
                ) : null}
            </main>

            <SiteFooter />
        </>
    );
}