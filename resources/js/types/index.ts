/**
 * Shapes of the props the server sends.
 *
 * Hand-written rather than generated: the payload is the contract between the
 * PHP side and this side, and a mismatch should be a type error here, not a
 * blank section on the page.
 */

import type { PageProps } from '@inertiajs/core';

export type Locale = 'de' | 'es' | 'en';

export interface LocaleOption {
    label: string;
    path: string;
    flag: string;
}

export type Translations = Record<string, string | number>;

export interface TranslationBag {
    ui: Record<string, unknown>;
    hero: Record<string, unknown>;
    fit: Record<string, unknown>;
    meta: Record<string, unknown>;
    profile: {
        summary: string;
        languages: Array<{ label: string; level: string; tone: string }>;
        citizenship_heading: string;
        citizenship_body: string;
    };
}

export interface SharedProps extends PageProps {
    locale: Locale;
    availableLocales: Record<Locale, LocaleOption>;
    tenant: { slug: string; name: string; accent: string } | null;
    flash: { status: string | null };
    translations: TranslationBag;
}

export interface ProofItem {
    label: string;
    value: string;
}

export interface FitItem {
    requirement: string;
    status: 'addressed' | 'partial';
    evidence: string;
    proof: ProofItem[];
}

export interface ExperienceItem {
    slug: string;
    role: string;
    organisation: string;
    organisation_url: string | null;
    location: string | null;
    is_remote: boolean;
    employment_type: string | null;
    start_date: string;
    end_date: string | null;
    is_current: boolean;
    duration_months: number;
    stack: string[];
    summary: string | null;
    highlights: string[];
}

export interface ProjectItem {
    slug: string;
    name: string;
    role: string | null;
    repo_url: string | null;
    live_url: string | null;
    case_study_url: string | null;
    year: number;
    status: string;
    is_featured: boolean;
    stack: string[];
    metrics: Record<string, string>;
    summary: string | null;
    problem: string | null;
    approach: string | null;
    highlights: string[];
}

export interface CertificationItem {
    name: string;
    issuer: string | null;
    url: string | null;
    status: 'completed' | 'in_progress';
    issued_year: number | null;
    note: string | null;
    status_label: string;
}

export interface StackProof {
    title: string;
    description: string;
    stack: string[];
    href: string;
}

export interface Hero {
    available: boolean;
    headline: string;
    subheadline: string;
    cta: string;
    secondaryCta: string;
}

export interface Profile {
    name: string;
    short_name: string;
    title: string;
    location: string;
    timezone: string;
    citizenship: string;
    citizenship_note: string;
    available: boolean;
    availability_note: string;
    birthplace: string;
    summary: string;
    languages: Array<{ label: string; level: string; tone: string }>;
    links: Record<string, string>;
}

export interface Telemetry {
    window: { total_runs: number; since: string | null };
    rates: Record<string, number>;
    latency: Record<string, number>;
    cost: Record<string, number>;
    by_provider: Record<string, { runs: number; success_rate: number }>;
    evals: {
        cases: number;
        measured: number;
        pass_rate: number | null;
        last_run_at: string | null;
    };
}

export interface AssistantAnswer {
    text: string;
    provider: string;
    model: string;
    intent: string;
    conversation_id: string;
    latency_ms: number;
    cached: boolean;
    cost_usd: number;
    prompt_tokens: number;
    completion_tokens: number;
}

export interface HomeProps extends SharedProps, PageProps {
    meta: { title: string; description: string };
    profile: Profile;
    hero: Hero;
    fit: FitItem[];
    experiences: ExperienceItem[];
    projects: ProjectItem[];
    skills: Record<string, Array<{ name: string; proof: string | null }>>;
    certifications: CertificationItem[];
    stackProof: StackProof[];
}

export interface ProjectShowProps extends SharedProps, PageProps {
    meta: { title: string; description: string };
    project: ProjectItem;
    all: Array<{ slug: string; name: string; year: number; summary: string | null }>;
}

export type TranslationsBag = Record<string, Translations>;