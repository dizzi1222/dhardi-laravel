import { Link } from '@inertiajs/react';
import Section from './Section';
import { useT } from '../lib/i18n';
import type { Profile } from '../types';

/**
 * Contact.
 *
 * No form and no tracking script. The posting asked for a CV and a few words
 * by email, and a contact form would only add a way for the message to be lost.
 */
export default function ContactSection({ profile }: { profile: Profile }) {
    const t = useT();

    const links = [
        { key: 'email', href: `mailto:${profile.links.email}`, label: profile.links.email },
        { key: 'github', href: profile.links.github, label: 'github.com/dizzi1222' },
        { key: 'linkedin', href: profile.links.linkedin, label: 'LinkedIn' },
        { key: 'telegram', href: profile.links.telegram, label: 'Telegram' },
        { key: 'whatsapp', href: profile.links.whatsapp, label: 'WhatsApp' },
    ];

    return (
        <Section
            index="07"
            id="contact"
            eyebrow={t('ui.sections.contact.eyebrow')}
            heading={t('ui.sections.contact.heading')}
            subtitle={t('ui.sections.contact.subtitle')}
        >
            <a
                href={`mailto:${profile.links.email}`}
                className="inline-flex items-center gap-2 rounded-full bg-ink px-5 py-2.5 text-sm font-medium text-paper transition-opacity hover:opacity-88"
            >
                {t('ui.sections.contact.primary_cta')}
            </a>

            <ul className="mt-8 divide-y divide-rule-soft border-y border-rule-soft">
                {links.map((link) => (
                    <li key={link.key}>
                        <a
                            href={link.href}
                            {...(link.key === 'email'
                                ? {}
                                : { target: '_blank', rel: 'noreferrer noopener' })}
                            className="flex items-baseline justify-between gap-4 py-3 text-sm transition-colors hover:text-signal"
                        >
                            <span className="eyebrow">
                                {link.key === 'email'
                                    ? t('ui.sections.contact.email_label')
                                    : link.key}
                            </span>
                            <span className="tabular text-right">{link.label}</span>
                        </a>
                    </li>
                ))}

                <li>
                    <Link
                        href="/lebenslauf"
                        className="flex items-baseline justify-between gap-4 py-3 text-sm transition-colors hover:text-signal"
                    >
                        <span className="eyebrow">PDF</span>
                        <span>{t('ui.sections.contact.cv_label')}</span>
                    </Link>
                </li>
            </ul>
        </Section>
    );
}