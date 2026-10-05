<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Pages
    |--------------------------------------------------------------------------
    |
    | Where Inertia looks for page components. This app uses `resources/js/Pages`
    | (capital P), the conventional Inertia layout, so the package default of
    | `js/pages` is overridden here.
    |
    | `ensure_pages_exist` makes a test that asserts on a component fail when the
    | file is missing or renamed, instead of passing against a name that no
    | longer resolves to anything.
    |
    */

    'pages' => [
        'ensure_pages_exist' => true,
        'paths' => [
            resource_path('js/Pages'),
        ],
        'extensions' => ['tsx', 'ts', 'jsx', 'js', 'vue', 'svelte'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Server-side rendering
    |--------------------------------------------------------------------------
    |
    | Disabled on purpose. The application ships as a container that runs only
    | PHP, so there is no Node process at runtime to render with. Enabling SSR
    | would mean shipping a second runtime into the image purely for the first
    | paint, which is not a trade worth making for two pages.
    |
    */

    'ssr' => [
        'enabled' => false,
        'url' => 'http://127.0.0.1:13714',
        'bundle' => '/tmp/inertia-server/index.js',
    ],

    /*
    |--------------------------------------------------------------------------
    | History encryption
    |--------------------------------------------------------------------------
    */

    'history' => [
        'encrypt' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Partial reloads
    |--------------------------------------------------------------------------
    */

    'partial_reloads' => [
        'enabled' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Shared prop keys
    |--------------------------------------------------------------------------
    */

    'expose_shared_prop_keys' => true,

];
