<?php

declare(strict_types=1);

return [

    'laravel' => [
        'title' => 'Esta aplicación',
        'description' => 'Laravel 13.34 sobre PHP 8.4, Inertia 3 con React 19 y TypeScript estricto, Tailwind 4, PHPUnit 12. Multi-tenancy por instancia, guardrails, circuit breaker, caché de prompts, golden dataset. Desplegada en Vercel en un contenedor Docker con FrankenPHP y Caddy.',
    ],

    'react' => [
        'title' => 'PTD-Talento — Frontend',
        'description' => 'Stack productivo de React y TypeScript para un marketplace de talento: Redux Toolkit, React Router, Material UI, design system propio en Figma, Vite. Co-desarrollado a lo largo de más de 18 sprints y 35 pull requests mergeados.',
    ],

    'typescript_backend' => [
        'title' => 'PTD-Talento — Backend',
        'description' => 'Node.js con Express y TypeORM sobre PostgreSQL: Google OAuth, JWT, sesiones, subidas a Google Cloud Storage, Swagger, Winston, rate-limiting. TypeScript de punta a punta, migraciones idempotentes, un P1 IDOR corregido.',
    ],

    'llm' => [
        'title' => 'Features de IA con fallback',
        'description' => 'Uso productivo en PTD-Talento: llamada a modelo vía OpenRouter con timeout de 15 segundos, prompt con JSON Schema y fallback heurístico determinista. Aquí lo amplié hasta convertirlo en un gateway con reintentos, circuit breaker, caché y evals.',
    ],

];
