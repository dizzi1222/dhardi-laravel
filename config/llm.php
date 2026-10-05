<?php

declare(strict_types=1);
use App\Llm\Providers\AnthropicProvider;
use App\Llm\Providers\DeterministicProvider;
use App\Llm\Providers\OpenRouterProvider;

return [

    /*
    |--------------------------------------------------------------------------
    | Backend order
    |--------------------------------------------------------------------------
    |
    | Tried top to bottom. A backend reporting itself unavailable — because a
    | credential is missing — is skipped without consuming an attempt. The
    | final entry is deterministic and always available, so the feature has a
    | floor: a worse answer instead of a stack trace.
    |
    */

    'chain' => [
        AnthropicProvider::class,
        OpenRouterProvider::class,
        DeterministicProvider::class,
    ],

    'providers' => [

        'anthropic' => [
            'key' => env('ANTHROPIC_API_KEY'),
            'model' => env('ANTHROPIC_MODEL', 'claude-sonnet-4-5'),
            'base_url' => env('ANTHROPIC_BASE_URL', 'https://api.anthropic.com/v1/messages'),
            'version' => '2023-06-01',
            'input_per_mtok' => 3.0,
            'output_per_mtok' => 15.0,
        ],

        'openrouter' => [
            'key' => env('OPENROUTER_API_KEY'),
            'model' => env('OPENROUTER_MODEL', 'nvidia/nemotron-3-nano-30b-a3b:free'),
            'base_url' => env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1/chat/completions'),
            'input_per_mtok' => 0.0,
            'output_per_mtok' => 0.0,
        ],

    ],

    'retry' => [
        'max_attempts' => (int) env('LLM_MAX_ATTEMPTS', 3),
        'base_delay_ms' => 250,
        'max_delay_ms' => 4000,
        'multiplier' => 2.0,
    ],

    'breaker' => [
        'failure_threshold' => (int) env('LLM_BREAKER_THRESHOLD', 5),
        'cooldown_seconds' => (int) env('LLM_BREAKER_COOLDOWN', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | `database` is the default because the store has to be shared between
    | instances: an in-memory or file store would give every serverless
    | instance its own private cache and quietly never hit.
    |
    */

    'cache' => [
        'store' => env('LLM_CACHE_STORE', 'database'),
        'ttl_seconds' => (int) env('LLM_CACHE_TTL', 900),
        'enabled' => (bool) env('LLM_CACHE_ENABLED', true),
    ],

    'guardrails' => [
        'max_input_chars' => 2000,
        'max_output_chars' => 8000,
        'blocked_input_fragments' => [
            'ignore all previous instructions',
            'ignore previous instructions',
            'reveal your system prompt',
        ],
        'blocked_output_fragments' => [
            'as a language model',
        ],
    ],

    'observability' => [
        'log_successful_runs' => (bool) env('LLM_LOG_SUCCESS', true),
    ],

];
