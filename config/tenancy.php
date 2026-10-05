<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Tenant Resolution
    |--------------------------------------------------------------------------
    |
    | This application mirrors an "one deployment per customer" model: each
    | customer runs on their own instance, which keeps their data isolated
    | without a shared-schema multi-tenant database. The tenant is therefore
    | resolved from the instance configuration rather than from a shared
    | lookup table.
    |
    | Resolution order:
    |   1. Session / query string, used by the preview deployments where each
    |      customer gets their own subdomain.
    |   2. The `TENANT_SLUG` environment variable baked into the instance.
    |
    */

    'resolve_from' => ['session', 'env'],

    'fallback' => env('TENANT_SLUG', 'dhardi'),

    /*
    |--------------------------------------------------------------------------
    | Provisioning
    |--------------------------------------------------------------------------
    |
    | `php artisan tenant:provision` scaffolds a new customer instance: it
    | creates the tenant row, copies the content template, and prints the
    | environment variables that have to be set on the new deployment.
    |
    */

    'provisioning' => [
        'default_locale' => 'de',
        'supported_locales' => ['de', 'es', 'en'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Locales
    |--------------------------------------------------------------------------
    |
    | German is the default and the primary language of the site, since it is
    | the language the reader of a Swiss job posting writes in. `path`
    | segments are used for hreflang alternates.
    |
    */

    'locales' => [
        'de' => ['label' => 'Deutsch', 'path' => 'de', 'flag' => '🇨🇭'],
        'es' => ['label' => 'Español', 'path' => 'es', 'flag' => '🇩🇴'],
        'en' => ['label' => 'English', 'path' => 'en', 'flag' => '🇬🇧'],
    ],

    'locale_fallback' => 'en',

    'detect_from_browser' => env('TENANCY_DETECT_BROWSER', false),

];
