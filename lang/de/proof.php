<?php

declare(strict_types=1);

return [

    'laravel' => [
        'title' => 'Diese Anwendung',
        'description' => 'Laravel 13.34 auf PHP 8.4, Inertia 3 mit React 19 und TypeScript im Strict-Modus, Tailwind 4, PHPUnit 12. Mandantenfähigkeit pro Instanz, Guardrails, Circuit Breaker, Prompt-Cache, Golden Dataset. Auf Vercel in einem Docker-Container mit FrankenPHP und Caddy ausgeliefert.',
    ],

    'react' => [
        'title' => 'PTD-Talento — Frontend',
        'description' => 'Produktiver React- und TypeScript-Stack für ein Talent-Marketplace: Redux Toolkit, React Router, Material UI, eigener Design-System in Figma, Vite. Über 18 Sprints und 35 gemergte Pull Requests mitentwickelt.',
    ],

    'typescript_backend' => [
        'title' => 'PTD-Talento — Backend',
        'description' => 'Node.js mit Express und TypeORM auf PostgreSQL: Google-OAuth, JWT, Sessions, Upload nach Google Cloud Storage, Swagger, Winston, Rate-Limiting. TypeScript durchgehend, Migrationen idempotent, ein P1-IDOR behoben.',
    ],

    'llm' => [
        'title' => 'LLM-Features mit Fallback',
        'description' => 'Produktiver Einsatz in PTD-Talento: OpenRouter-Modellaufruf mit 15 Sekunden Timeout, JSON-Schema-Prompt und deterministischem heuristischen Fallback. Hier ausgebaut zum Gateway mit Retry, Circuit Breaker, Cache und Evals.',
    ],

];
