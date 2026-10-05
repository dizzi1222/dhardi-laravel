<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Portfolio\PortfolioService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The single page of the portfolio.
 *
 * Inertia rather than a multi-page site: a visitor deciding whether to reply to
 * a job posting reads one screen, and a client-side transition between anchors
 * costs less than maintaining five Blade templates that drift apart.
 */
final class HomeController extends Controller
{
    public function __invoke(Request $request, PortfolioService $portfolio): Response
    {
        return Inertia::render('Home', array_merge(
            $portfolio->pagePayload(),
            ['meta' => $this->meta()],
        ));
    }

    /**
     * @return array<string, string>
     */
    private function meta(): array
    {
        return [
            'title' => (string) trans('meta.title'),
            'description' => (string) trans('meta.description'),
        ];
    }
}
