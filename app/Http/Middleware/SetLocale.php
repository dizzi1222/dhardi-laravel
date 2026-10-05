<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the active language for the request.
 *
 * Runs in both the web and the api groups, and the api group is stateless — so
 * the session is treated as optional rather than assumed. Asking an api request
 * for its session used to throw "Session store not set on request", which meant
 * the assistant endpoints were unreachable without a cookie.
 *
 * Priority is deliberate: an explicit choice the visitor made is honoured first,
 * then the tenant's configured default, then the browser's preference. German
 * is the primary language of the site, so a visitor with no signal at all gets
 * German rather than the framework default of English.
 */
final class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = array_keys((array) config('tenancy.locales'));

        $candidate = $this->sessionLocale($request)
            ?? $this->queryLocale($request)
            ?? $this->tenantLocale()
            ?? $this->browserLocale($request);

        // An unrecognised `?lang=` must not reach the lang loader: it would fall
        // through to the framework default and quietly render the page in
        // English, which is the one outcome this middleware exists to prevent.
        $locale = in_array($candidate, $supported, true)
            ? $candidate
            : (string) config('tenancy.provisioning.default_locale', 'de');

        app()->setLocale($locale);

        if ($request->has('lang') && $request->hasSession()) {
            $request->session()->put('locale', $locale);
        }

        // Force the application and the lang loader onto this request's locale.
        // A long-lived worker — queue, Octane, a test process — boots the app
        // once and would otherwise keep the locale of whichever request arrived
        // first, so the language switch would silently do nothing.
        app()->setLocale($locale);

        return $next($request);
    }

    private function sessionLocale(Request $request): ?string
    {
        if (! $request->hasSession()) {
            return null;
        }

        $value = $request->session()->get('locale');

        return is_string($value) ? $value : null;
    }

    private function queryLocale(Request $request): ?string
    {
        $value = $request->query('lang');

        return is_string($value) && $value !== '' ? $value : null;
    }

    private function tenantLocale(): ?string
    {
        return app(TenantContext::class)->tenant()?->default_locale;
    }

    private function browserLocale(Request $request): ?string
    {
        if (! config('tenancy.detect_from_browser')) {
            return null;
        }

        $header = $request->header('Accept-Language');

        if (! is_string($header) || $header === '') {
            return null;
        }

        $supported = array_keys((array) config('tenancy.locales'));

        foreach (explode(',', $header) as $part) {
            $tag = strtolower(trim(explode(';', $part)[0]));
            $primary = explode('-', $tag)[0];

            if (in_array($primary, $supported, true)) {
                return $primary;
            }
        }

        return null;
    }
}
