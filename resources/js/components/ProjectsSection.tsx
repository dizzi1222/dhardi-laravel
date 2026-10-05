import { Link } from '@inertiajs/react';
import Section from './Section';
import { useT } from '../lib/i18n';
import type { ProjectItem } from '../types';

interface ProjectsSectionProps {
    items: ProjectItem[];
}

const STATUS_TONE: Record<string, string> = {
    shipped: 'border-verify/40 bg-verify-soft text-verify',
    in_progress: 'border-warn/40 bg-warn-soft text-warn',
    archived: 'border-rule text-ink-mute',
};

/**
 * The project grid.
 *
 * Featured work comes first and gets the metrics; the rest stay compact. The
 * source link is only rendered when the repository is actually public — a dead
 * or private link costs more credibility than it offers.
 */
export default function ProjectsSection({ items }: ProjectsSectionProps) {
    const t = useT();

    return (
        <Section
            index="03"
            id="projects"
            eyebrow={t('ui.sections.projects.eyebrow')}
            heading={t('ui.sections.projects.heading')}
            subtitle={t('ui.sections.projects.subtitle')}
        >
            <ul className="grid gap-5 md:grid-cols-2">
                {items.map((project) => {
                    const metrics = Object.entries(project.metrics);

                    return (
                        <li
                            key={project.slug}
                            className="flex flex-col rounded-lg border border-rule bg-paper-raised p-5 transition-colors hover:border-ink-faint sm:p-6"
                        >
                            <div className="flex items-start justify-between gap-4">
                                <div className="min-w-0">
                                    <h3 className="text-base font-semibold">
                                        {project.case_study_url ? (
                                            <Link
                                                href={project.case_study_url}
                                                className="underline-offset-4 hover:underline"
                                            >
                                                {project.name}
                                            </Link>
                                        ) : (
                                            project.name
                                        )}
                                    </h3>

                                    <p className="tabular mt-1 text-xs text-ink-faint">
                                        {project.year}
                                        {project.role ? ` · ${project.role}` : ''}
                                    </p>
                                </div>

                                <span
                                    className={`tag shrink-0 ${STATUS_TONE[project.status] ?? STATUS_TONE.archived}`}
                                >
                                    {t(`ui.status.${project.status}`)}
                                </span>
                            </div>

                            {project.summary ? (
                                <p className="mt-4 text-sm leading-relaxed text-ink-soft">
                                    {project.summary}
                                </p>
                            ) : null}

                            {metrics.length > 0 ? (
                                <dl className="mt-5 grid grid-cols-2 gap-x-4 gap-y-3 border-t border-rule-soft pt-4 sm:grid-cols-4">
                                    {metrics.map(([key, value]) => (
                                        <div key={key}>
                                            <dt className="eyebrow">
                                                {t(`ui.metrics.${key}`)}
                                            </dt>
                                            <dd className="tabular mt-1 text-sm font-medium">{value}</dd>
                                        </div>
                                    ))}
                                </dl>
                            ) : null}

                            {project.stack.length > 0 ? (
                                <ul className="mt-5 flex flex-wrap gap-1.5">
                                    {project.stack.map((tech) => (
                                        <li key={tech} className="tag">
                                            {tech}
                                        </li>
                                    ))}
                                </ul>
                            ) : null}

                            <div className="mt-6 flex flex-wrap gap-x-5 gap-y-2 pt-1 text-sm">
                                {project.case_study_url ? (
                                    <Link
                                        href={project.case_study_url}
                                        className="text-ink underline-offset-4 hover:underline"
                                    >
                                        {t('ui.sections.projects.view')}
                                    </Link>
                                ) : null}

                                {project.live_url ? (
                                    <a
                                        href={project.live_url}
                                        target="_blank"
                                        rel="noreferrer noopener"
                                        className="text-ink-mute underline-offset-4 hover:text-ink hover:underline"
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
                        </li>
                    );
                })}
            </ul>
        </Section>
    );
}