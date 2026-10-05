<?php

declare(strict_types=1);

return [

    'heading' => 'Why I fit this role',
    'intro' => 'You linked no official job description, just an email address. So I answered the requirements from your posting one by one — with the evidence next to each, and in two cases with what is still missing. I would rather show an open gap than a claim that breaks in a technical interview.',

    'legend' => [
        'addressed' => 'Evidenced',
        'partial' => 'Partial',
    ],

    'requirements' => [

        'laravel' => 'Solid Laravel experience in production — shipped and operated, not just side projects',

        'react' => 'React with TypeScript at a professional level',

        'llm' => 'Experience with LLM APIs, and how to build something reliable on top of them',

        'ownership' => 'Taking responsibility: the code is yours',

        'tests' => 'Writing tests, and caring whether things actually work',

        'german' => 'Enough German to speak with German-speaking customers',

        'location' => 'Based in the Alpine region or something comparable',

        'multitenancy' => 'Nice to have: multi-tenancy or instance-based deployment',

        'streaming' => 'Nice to have: voice interfaces, streaming, real-time UI',

    ],

    'evidence' => [

        'laravel' => 'This website is the answer — and it is not a claim, it is the code you are using right now. Laravel 13.34 on PHP 8.4, Inertia 3, React 19, Tailwind 4, PHPUnit 12 as the test framework, CI running Pint and PHPUnit, deployed to Vercel in a Docker container with FrankenPHP. Instance-based tenancy, migrations, factories, seeders, and a golden dataset for the assistant. Honestly: my production backend background is Node.js with Express, TypeORM and PostgreSQL, not Laravel. This application is my first production Laravel base — it is being built in public, with the test suite running and real monitoring, instead of in a private repository nobody ever sees.',

        'react' => 'Two production frontends with React and strict TypeScript: PTD-Talento for the Cincinnatus Institute of Craftsmanship (React 18, Redux Toolkit, Material UI, Vite, own design system in Figma) and PCE-Agencia (React 19, Vite, Tailwind, React Router). In both I also drove delivery: 74 merged pull requests and issues, 18 sprints, idempotent migrations, a P1 IDOR fixed, anti-spam rate limiting, and uploads moved to Google Cloud Storage.',

        'llm' => 'Three layers, because the question is not "can I call an API" but "what happens when it goes down". First, in production: profile-strength scoring in PTD-Talento calls a model through OpenRouter with a 15 second timeout, a JSON-schema-constrained prompt and a deterministic heuristic fallback when no key is present. Second, here on this site: a real gateway layer with exponential backoff and jitter, Retry-After respected, a circuit breaker per backend, a fallback chain, a per-tenant prompt cache, guardrails on input and output, and one observability row per attempt. Third, measurable: a golden dataset I replay against the live chain with `php artisan evals:run`, which exits non-zero if a prompt or model change breaks it. The live figures from the `llm_runs` table are further down this page.',

        'ownership' => 'On PTD-Talento I was Lead Tech in a team of four: I set the pace, ran the reviews and cut the releases. On top of that I operate all of my own projects single-handed, from the first commit to the deployment, including CI, secrets, servers and rollback. This site follows the same pattern: I designed it, built it, tested it and shipped it, with no layer in between.',

        'tests' => 'On this site: unit tests for the backoff curve, the circuit breaker, the guardrails and the cache keys; feature tests for tenant resolution, the assistant endpoint and the SSE stream; and tests that fail the build when a translation is missing. Plus CI running Pint, PHPUnit and tsc. Correcting my own history: the PTD-Talento backend had no test suite — I did not remedy that there, so I do not claim it here. PCE-Agencia does have a vitest stage in its GitHub Actions pipeline.',

        'german' => 'German is not my mother tongue — Spanish is, and I speak English at B2. This site is complete in German, and the assistant you are talking to right now answers in German. I write business German and read technical text and product discussion without effort. What I do not claim: fluent customer conversations at the level an enterprise client in the Alpine region expects. That is the single requirement from your posting I do not fully meet, and I am saying it now rather than explaining it in an interview. I am working on it actively, and if you want a trial interview in German, say so — then you will know immediately where I stand.',

        'location' => 'I live in the Dominican Republic (UTC−4), not in the Alpine region. That is the point your posting calls "Dach Region or comparable", and I would rather say it up front: I am not. What does apply: I am a Swiss citizen, so no work permit and no visa lead time is involved. I can start on day one. The overlap with CET/CEST is in the morning, which makes working with an Alpine-region team practical. For in-person customer or investor meetings I need lead time and planning, and I am glad to arrange it — I know that from working across two time zones, not from assumption.',

        'multitenancy' => 'This application is built exactly that way. One deployment per customer, the tenant resolved from the environment and applied with a global scope to every query so that isolation does not depend on discipline. `php artisan tenant:provision` creates a new customer instance in a single command, migrates, seeds and prints the environment variables that must be set on the new deployment. That matches your model of "every customer on its own instance" without making it more complicated than it is.',

        'streaming' => 'The assistant on this site streams its answer over Server-Sent Events, with honest provenance: you see which model answered, how long it took, and whether the answer came from cache. That is precisely the real-time UI you mention as a nice to have — built here because I would rather show it than claim it. If your AI Companion is moving towards voice interfaces, that is the area where I would learn fastest while you show me in live operation what the customer actually needs.',

    ],

    'label_tests' => 'Tests',
    'label_deploy' => 'Delivery',
    'label_retries' => 'Retries',
    'label_fallback' => 'Fallback',
    'label_evals' => 'Evals',
    'label_prs' => 'PRs',
    'label_sprints' => 'Sprints',
    'label_team' => 'Team',
    'label_permit' => 'Permit',
    'label_tz' => 'Timezone',
    'label_overlap' => 'CET overlap',

    'vercel' => 'Vercel (FrankenPHP)',
    'golden_dataset' => 'Golden dataset',
    'yes' => 'Yes',
    'none_required' => 'Not required',
    'overlap' => 'Morning',

];
