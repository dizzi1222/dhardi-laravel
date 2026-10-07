# dhardi-laravel

A multilingual portfolio built as a real Laravel application rather than a
static site that mentions Laravel. It exists to answer one question with
evidence instead of adjectives: *can this person ship and operate Laravel in
production?*

The live site is in German, Spanish and English. The AI assistant you can talk
to on it runs through a reliability layer that is instrumented, tested and
measured — which is the part of the stack I want to be judged on.

> **Where I stand honestly.** My production backend background is Node.js with
> Express, TypeORM and PostgreSQL. This application is my first production
> Laravel base. It is built in public, with a running test suite and real
> monitoring, instead of in a private repository. The site says the same thing
> in German, Spanish and English.

---

## Table of contents

- [Stack](#stack)
- [Notes from running this on Vercel](#notes-from-running-this-on-vercel)
- [Requirements](#requirements)
- [Quickstart](#quickstart)
- [Environment variables](#environment-variables)
- [Databases](#databases)
- [Content and translations](#content-and-translations)
- [Multi-tenancy](#multi-tenancy)
- [The AI layer](#the-ai-layer)
- [Evals](#evals)
- [Tests, linting and CI](#tests-linting-and-ci)
- [Deploying to Vercel](#deploying-to-vercel)
- [Serverless constraints](#serverless-constraints)
- [Project structure](#project-structure)
- [Troubleshooting](#troubleshooting)

---

## Stack

| Layer      | Choice                                                      |
| ---------- | ----------------------------------------------------------- |
| Backend    | Laravel 13.34, PHP 8.4                                      |
| Frontend   | React 19, TypeScript (strict), Inertia 3, Tailwind CSS 4    |
| Data       | MySQL in production, SQLite for local development           |
| Tests      | PHPUnit 12, one suite for unit and feature tests           |
| Deployment | Vercel, container runtime, FrankenPHP + Caddy inside Docker |

**Live:** <https://dhardi-laravel.vercel.app> · **Repo:** <https://github.com/dizzi1222/dhardi-laravel>

---

## Notes from running this on Vercel

Three things cost real time and are worth writing down, because each one looks
healthy until it does not.

### `APP_KEY` must be a `config` variable, not a `secret`

Vercel masks `secret` variables when injecting them into the container, so
`APP_KEY` arrives as the literal string `[SENSITIVE]`.
`EncryptionServiceProvider` cannot decode it, and every route that touches the
encrypter returns a 500 while the health check and the API endpoints keep
answering. That split is what makes it hard to spot from the outside.

```bash
vercel env add APP_KEY production --type=config --value "base64:$(openssl rand -base64 32)"
```

### A Caddy site address that parses is not the same as one that works

Three spellings of the same address, all of which start a server:

| Address                        | Behaviour                                                     |
| ------------------------------ | ------------------------------------------------------------- |
| `:80`                          | Treated as a domain named `80`, retries a certificate for 3 days |
| `http://:80`                   | Works. TLS and the HTTPS redirect are off, one handler per request |
| `http://{$PORT:80}`            | **200 with a zero-length body on every route.** PHP never runs |

The last one is the dangerous one: the config adapts without complaint and every
request returns a success status. `try_files` inside `php_server` has the same
effect for a different reason — it resolves and serves before handing off to PHP.

### Reading a prompt back from the database

`CompanionService` originally assembled its message list by reading the
transcript back out. On an instance where the transcript cannot be written, the
history came back empty, the gateway received a prompt with no user turn, and
the input guardrails never saw the text they exist to reject. A prompt-injection
filter that only runs when the database happens to be writable is not a control.
The question is now placed in memory and the stored history is only a
supplement.

### Trusting the proxy, or the page loads blank

Without `trustProxies`, the platform's TLS termination makes Laravel believe
every request is insecure, and every generated URL comes out as `http://` —
including the Vite asset tags. The browser blocks those as mixed active content,
so the document arrives, returns 200, and renders with no styles and no
JavaScript.

The reason this is worth a test is that no status code reports it.
`GeneratedUrlsTest` asserts on the scheme inside the markup, and it reproduces
the real shape of the request: an `http` URL plus an `X-Forwarded-Proto` header.
Asking for an `https` URL directly would hide the bug, because the framework
would never consult the header at all.

---

## Requirements

| Tool       | Version                    | Notes                                          |
| ---------- | -------------------------- | ---------------------------------------------- |
| PHP        | 8.4                        | Needs `pdo_mysql`, `pdo_sqlite`, `intl`, `mbstring`, `dom` |
| Composer   | 2.x                        |                                                |
| Node.js    | 20 or newer                | Built and tested on 24                         |
| npm        | 10 or newer                | A `package-lock.json` is committed             |

Verify before starting:

```bash
php -v
composer -V
node -v
npm -v
```

## Quickstart

From a clean checkout:

```bash
# 1. Dependencies
composer install
npm install

# 2. Environment
cp .env.example .env
php artisan key:generate

# 3. Database — SQLite locally, so there is nothing to install
touch database/database.sqlite
php artisan migrate --seed

# 4. Run it
composer run dev
```

`composer run dev` starts the PHP server, the queue worker and the Vite dev
server together. `http://localhost:8000` serves the site, and with no AI
credentials configured the assistant answers through the deterministic
responder, so every feature is usable immediately.

> **Building the assets is not optional.** The Blade shell calls `@vite`, which
> throws when `public/build/manifest.json` is missing. If the page returns a 500
> about a missing manifest, run `npm run build` once before `php artisan serve`.

To run the pieces separately:

```bash
npm run dev                  # asset build with hot reload
php artisan serve            # application only, expects a prior `npm run build`
php artisan queue:work       # if you add queued work
```

### Production build

```bash
npm run build                # compiles into public/build
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
```

## Environment variables

Everything is documented in [`.env.example`](.env.example). The ones that are
not obvious:

| Variable                              | Why it matters                                                                                     |
| ------------------------------------- | ------------------------------------------------------------------------------------------------- |
| `APP_LOCALE`                          | Default language. German, because it is the primary language of the site.                            |
| `TENANT_SLUG`                         | Which customer this instance serves. One deployment per customer, so it is baked in.                 |
| `ANTHROPIC_API_KEY`                   | Enables the primary model backend. Optional.                                                       |
| `OPENROUTER_API_KEY`                  | Enables the fallback backend. Optional.                                                            |
| `LLM_MAX_ATTEMPTS`                    | Attempt budget per backend before the chain moves on.                                              |
| `LLM_BREAKER_THRESHOLD`               | Consecutive failures that trip the circuit breaker.                                                |
| `LLM_BREAKER_COOLDOWN`                | Seconds before a tripped breaker admits a probe.                                                   |
| `LLM_CACHE_TTL`                       | Seconds a prompt answer is reused.                                                                 |
| `PORTFOLIO_TEST_COUNT`                | Test count shown on the site. Update it when the suite grows.                                      |

**No credentials are required to run the project.** With none of them set the
chain falls through to the deterministic responder. That is deliberate: a demo
that needs an API key to work is not much of a demo.

## Databases

Local development uses SQLite and needs no service:

```bash
touch database/database.sqlite
php artisan migrate --seed
```

Production uses MySQL. Only the connection changes; the schema and every query
in the project are portable:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dhardi_laravel
DB_USERNAME=…
DB_PASSWORD=…
```

On a serverless platform there is no local disk and no bundled database, so both
`DB_HOST` and the cache/session stores point at a managed service. Use a
MySQL-compatible serverless provider and set the variables above.

Migrations are idempotent and are expected to run on every deploy:

```bash
php artisan migrate --force
```

## Content and translations

All copy lives in the lang files and in the content JSON. There is deliberately
no second copy of any string in the React components, because two sources of
truth is how a translation silently goes stale.

```
lang/{de,es,en}/           interface copy, one file per concern
database/content/          experiences, projects, skills, certifications, evals
```

`lang/` is nested (`ui.php`, `fit.php`, `assistant.php`, …). The UI strings
reach the client through a shared Inertia prop and are resolved with a small
helper:

```ts
const t = useT();
t('ui.sections.projects.heading');
```

A missing key renders the key itself rather than an empty string, so a gap is
visible on the page instead of silently disappearing.

`database/content/*.json` holds the content with every locale **side by side**,
which makes a missing translation obvious while editing:

```json
{
  "slug": "pce-agencia",
  "name": "PCE-Agencia",
  "summary": {
    "de": "Betriebs- und Buchungssystem für eine Reiseagentur …",
    "es": "Sistema de gestión y reservas para una agencia de viajes …",
    "en": "An operations and booking system for a travel agency …"
  }
}
```

Content is loaded by `ContentSeeder`, which matches on the stable slug. Running
it again updates the copy instead of duplicating it.

### Adding a language

1. Create `lang/<code>/` and copy the files from `lang/de/`.
2. Register it in `config/tenancy.php` under `locales`.
3. Add the code to `assistant.intents` translations and to the content JSON.
4. Run `php artisan db:seed --force`.

`tests/Feature/TranslationCoverageTest.php` fails if a key is missing in any
registered locale, so this is caught by CI rather than by a visitor.

## Multi-tenancy

The model is **one deployment per customer**, which is why each customer already
gets their own instance. Content is scoped with a global scope, so isolation does
not depend on remembering to add a `where`:

```php
App\Models\Concerns\BelongsToTenant::class
```

The scope reads the current tenant from `App\Support\TenantContext`, resolved
from the session and then from `TENANT_SLUG`. The creating hook refuses to write
a row without a tenant, rather than defaulting to one.

Standing up a new customer instance:

```bash
php artisan tenant:provision acme \
  --name="Acme AG" \
  --locale=de
```

That creates the tenant, migrates, seeds the content and prints the environment
block to set on the new deployment. Getting a new customer running is one
command, not a project day.

## The AI layer

The assistant on the site is a real feature, not a mock. The interesting part
is not the model call — it is what happens when the model call fails.

Every request goes through `App\Llm\LlmGateway`, in this order:

1. **Input guardrails** — length, control characters, prompt-injection
   fragments. Rejected before anything spends money.
2. **Prompt cache** — keyed by a hash of the full request fingerprint *and* the
   tenant, so one customer's cache can never serve another's answer, and a change
   to the system prompt invalidates correctly.
3. **Backend chain** — tried in order, skipping any backend without credentials:

   | Backend          | Role                                             |
   | ---------------- | ------------------------------------------------ |
   | `anthropic`      | Primary model                                     |
   | `openrouter`     | Cheaper second tier                               |
   | `deterministic`  | Always available, no network, answers from content |

4. **Circuit breaker** per backend. After `LLM_BREAKER_THRESHOLD` consecutive
   failures the backend is skipped. Once the cooldown expires, **exactly one**
   probe is admitted, so a recovering backend is not immediately flooded again.
5. **Bounded retries** with exponential backoff and full jitter. A
   `Retry-After` from the provider overrides the local curve. Only failures
   declared retryable are retried — a 400 is the caller's fault and retrying it
   only burns quota.
6. **Output guardrails** — the model's text is untrusted input for everything
   downstream, so it is validated before it reaches a browser or a database.
7. **Observability** — one `llm_runs` row per attempt, with latency, tokens,
   cost, outcome and error class, whatever happened.

The panel on the home page is a query against `llm_runs`. Nothing there is
hard-coded, so it cannot drift from what actually happened.

Design notes worth reading:

- `RetryPolicy` takes a `Sleeper`, so backoff is asserted in tests without the
  suite actually sleeping.
- A guardrail violation is **not** retried. Refusing is a correct outcome, and
  retrying identical text cannot change it.
- Streaming does not fall back mid-stream. Once tokens have reached the client,
  appending a second answer to a half-written sentence would be worse than an
  error.

## Evals

A prompt change or a model swap is a silent regression until something measures
it. `database/content/evals.json` holds the golden dataset: a prompt plus the
fragments a correct answer must contain, per locale.

```bash
php artisan evals:run
```

It replays every active case against the live chain, writes a row per case so
two runs can be compared, and **exits non-zero** when a case fails. That is what
makes it usable in CI rather than as a console curiosity.

## Tests, linting and CI

```bash
php artisan test           # or:
vendor/bin/pint            # format PHP
vendor/bin/pint --test     # check only
npx tsc --noEmit           # typecheck TypeScript
```

The suite covers the parts where a mistake is expensive:

| Area                | What is asserted                                                     |
| ------------------- | -------------------------------------------------------------------- |
| `RetryPolicy`       | retries transient failures, gives up on non-retryable ones, honours `Retry-After`, jitter stays within the ceiling |
| `CircuitBreaker`    | opens after the threshold, admits one probe while half-open, closes on success |
| `FallbackChain`     | the chain degrades to a lesser answer instead of erroring            |
| `PromptCache`       | hit and miss, tenant isolation, system-prompt invalidation            |
| `Guardrails`        | oversized input, blocked fragments, empty output                     |
| `LlmGateway`        | ordering of the stages, blocked output, audit rows                   |
| Tenant resolution   | env fallback, session override, refusal to write without a tenant    |
| Endpoints           | assistant blocking and streaming, telemetry shape                    |
| Translations        | no key missing in any registered locale                              |

## Deploying to Vercel

The app ships as a container: `Dockerfile.vercel` builds it with FrankenPHP, and
`Caddyfile` exposes only `public/` and routes everything else to
`public/index.php`.

```bash
vercel deploy --prod
```

Or import the repository in the Vercel dashboard — the platform reads
`vercel.json`, `Dockerfile.vercel` and `Caddyfile` from the project root.

Set these as project environment variables:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_KEY=            # stable, so cookies stay decryptable across deploys
APP_URL=https://…
TENANT_SLUG=dhardi
LOG_CHANNEL=stderr
CACHE_STORE=database
SESSION_DRIVER=database
DB_CONNECTION=mysql
DB_HOST=…
DB_PORT=3306
DB_DATABASE=…
DB_USERNAME=…
DB_PASSWORD=…
ANTHROPIC_API_KEY=          # optional
OPENROUTER_API_KEY=         # optional
```

Two settings are easy to get wrong:

- `APP_DEBUG=false` in production.
- Do **not** run `php artisan optimize` while building the image. Config caching
  resolves environment variables at the time it runs, so doing it at build time
  bakes in whatever was set during the build instead of what exists at runtime.
  Cache the routes instead.

## Serverless constraints

This is a stateless application and treats the platform as such:

| Constraint         | Handling                                                                   |
| ------------------ | -------------------------------------------------------------------------- |
| No durable disk    | Nothing is written to disk. Uploads would go to object storage.            |
| Multiple instances | Session and cache use the database store, which is shared.                 |
| Logs               | `LOG_CHANNEL=stderr`, so they reach the platform's runtime log.            |
| No long processes  | Queue work is dispatched over HTTP or handled synchronously.              |
| Cold starts        | Dependencies are installed in the image, not at runtime.                  |

## Project structure

```
app/
  Assistant/        the companion as a product: history, prompts, persistence
  Http/             middleware, requests, controllers
  Llm/              the model layer
    Contracts/      provider interface, request and response DTOs
    Exceptions/     typed errors that carry the retry decision
    Providers/      anthropic, openrouter, deterministic
    Reliability/    retry, breaker, cache, guardrails, fallback chain
  Models/           tenant-scoped models
  Portfolio/        assembles the page payload from the database
  Support/          tenant resolution
database/
  content/          seed content, all locales side by side
lang/{de,es,en}/    interface copy
resources/
  js/Pages/         Inertia pages
  js/components/    section components
  js/lib/           i18n helper, streaming hook
```

## Troubleshooting

**The assistant answers instantly and mentions no model.** No credentials are
configured, so the chain resolved to the deterministic responder. That is the
expected behaviour, not a bug.

**`No tenant resolved for the current instance.`** `TENANT_SLUG` is unset and no
session tenant exists. Seed the database, or set the variable.

**The page renders without styles.** The build is missing. Run `npm run build`,
or `npm run dev` while working locally.

**The assistant returns 422 with a rule name.** An input guardrail rejected the
prompt. The rule is in the response; `config/llm.php` lists the fragments.

**Every HTML route returns 500 but the API endpoints work.**  is
probably a  variable, which Vercel injects as the literal string
. Recreate it as . See the notes section above.

**The assistant answers but nothing is recorded.** Expected: on a platform with
no durable disk the audit rows and transcripts degrade to log lines rather than
failing the request. That is the  path working.

**Cold starts are slow.** The image is doing work that belongs at build time.
Check that 
  [37;44m INFO [39;49m Discovering packages.  

  inertiajs/inertia-laravel [90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m [32;1mDONE[39;22m
  laravel/tinker [90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m [32;1mDONE[39;22m
  nesbot/carbon [90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m [32;1mDONE[39;22m
  nunomaduro/termwind [90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m[90m.[39m [32;1mDONE[39;22m ran in the image, not at runtime.