<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Identity
    |--------------------------------------------------------------------------
    |
    | Facts only. Anything that needs translating lives in the lang files so
    | that it can differ per language; anything verifiable — a handle, a URL,
    | a citizenship — belongs here.
    |
    */

    'profile' => [
        'name' => 'Diego Samuel Härdi Santana',
        'short_name' => 'Diego Härdi',
        'title' => 'DevOps & Software Engineer',
        'location' => 'Jarabacoa, La Vega, República Dominicana',
        'timezone' => 'UTC−4',
        'citizenship' => 'schweizerisch',
        'citizenship_note' => 'domiciliado',
        'available' => true,
        'availability_note' => 'verfügbar',
        'birthplace' => 'San Pedro de Macorís, RD',
        'summary_key' => 'profile.summary',
    ],

    'links' => [
        'email' => 'diegosamuel042@gmail.com',
        'github' => 'https://github.com/dizzi1222',
        'linkedin' => 'https://www.linkedin.com/in/diego-samuel-h%C3%A4rdi-santana-3a4343428',
        'devto' => 'https://dev.to/dizzi1222',
        'telegram' => 'https://t.me/dizzi1222',
        'whatsapp' => 'https://wa.me/18298216385',
        'cv' => '/cv.pdf',
    ],

    /*
    |--------------------------------------------------------------------------
    | Test count
    |--------------------------------------------------------------------------
    |
    | Surfaced next to the Laravel requirement. It is a claim about this
    | repository, so it has to be kept true: update it when the suite grows,
    | which is exactly the sort of thing CI should be asserting instead.
    |
    */

    'test_count' => (int) env('PORTFOLIO_TEST_COUNT', 0),

    /*
    |--------------------------------------------------------------------------
    | Stack evidence
    |--------------------------------------------------------------------------
    |
    | Each entry points at a repository or a commit rather than at a claim.
    |
    */

    'stack_proof' => [
        [
            'title_key' => 'proof.laravel.title',
            'description_key' => 'proof.laravel.description',
            'stack' => ['Laravel 13', 'PHP 8.4', 'Inertia 3', 'PHPUnit 12', 'FrankenPHP'],
            'href' => 'https://github.com/dizzi1222/dhardi-laravel',
        ],
        [
            'title_key' => 'proof.react.title',
            'description_key' => 'proof.react.description',
            'stack' => ['React 19', 'TypeScript', 'Tailwind 4', 'Vite 8'],
            'href' => 'https://github.com/Cincinnatus-Institute-of-Craftsmanship/ptd-talento-front',
        ],
        [
            'title_key' => 'proof.typescript_backend.title',
            'description_key' => 'proof.typescript_backend.description',
            'stack' => ['Express', 'TypeORM', 'PostgreSQL', 'Docker'],
            'href' => 'https://github.com/Cincinnatus-Institute-of-Craftsmanship/ptd-talento-back',
        ],
        [
            'title_key' => 'proof.llm.title',
            'description_key' => 'proof.llm.description',
            'stack' => ['OpenRouter', 'Anthropic', 'MCP', 'Evals'],
            'href' => 'https://github.com/Cincinnatus-Institute-of-Craftsmanship/ptd-talento-back',
        ],
    ],

];
