<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // The platform terminates TLS and forwards plain HTTP to the container,
        // so every request arrives with `X-Forwarded-Proto: https` and a
        // transport that looks like http. Without trusting the proxy, Laravel
        // believes the connection is insecure and emits every generated URL
        // with an `http://` scheme — including the Vite asset tags. The
        // browser then refuses to load them as mixed active content, and the
        // page renders unstyled with no JavaScript.
        //
        // `at: '*'` is the right setting here because the trusted hop is a
        // platform edge whose address is not fixed and cannot be listed.
        $middleware->trustProxies(at: '*');

        $middleware->web(append: [
            SetLocale::class,
            HandleInertiaRequests::class,
        ]);

        $middleware->api(prepend: [
            SetLocale::class,
        ]);

        // The assistant is the only state-changing endpoint surface, and it is
        // reachable by anyone on the internet. Throttling here is cheaper than
        // discovering the bill in the provider dashboard.
        $middleware->throttleApi('60,1');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
