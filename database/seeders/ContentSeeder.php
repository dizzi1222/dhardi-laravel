<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Certification;
use App\Models\Experience;
use App\Models\LlmEvalCase;
use App\Models\Project;
use App\Models\Skill;
use App\Models\Tenant;
use App\Support\TenantContext;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Loads the content files for one tenant.
 *
 * Idempotent by design: every entity is matched on its stable slug, so running
 * the seeder again updates the copy instead of duplicating it. That property is
 * what lets `tenant:provision` reuse this same code path to stand up a new
 * customer instance.
 *
 * The content files hold every locale side by side, which makes a missing
 * translation visible when editing rather than at render time.
 */
final class ContentSeeder extends Seeder
{
    public function __construct(private readonly Tenant $tenant) {}

    public function run(): void
    {
        app(TenantContext::class)->use($this->tenant);

        $this->experiences($this->read('experiences'));
        $this->projects($this->read('projects'));
        $this->skills($this->read('skills'));
        $this->certifications($this->read('certifications'));
        $this->evalCases($this->read('evals'));

        $this->tenant->forceFill(['default_locale' => 'de'])->save();
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function experiences(array $rows): void
    {
        foreach ($rows as $row) {
            $experience = Experience::query()->updateOrCreate(
                ['slug' => $row['slug']],
                [
                    'tenant_id' => $this->tenant->getKey(),
                    'role' => $row['role']['en'],
                    'organisation' => $this->scalarise($row['organisation']),
                    'organisation_url' => $this->scalarise($row['organisation_url'] ?? null),
                    'employment_type' => $this->scalarise($row['employment_type'] ?? null),
                    'location' => $this->scalarise($row['location'] ?? null),
                    'is_remote' => (bool) ($row['is_remote'] ?? false),
                    'start_date' => $row['start_date'],
                    'end_date' => $row['end_date'] ?? null,
                    'sort_order' => $row['sort_order'] ?? 0,
                    'stack' => $row['stack'] ?? [],
                ],
            );

            foreach ($row['summary'] as $locale => $summary) {
                $experience->translations()->updateOrCreate(
                    ['locale' => $locale],
                    ['summary' => $summary, 'highlights' => $row['highlights'][$locale] ?? []],
                );
            }
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function projects(array $rows): void
    {
        foreach ($rows as $row) {
            $project = Project::query()->updateOrCreate(
                ['slug' => $row['slug']],
                [
                    'tenant_id' => $this->tenant->getKey(),
                    // Project names are proper nouns of the product and are not
                    // translated; "this website" is the one exception.
                    'name' => $this->localised($row['name'], config('tenancy.provisioning.default_locale', 'de')),
                    'role' => $this->localised($row['role'] ?? null, 'en'),
                    'repo_url' => $row['repo_url'] ?? null,
                    'live_url' => $row['live_url'] ?? null,
                    'case_study_url' => $row['case_study_url'] ?? null,
                    'year' => $row['year'],
                    'status' => $row['status'],
                    'is_featured' => (bool) ($row['is_featured'] ?? false),
                    'is_public_repo' => (bool) ($row['is_public_repo'] ?? true),
                    'sort_order' => $row['sort_order'] ?? 0,
                    'stack' => $row['stack'] ?? [],
                    'metrics' => $row['metrics'] ?? [],
                ],
            );

            foreach ($row['summary'] as $locale => $summary) {
                $project->translations()->updateOrCreate(
                    ['locale' => $locale],
                    [
                        'name' => is_array($row['name']) ? ($row['name'][$locale] ?? null) : null,
                        'summary' => $summary,
                        'problem' => $row['problem'][$locale] ?? null,
                        'approach' => $row['approach'][$locale] ?? null,
                        'highlights' => $row['highlights'][$locale] ?? [],
                    ],
                );
            }
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function skills(array $rows): void
    {
        foreach ($rows as $row) {
            Skill::query()->updateOrCreate(
                ['group' => $row['group'], 'name' => $row['name']],
                [
                    'tenant_id' => $this->tenant->getKey(),
                    'proof' => $row['proof']['en'] ?? null,
                    'sort_order' => $row['sort_order'] ?? 0,
                ],
            );
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function certifications(array $rows): void
    {
        foreach ($rows as $row) {
            Certification::query()->updateOrCreate(
                ['name' => $row['name']],
                [
                    'tenant_id' => $this->tenant->getKey(),
                    'issuer' => $row['issuer'] ?? null,
                    'url' => $row['url'] ?? null,
                    'status' => $row['status'],
                    'issued_year' => $row['issued_year'] ?? null,
                    'notes' => $row['note'] ?? null,
                    'sort_order' => $row['sort_order'] ?? 0,
                ],
            );
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function evalCases(array $rows): void
    {
        foreach ($rows as $row) {
            LlmEvalCase::query()->updateOrCreate(
                ['key' => $row['key']],
                [
                    'tenant_id' => $this->tenant->getKey(),
                    'locale' => $row['locale'],
                    'prompt' => $row['prompt'],
                    'expected_contains' => $row['expected_contains'],
                    'is_active' => (bool) ($row['is_active'] ?? true),
                ],
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function read(string $file): array
    {
        $path = database_path('content/'.$file.'.json');

        if (! is_file($path)) {
            throw new RuntimeException("Missing content file: {$path}");
        }

        return json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * Reads a value that may be a plain string or a per-locale object.
     */
    private function localised(mixed $value, string $locale): ?string
    {
        if (is_string($value)) {
            return $value;
        }

        return is_array($value) ? ($value[$locale] ?? null) : null;
    }

    private function scalarise(mixed $value): ?string
    {
        return $this->localised($value, 'en');
    }
}
