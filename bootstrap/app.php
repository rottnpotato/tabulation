<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->use([
            // Global middleware
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        $middleware->api(append: [
            // API middleware
        ]);

        $middleware->alias([
            'check_role' => \App\Http\Middleware\CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->respond(function (\Symfony\Component\HttpFoundation\Response $response, \Throwable $exception, \Illuminate\Http\Request $request) {
            // Handle 403 Forbidden errors
            if ($response->getStatusCode() === 403 && ! $request->expectsJson()) {
                return \Inertia\Inertia::render('Errors/403', [
                    'message' => $exception->getMessage() ?: "You don't have permission to access this resource.",
                ])
                    ->toResponse($request)
                    ->setStatusCode(403);
            }

            // Handle 419 CSRF token mismatch
            if ($response->getStatusCode() === 419 && ! $request->expectsJson()) {
                return back()->with([
                    'message' => 'The page expired, please try again.',
                ]);
            }

            return $response;
        });
    })->create();
