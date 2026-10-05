<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * A single project, on its own route so it can be linked to directly.
 *
 * Depth is the point: a list entry cannot show how a decision was reached, and
 * that is the part a technical interviewer actually wants to interrogate.
 */
final class ProjectController extends Controller
{
    public function show(Request $request, Project $project): Response
    {
        $project->load('translations');

        return Inertia::render('ProjectShow', [
            'project' => [
                'name' => $project->name,
                'role' => $project->role,
                'repo_url' => $project->is_public_repo ? $project->repo_url : null,
                'live_url' => $project->live_url,
                'year' => $project->year,
                'status' => $project->status,
                'stack' => $project->stack ?? [],
                'metrics' => $project->metrics ?? [],
                'summary' => $project->translate('summary'),
                'problem' => $project->translate('problem'),
                'approach' => $project->translate('approach'),
                'highlights' => $project->translateList('highlights'),
            ],

            'all' => Project::query()
                ->with('translations')
                ->where('id', '!=', $project->getKey())
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->get()
                ->map(fn (Project $other): array => [
                    'slug' => $other->slug,
                    'name' => $other->name,
                    'year' => $other->year,
                    'summary' => $other->translate('summary'),
                ])
                ->all(),

            'meta' => [
                'title' => $project->name.' — '.trans('meta.site_short'),
                'description' => (string) $project->translate('summary'),
            ],
        ]);
    }
}
