import { Link, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { useLocaleSwitch, useT } from '../lib/i18n';
import type { Locale, SharedProps } from '../types';

const NAV = ['fit', 'experience', 'projects', 'stack', 'assistant', 'contact'] as const;

type NavKey = (typeof NAV)[number];

/**
 * Sticky header with the language switcher.
 *
 * The switcher is a real form submit rather than a JavaScript handler, so it
 * keeps working without JS and stays keyboard-operable for free.
 */
export default function SiteHeader() {
    const t = useT();
    const switchLocale = useLocaleSwitch();
    const { locale, availableLocales } = usePage<SharedProps>().props;

    const [scrolled, setScrolled] = useState(false);

    useEffect(() => {
        const onScroll = () => setScrolled(window.scrollY > 8);

        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });

        return () => window.removeEventListener('scroll', onScroll);
    }, []);

    return (
        <header
            className={`sticky top-0 z-50 border-b transition-colors ${
                scrolled
                    ? 'border-rule bg-paper/85 backdrop-blur-md'
                    : 'border-transparent bg-paper'
            }`}
        >
            <a
                href="#main"
                className="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-3 focus:z-10 focus:rounded focus:bg-ink focus:px-3 focus:py-1.5 focus:text-sm focus:text-paper"
            >
                {t('ui.skip_to_content')}
            </a>

            <div className="shell flex items-center gap-6 py-3.5">
                <Link href="/" className="tabular text-sm font-medium tracking-tight">
                    DH
                </Link>

                <nav aria-label="Primary" className="hidden flex-1 md:block">
                    <ul className="flex flex-wrap items-center gap-x-6 gap-y-1">
                        {NAV.map((key: NavKey) => (
                            <li key={key}>
                                <a
                                    href={`#${key}`}
                                    className="text-sm text-ink-mute transition-colors hover:text-ink"
                                >
                                    {t(`ui.nav.${key}`)}
                                </a>
                            </li>
                        ))}
                    </ul>
                </nav>

                <div className="ml-auto flex items-center gap-4">
                    <a
                        href="/cv.pdf"
                        className="hidden text-sm text-ink-mute transition-colors hover:text-ink sm:inline"
                    >
                        {t('ui.cv')}
                    </a>

                    <div
                        className="flex items-center gap-0.5"
                        role="group"
                        aria-label={t('ui.language_label')}
                    >
                        {(Object.keys(availableLocales) as Locale[]).map((code) => (
                            <button
                                key={code}
                                type="button"
                                onClick={() => switchLocale(code)}
                                aria-current={code === locale ? 'true' : undefined}
                                className={`tabular rounded px-1.5 py-1 text-xs uppercase transition-colors ${
                                    code === locale
                                        ? 'bg-ink text-paper'
                                        : 'text-ink-faint hover:text-ink'
                                }`}
                            >
                                {code}
                            </button>
                        ))}
                    </div>
                </div>
            </div>
        </header>
    );
}