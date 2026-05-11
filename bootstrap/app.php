<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \App\Http\Middleware\DebugRouteResolution::class,
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'school.domain' => \App\Http\Middleware\ResolveSchoolByDomain::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (\Illuminate\Routing\Exceptions\UrlGenerationException $e, $request) {
            file_put_contents('/tmp/route_debug.log', json_encode([
                'timestamp' => date('Y-m-d H:i:s'),
                'event' => 'url_generation_exception',
                'exception_message' => $e->getMessage(),
                'uri' => $request->getRequestUri(),
                'host' => $request->getHost(),
                'session_school_id' => $request->session()->get('school_id'),
            ]) . "\n", FILE_APPEND);

            return null; // Let Laravel handle it normally
        });
    })->create();
