<?php

declare(strict_types=1);

return [

    'laravel' => [
        'title' => 'This application',
        'description' => 'Laravel 13.34 on PHP 8.4, Inertia 3 with React 19 and strict TypeScript, Tailwind 4, PHPUnit 12. Instance-based tenancy, guardrails, circuit breaker, prompt cache, golden dataset. Deployed to Vercel in a Docker container with FrankenPHP and Caddy.',
    ],

    'react' => [
        'title' => 'PTD-Talento — Frontend',
        'description' => 'A production React and TypeScript stack for a talent marketplace: Redux Toolkit, React Router, Material UI, own design system in Figma, Vite. Co-developed across more than 18 sprints and 35 merged pull requests.',
    ],

    'typescript_backend' => [
        'title' => 'PTD-Talento — Backend',
        'description' => 'Node.js with Express and TypeORM on PostgreSQL: Google OAuth, JWT, sessions, uploads to Google Cloud Storage, Swagger, Winston, rate limiting. TypeScript end to end, idempotent migrations, one P1 IDOR fixed.',
    ],

    'llm' => [
        'title' => 'AI features with fallback',
        'description' => 'Production use in PTD-Talento: an OpenRouter model call with a 15 second timeout, a JSON-schema-constrained prompt and a deterministic heuristic fallback. Extended here into a gateway with retries, circuit breaker, cache and evals.',
    ],

];
