import { Head } from '@inertiajs/react';
import AssistantSection from '../components/AssistantSection';
import ContactSection from '../components/ContactSection';
import ExperienceSection from '../components/ExperienceSection';
import FitSection from '../components/FitSection';
import Hero from '../components/Hero';
import ProjectsSection from '../components/ProjectsSection';
import SiteFooter from '../components/SiteFooter';
import SiteHeader from '../components/SiteHeader';
import {
    CertificationsSection,
    StackProofSection,
} from '../components/StackSections';
import TelemetrySection from '../components/TelemetrySection';
import { useT } from '../lib/i18n';
import type { HomeProps } from '../types';

/**
 * The whole portfolio, one page.
 *
 * A reader deciding whether to reply to a job posting gets through this on one
 * scroll, so splitting it into routes would add navigation cost without adding
 * anything. The only thing worth its own route is a project case study.
 */
export default function Home(props: HomeProps) {
    const t = useT();

    return (
        <>
            <Head title={props.meta.title}>
                <meta name="description" content={props.meta.description} />
            </Head>

            <SiteHeader />

            <main id="main">
                <Hero />

                <FitSection items={props.fit} intro={t('fit.intro')} />

                <ExperienceSection items={props.experiences} />

                <ProjectsSection items={props.projects} />

                <StackProofSection items={props.stackProof} />

                <CertificationsSection items={props.certifications} />

                <AssistantSection />

                <TelemetrySection />

                <ContactSection profile={props.profile} />
            </main>

            <SiteFooter />
        </>
    );
}