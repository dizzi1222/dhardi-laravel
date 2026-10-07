<?php

declare(strict_types=1);

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProjectController;
use App\Models\Tenant;
use App\Support\TenantContext;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

/*
|--------------------------------------------------------------------------
| Portfolio
|--------------------------------------------------------------------------
*/

Route::get('/', HomeController::class)->name('home');

Route::get('/projekt/{project:slug}', [ProjectController::class, 'show'])
    ->name('projects.show');

/*
|--------------------------------------------------------------------------
| Lebenslauf
|--------------------------------------------------------------------------
|
| A printable, server-rendered version of the CV. It exists next to the PDF
| because that PDF has no text layer: it was produced by a vector editor from a
| document whose text had already been converted to outlines, so nothing in it
| can be selected, searched, translated or read by a screen reader.
|
| Rendering real text fixes all four, and it still prints to PDF from the
| browser's own print dialog if a PDF file is what someone actually needs.
|
*/

Route::get('/lebenslauf', function (): Response {
    $tenant = Tenant::query()->where('slug', config('tenancy.fallback', 'dhardi'))->first();

    if ($tenant !== null) {
        app(TenantContext::class)->use($tenant);
    }

    return Inertia::render('Lebenslauf', [
        'meta' => [
            'title' => 'Lebenslauf — '.config('portfolio.public_identity.display_name'),
            'description' => (string) trans('cv.meta_description'),
        ],
        'cv' => [
            'role' => (string) trans('cv.role'),
            'location' => (string) trans('cv.location'),
            'availability' => (string) trans('cv.availability'),
            'print' => (string) trans('cv.print'),
            'notice_title' => (string) trans('cv.notice_title'),
            'notice_body' => (string) trans('cv.notice_body'),
            'sections' => [
                'profil' => trans('cv.sections.profil'),
                'stack' => trans('cv.sections.stack'),
                'idiomas' => trans('cv.sections.idiomas'),
                'enlaces' => trans('cv.sections.enlaces'),
                'portfolio' => trans('cv.sections.portfolio'),
                'experiencia' => trans('cv.sections.experiencia'),
                'educacion' => trans('cv.sections.educacion'),
            ],
            'footer_note' => (string) trans('cv.footer_note'),
        ],
        'displayName' => (string) config('portfolio.public_identity.display_name'),
        'email' => (string) config('portfolio.links.email'),
        'phone' => '+1 829-821-6385',
    ]);
})->name('lebenslauf');
