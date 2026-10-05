<?php

declare(strict_types=1);

namespace App\Portfolio;

use App\Models\Certification;
use App\Models\Experience;
use App\Models\Project;
use App\Models\Skill;
use Illuminate\Support\Collection;

/**
 * Builds the page payload from the database.
 *
 * Nothing about the copy is assembled in this class: the site renders what the
 * content tables hold, and every string a visitor reads is translatable. That
 * keeps German, Spanish and English from drifting apart as the site grows.
 */
final class PortfolioService
{
    /**
     * @return array<string, mixed>
     */
    public function pagePayload(): array
    {
        return [
            'profile' => $this->profile(),
            'hero' => $this->hero(),
            'fit' => $this->fit(),
            'experiences' => $this->experiences(),
            'projects' => $this->projects(),
            'skills' => $this->skills(),
            'certifications' => $this->certifications(),
            'stackProof' => $this->stackProof(),
        ];
    }

    /**
     * Identity and channels. Sourced from the profile config so that the same
     * facts are available to every renderer.
     *
     * @return array<string, mixed>
     */
    private function profile(): array
    {
        return array_merge(config('portfolio.profile'), [
            'summary' => (string) trans('profile.summary'),
            'languages' => (array) trans('profile.languages'),
            'links' => (array) config('portfolio.links'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function hero(): array
    {
        return [
            'available' => (bool) config('portfolio.available'),
            'headline' => (string) trans('hero.headline'),
            'subheadline' => (string) trans('hero.subheadline'),
            'cta' => (string) trans('hero.cta'),
            'secondaryCta' => (string) trans('hero.secondary_cta'),
        ];
    }

    /**
     * The requirement mapping.
     *
     * Each entry answers one published requirement with a link to the evidence,
     * including the two the candidate does not yet meet. Presenting the gaps
     * alongside the evidence is the only version of this section that survives
     * a technical interview.
     *
     * @return array<int, array<string, mixed>>
     */
    private function fit(): array
    {
        return [
            [
                'requirement' => (string) trans('fit.requirements.laravel'),
                'status' => 'addressed',
                'evidence' => (string) trans('fit.evidence.laravel'),
                'proof' => [
                    ['label' => 'Laravel', 'value' => '13.34'],
                    ['label' => 'PHP', 'value' => '8.4'],
                    ['label' => trans('fit.label_tests'), 'value' => (string) config('portfolio.test_count')],
                    ['label' => trans('fit.label_deploy'), 'value' => (string) trans('fit.vercel')],
                ],
            ],
            [
                'requirement' => (string) trans('fit.requirements.react'),
                'status' => 'addressed',
                'evidence' => (string) trans('fit.evidence.react'),
                'proof' => [
                    ['label' => 'React', 'value' => '19'],
                    ['label' => 'TypeScript', 'value' => 'strict'],
                    ['label' => 'Tailwind', 'value' => '4'],
                ],
            ],
            [
                'requirement' => (string) trans('fit.requirements.llm'),
                'status' => 'addressed',
                'evidence' => (string) trans('fit.evidence.llm'),
                'proof' => [
                    ['label' => trans('fit.label_retries'), 'value' => (string) config('llm.retry.max_attempts')],
                    ['label' => trans('fit.label_fallback'), 'value' => (string) count((array) config('llm.chain'))],
                    ['label' => trans('fit.label_evals'), 'value' => (string) trans('fit.golden_dataset')],
                ],
            ],
            [
                'requirement' => (string) trans('fit.requirements.ownership'),
                'status' => 'addressed',
                'evidence' => (string) trans('fit.evidence.ownership'),
                'proof' => [
                    ['label' => trans('fit.label_prs'), 'value' => '74'],
                    ['label' => trans('fit.label_sprints'), 'value' => '18'],
                    ['label' => trans('fit.label_team'), 'value' => '4'],
                ],
            ],
            [
                'requirement' => (string) trans('fit.requirements.tests'),
                'status' => 'addressed',
                'evidence' => (string) trans('fit.evidence.tests'),
                'proof' => [
                    ['label' => 'Unit', 'value' => (string) trans('fit.yes')],
                    ['label' => 'Feature', 'value' => (string) trans('fit.yes')],
                    ['label' => 'CI', 'value' => (string) trans('fit.yes')],
                ],
            ],
            [
                'requirement' => (string) trans('fit.requirements.german'),
                'status' => 'partial',
                'evidence' => (string) trans('fit.evidence.german'),
                'proof' => [],
            ],
            [
                'requirement' => (string) trans('fit.requirements.location'),
                'status' => 'partial',
                'evidence' => (string) trans('fit.evidence.location'),
                'proof' => [
                    ['label' => trans('fit.label_permit'), 'value' => (string) trans('fit.none_required')],
                    ['label' => trans('fit.label_tz'), 'value' => 'UTC−4'],
                    ['label' => trans('fit.label_overlap'), 'value' => (string) trans('fit.overlap')],
                ],
            ],
            [
                'requirement' => (string) trans('fit.requirements.multitenancy'),
                'status' => 'addressed',
                'evidence' => (string) trans('fit.evidence.multitenancy'),
                'proof' => [],
            ],
            [
                'requirement' => (string) trans('fit.requirements.streaming'),
                'status' => 'addressed',
                'evidence' => (string) trans('fit.evidence.streaming'),
                'proof' => [],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function experiences(): array
    {
        return Experience::query()
            ->with('translations')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Experience $experience): array => [
                'slug' => $experience->slug,
                'role' => $experience->role,
                'organisation' => $experience->organisation,
                'organisation_url' => $experience->organisation_url,
                'location' => $experience->location,
                'is_remote' => $experience->is_remote,
                'employment_type' => $experience->employment_type,
                'start_date' => $experience->start_date->toIso8601String(),
                'end_date' => $experience->end_date?->toIso8601String(),
                'is_current' => $experience->isCurrent(),
                'duration_months' => $experience->durationMonths(),
                'stack' => $experience->stack ?? [],
                'summary' => $experience->translate('summary'),
                'highlights' => $experience->translateList('highlights'),
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function projects(): array
    {
        return Project::query()
            ->with('translations')
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Project $project): array => [
                'slug' => $project->slug,
                'name' => $project->translate('name') ?? $project->name,
                'role' => $project->role,
                'repo_url' => $project->is_public_repo ? $project->repo_url : null,
                'live_url' => $project->live_url,
                'case_study_url' => $project->case_study_url,
                'year' => $project->year,
                'status' => $project->status,
                'is_featured' => $project->is_featured,
                'stack' => $project->stack ?? [],
                'metrics' => $project->metrics ?? [],
                'summary' => $project->translate('summary'),
                'problem' => $project->translate('problem'),
                'approach' => $project->translate('approach'),
                'highlights' => $project->translateList('highlights'),
            ])
            ->all();
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function skills(): array
    {
        return Skill::query()
            ->orderBy('sort_order')
            ->get()
            ->groupBy('group')
            ->map(fn (Collection $group): array => $group
                ->map(fn (Skill $skill): array => [
                    'name' => $skill->name,
                    'proof' => $skill->proof === null ? null : (string) trans($skill->proof),
                ])
                ->all())
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function certifications(): array
    {
        return Certification::query()
            ->orderByDesc('status')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Certification $certification): array => [
                'name' => $certification->name,
                'issuer' => $certification->issuer,
                'url' => $certification->url,
                'status' => $certification->status,
                'issued_year' => $certification->issued_year,
                'note' => $certification->note(),
                'status_label' => (string) trans('ui.cert_status.'.$certification->status),
            ])
            ->all();
    }

    /**
     * Evidence that the primary stack is used in anger rather than listed.
     *
     * @return array<int, array<string, mixed>>
     */
    private function stackProof(): array
    {
        return array_map(
            static fn (array $item): array => $item + [
                'title' => (string) trans($item['title_key']),
                'description' => (string) trans($item['description_key']),
            ],
            (array) config('portfolio.stack_proof'),
        );
    }
}
