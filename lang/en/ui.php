<?php

declare(strict_types=1);

return [

    'brand' => 'DH',
    'language_label' => 'Language',
    'skip_to_content' => 'Skip to content',

    'nav' => [
        'fit' => 'Fit',
        'experience' => 'Experience',
        'projects' => 'Projects',
        'stack' => 'Stack',
        'assistant' => 'Assistant',
        'contact' => 'Contact',
    ],

    'cv' => 'Résumé',

    'available' => 'Open to new work',

    'sections' => [

        'fit' => [
            'eyebrow' => 'Requirements',
            'heading' => 'Why I fit this role',
        ],

        'experience' => [
            'eyebrow' => 'Background',
            'heading' => 'Experience',
            'subtitle' => 'Roles, scope of responsibility, and what actually shipped.',
            'highlights' => 'Responsibility',
            'duration_label' => 'Duration',
            'current' => 'Present',
            'remote' => 'Remote',
            'months' => 'months',
            'no_highlights' => 'No further detail recorded.',
        ],

        'projects' => [
            'eyebrow' => 'Work',
            'heading' => 'Projects',
            'subtitle' => 'Each with its stack, its status and the evidence carrying the claim.',
            'live' => 'Live',
            'source' => 'Source',
            'view' => 'Case study',
            'problem' => 'Starting point',
            'approach' => 'Approach',
            'highlights' => 'Outcome',
            'metrics' => 'Metrics',
            'featured' => 'In focus',
        ],

        'stack' => [
            'eyebrow' => 'Evidence',
            'heading' => 'Stack, with evidence',
            'subtitle' => 'Every entry points at a repository or a commit, not at a claim.',
        ],

        'skills' => [
            'heading' => 'Technologies',
            'subtitle' => 'With the artefact beside it that backs the depth.',
        ],

        'certifications' => [
            'eyebrow' => 'Education',
            'heading' => 'Education and certifications',
            'subtitle' => 'Completed and in-progress listed separately.',
        ],

        'contact' => [
            'eyebrow' => 'Contact',
            'heading' => 'Contact',
            'subtitle' => 'No form, no tracking. Direct, the way you put it in the posting.',
            'email_label' => 'Email',
            'cv_label' => 'Résumé as PDF',
            'primary_cta' => 'Send an email',
        ],

        'footer' => [
            'built_with' => 'Built with Laravel 13, Inertia 3, React 19, TypeScript, Tailwind 4 and Pest. Deployed to Vercel in a Docker container with FrankenPHP.',
            'source' => 'Source of this site',
            'rights' => 'All rights reserved.',
        ],

    ],

    'status' => [
        'shipped' => 'Shipped',
        'in_progress' => 'In progress',
        'archived' => 'Archived',
    ],

    'cert_status' => [
        'completed' => 'Completed',
        'in_progress' => 'In progress',
    ],

    'profile' => [
        'role' => 'Role',
        'born' => 'Born',
    ],

    // Labels for the `metrics` object of a project. The keys are stable slugs;
    // the text is translated, so the same project renders correctly in all
    // three languages without the content file knowing any of them.
    'metrics' => [
        'languages' => 'Languages',
        'backends' => 'Backends',
        'tenancy' => 'Tenancy model',
        'prs' => 'PRs and issues',
        'sprints' => 'Sprints',
        'team' => 'Team',
        'epics' => 'Epics',
        'resources' => 'REST resources',
        'ci_stages' => 'CI stages',
        'tables' => 'Tables',
        'deploy' => 'Delivery',
        'migrations' => 'Migrations',
        'windows' => 'Windows',
        'packages' => 'Packages',
        'rebuild' => 'Rebuild',
        'tools' => 'Tools',
        'ssrf' => 'SSRF protection',
    ],

    'project' => [
        'back' => 'Back to the index',
        'other' => 'Other projects',
        'not_found' => 'That project does not exist.',
    ],

    'assistant' => [
        'eyebrow' => 'Live demo',
        'heading' => 'Ask the assistant',
        'subtitle' => 'The assistant answers from this site’s own data and always names the model that replied. It runs through the same gateway layer a production feature would use: retries, circuit breaker, fallback, cache, guardrails.',
        'placeholder' => 'For example: how is instance-based tenancy handled?',
        'send' => 'Ask',
        'sending' => 'Answering …',
        'clear' => 'New conversation',
        'suggestions' => [
            'Why Laravel and not Node?',
            'How does the reliability layer work?',
            'How does a new customer get set up?',
            'Do you speak German?',
        ],
        'meta_provider' => 'Model',
        'meta_latency' => 'Duration',
        'meta_cached' => 'from cache',
        'meta_cost' => 'Cost',
        'disclaimer' => 'The assistant can be wrong. It is fed from this site’s content and does not invent facts that are not there — but please check anything it says about me against my repository.',
        'empty' => 'No question asked yet.',
    ],

    'telemetry' => [
        'eyebrow' => 'Operations',
        'heading' => 'What the reliability layer measures',
        'subtitle' => 'Live from the llm_runs table. No estimated figures: every value is a query over the attempts this application has recorded itself.',
        'runs' => 'Attempts',
        'since' => 'since',
        'metrics' => [
            'success' => 'Success rate',
            'cache_hit' => 'Cache hits',
            'blocked' => 'Blocked by guardrails',
            'fallback' => 'Answered by fallback',
            'p50' => 'Latency p50',
            'p95' => 'Latency p95',
            'max' => 'Max latency',
            'cost_total' => 'Total cost',
            'cost_avg' => 'Cost per attempt',
        ],
        'evals' => [
            'heading' => 'Golden dataset',
            'note' => 'Replayed regularly against the live chain. A prompt or model change that alters the expected answers shows up here, rather than first in a customer conversation.',
            'cases' => 'Cases',
            'measured' => 'measured',
            'pass_rate' => 'Pass rate',
            'last_run' => 'Last run',
        ],
        'empty' => 'No data yet. This table fills up as soon as the assistant is used.',
    ],

    'common' => [
        'back' => 'Back',
        'close' => 'Close',
        'loading' => 'Loading …',
        'retry' => 'Try again',
        'unknown' => 'Unknown',
    ],

];
