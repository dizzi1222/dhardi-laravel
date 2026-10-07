import { Link, usePage } from '@inertiajs/react';
import { useT } from '../lib/i18n';
import type { SharedProps } from '../types';

export default function SiteFooter() {
    const t = useT();
    const { tenant, displayName } = usePage<SharedProps>().props;

    return (
        <footer className="rule mt-8 py-12">
            <div className="shell grid gap-8 sm:grid-cols-[1fr_auto] sm:items-end">
                <div className="max-w-2xl">
                    <p className="eyebrow">
                        {tenant?.name ?? displayName} · {new Date().getFullYear()}
                    </p>
                    <p className="mt-3 text-xs leading-relaxed text-ink-mute">
                        {t('ui.sections.footer.built_with')}
                    </p>
                </div>

                <div className="flex flex-wrap gap-x-6 gap-y-2 text-sm">
                    <Link href="/" className="text-ink-mute hover:text-ink">
                        {t('ui.nav.experience')}
                    </Link>
                    <a
                        href="https://github.com/dizzi1222/dhardi-laravel"
                        target="_blank"
                        rel="noreferrer noopener"
                        className="text-ink-mute underline-offset-4 hover:text-ink hover:underline"
                    >
                        {t('ui.sections.footer.source')}
                    </a>
                </div>
            </div>
        </footer>
    );
}