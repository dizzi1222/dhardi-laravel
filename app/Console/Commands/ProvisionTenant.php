<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Support\TenantContext;
use Database\Seeders\ContentSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

/**
 * Stands up a new customer instance.
 *
 * This command exists because "set up a new customer" is the operation a
 * per-customer deployment model makes frequent. Turning it into one command
 * with a printed environment block is what keeps it from being a project.
 */
final class ProvisionTenant extends Command
{
    protected $signature = 'tenant:provision
        {slug : Short, unique identifier for the customer}
        {--name= : Display name of the customer}
        {--locale=de : Default locale for this instance}
        {--plan=standard : Plan identifier}
        {--no-migrate : Skip running migrations against the target database}
        {--force : Provision even if the slug already exists}';

    protected $description = 'Create a new customer instance, seed its content and print the environment block for its deployment';

    public function handle(): int
    {
        $slug = (string) $this->argument('slug');

        if (! preg_match('/^[a-z0-9][a-z0-9-]{1,40}$/', $slug)) {
            $this->components->error('The slug must be lowercase alphanumeric with dashes, 2 to 41 characters.');

            return self::FAILURE;
        }

        if (! in_array((string) $this->option('locale'), array_keys((array) config('tenancy.locales')), true)) {
            $this->components->error('Unknown locale: '.$this->option('locale'));

            return self::FAILURE;
        }

        $existing = Tenant::query()->where('slug', $slug)->exists();

        if ($existing && ! $this->option('force')) {
            $this->components->error("Tenant [{$slug}] already exists. Use --force to reprovision it.");

            return self::FAILURE;
        }

        $tenant = Tenant::query()->updateOrCreate(
            ['slug' => $slug],
            [
                'name' => (string) ($this->option('name') ?: $slug),
                'default_locale' => (string) $this->option('locale'),
                'brand_name' => $this->option('name') ?: null,
                'brand_accent' => '#C8102E',
                'plan' => (string) $this->option('plan'),
                'is_active' => true,
            ],
        );

        // The seeder writes tenant-scoped rows, and there is no session or
        // environment variable to resolve the tenant from inside a console run.
        app(TenantContext::class)->use($tenant);

        if (! $this->option('no-migrate')) {
            Artisan::call('migrate', ['--force' => true], $this->output->isVerbose() ? $this->output : null);
        }

        (new ContentSeeder($tenant))->run();

        $this->components->info("Tenant [{$slug}] is ready.");
        $this->components->twoColumnDetail('experiences', (string) $tenant->experiences()->count());
        $this->components->twoColumnDetail('projects', (string) $tenant->projects()->count());
        $this->components->twoColumnDetail('eval cases', (string) $tenant->evalCases()->count());

        $this->newLine();
        $this->line('  Set these on the new deployment:');
        $this->newLine();

        foreach ([
            'TENANT_SLUG='.$slug,
            'APP_LOCALE='.$tenant->default_locale,
            'APP_ENV=production',
            'APP_DEBUG=false',
        ] as $line) {
            $this->line('    '.$line);
        }

        $this->newLine();
        $this->line('  Database, session and cache credentials are shared with the platform.');
        $this->line('  Every other variable in .env.example applies unchanged.');

        return self::SUCCESS;
    }
}
