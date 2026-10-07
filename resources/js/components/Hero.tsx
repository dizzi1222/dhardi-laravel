import { Link, usePage } from '@inertiajs/react';
import { useT } from '../lib/i18n';
import type { HomeProps } from '../types';

/**
 * The first screen.
 *
 * Everything a reader needs to decide whether to keep reading is above the
 * fold: who this is, whether he is available, that he is a Swiss citizen, and
 * the one sentence that explains the Laravel point. The citizenship line is not
 * decoration — for a Swiss employer it removes the first objection before it is
 * raised.
 */
export default function Hero() {
    const t = useT();
    const { profile, hero, translations } = usePage<HomeProps>().props;
    const languages = translations.profile.languages;

    return (
        <section className="shell pt-16 pb-12 sm:pt-24 sm:pb-16">
            <div className="grid gap-10 lg:grid-cols-[1.35fr_1fr] lg:gap-16">
                <div className="min-w-0">
                    <div className="flex flex-wrap items-center gap-3">
                        {hero.available ? (
                            <span className="inline-flex items-center gap-2 rounded-full bg-verify-soft px-3 py-1 text-xs font-medium text-verify">
                                <span className="size-1.5 rounded-full bg-verify" aria-hidden="true" />
                                {t('ui.available')}
                            </span>
                        ) : null}

                        <span className="tabular text-xs text-ink-faint">
                            {profile.location} · {profile.timezone}
                        </span>
                    </div>

                    <h1 className="mt-6 text-[length:var(--text-hero)] font-semibold leading-[1.05]">
                        {hero.headline}
                    </h1>

                    <p className="mt-6 max-w-2xl text-lg leading-relaxed text-ink-soft">
                        {hero.subheadline}
                    </p>

                    <div className="mt-8 flex flex-wrap items-center gap-3">
                        <a
                            href="#projects"
                            className="inline-flex items-center gap-2 rounded-full bg-ink px-5 py-2.5 text-sm font-medium text-paper transition-opacity hover:opacity-88"
                        >
                            {hero.cta}
                        </a>

                        <Link
                            href="/lebenslauf"
                            className="inline-flex items-center gap-2 rounded-full border border-rule px-5 py-2.5 text-sm font-medium text-ink-soft transition-colors hover:border-ink hover:text-ink"
                        >
                            {hero.secondaryCta}
                        </Link>
                    </div>

                    <p className="mt-8 max-w-2xl text-sm leading-relaxed text-ink-mute">
                        {profile.summary}
                    </p>
                </div>

                <aside className="lg:pt-1">
                    <div className="rounded-lg border border-rule bg-paper-raised p-5">
                        <p className="eyebrow">{translations.profile.citizenship_heading}</p>
                        <p className="mt-2 text-sm leading-relaxed text-ink">
                            {translations.profile.citizenship_body}
                        </p>
                    </div>

                    <dl className="mt-6 space-y-3 border-t border-rule-soft pt-5 text-sm">
                        <div className="flex items-baseline justify-between gap-4">
                            <dt className="text-ink-mute">{t('ui.profile.role')}</dt>
                            <dd className="text-right font-medium">{profile.title}</dd>
                        </div>

                        <div className="flex items-baseline justify-between gap-4">
                            <dt className="text-ink-mute">{t('ui.profile.born')}</dt>
                            <dd className="text-right text-ink-soft">{profile.birthplace}</dd>
                        </div>

                        <div className="flex items-baseline justify-between gap-4">
                            <dt className="text-ink-mute">{t('ui.language_label')}</dt>
                            <dd className="text-right">
                                <ul className="flex flex-wrap justify-end gap-1.5">
                                    {languages.map((language) => (
                                        <li
                                            key={language.label}
                                            className={`tag ${
                                                language.tone === 'growing'
                                                    ? 'border-signal/40 text-signal'
                                                    : ''
                                            }`}
                                            title={`${language.label} · ${language.level}`}
                                        >
                                            {language.label} {language.level}
                                        </li>
                                    ))}
                                </ul>
                            </dd>
                        </div>
                    </dl>

                    <ul className="mt-6 flex flex-wrap gap-x-5 gap-y-2 text-sm">
                        {Object.entries({
                            github: profile.links.github,
                            linkedin: profile.links.linkedin,
                            devto: profile.links.devto,
                            telegram: profile.links.telegram,
                        }).map(([key, href]) => (
                            <li key={key}>
                                <a
                                    href={href}
                                    target="_blank"
                                    rel="noreferrer noopener"
                                    className="text-ink-mute underline-offset-4 transition-colors hover:text-ink hover:underline"
                                >
                                    {key === 'devto' ? 'DEV.TO' : key.toUpperCase()}
                                </a>
                            </li>
                        ))}
                    </ul>
                </aside>
            </div>
        </section>
    );
}