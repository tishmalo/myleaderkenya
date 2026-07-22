<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'fix.token' => \App\Http\Middleware\FixApiTokenHeader::class,
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'superadmin' => \App\Http\Middleware\EnsureUserIsSuperAdmin::class,
            'permission' => \App\Http\Middleware\EnsureUserHasPermission::class,
            'aspirant' => \App\Http\Middleware\EnsureUserIsAspirant::class,
        ]);
        $middleware->prepend(\App\Http\Middleware\FixApiTokenHeader::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
    $exceptions->shouldRenderJsonWhen(function ($request, $e) {
        return $request->is('api/*') || $request->wantsJson();
    });
}) ->create();

    // ->withRouting(
    // web: __DIR__.'/../routes/web.php',
    // api: __DIR__.'/../routes/api.php',          // ← ADD THIS LINE
    // apiPrefix: 'api',                           // ← optional but recommended (sets /api prefix automatically)
    // commands: __DIR__.'/../routes/console.php',
    // health: '/up',
// )
