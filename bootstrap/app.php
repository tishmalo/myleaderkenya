<?php

use App\Http\Middleware\AuditMutatingRequests;
use App\Http\Middleware\EnsureUserHasPermission;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsAspirant;
use App\Http\Middleware\EnsureUserIsPartyOfficial;
use App\Http\Middleware\EnsureUserIsSuperAdmin;
use App\Http\Middleware\EnsureUserOwnsActiveCandidate;
use App\Http\Middleware\FixApiTokenHeader;
use App\Http\Middleware\PreventStaleCsrfPages;
use App\Http\Middleware\EnsurePulseEngineApiKey;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;

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
            'fix.token' => FixApiTokenHeader::class,
            'pulse.engine' => EnsurePulseEngineApiKey::class,
            'admin' => EnsureUserIsAdmin::class,
            'superadmin' => EnsureUserIsSuperAdmin::class,
            'permission' => EnsureUserHasPermission::class,
            'aspirant' => EnsureUserIsAspirant::class,
            'aspirant.owner' => EnsureUserOwnsActiveCandidate::class,
            'party' => EnsureUserIsPartyOfficial::class,
        ]);
        $middleware->prepend(FixApiTokenHeader::class);
        $middleware->appendToGroup('web', [PreventStaleCsrfPages::class, AuditMutatingRequests::class]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(function ($request, $e) {
            return $request->is('api/*') || $request->wantsJson();
        });
        $exceptions->render(function (TokenMismatchException $exception, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Your session expired. Refresh the page and try again.'], 419);
            }

            $target = match (true) {
                $request->is('login') => route('landing', ['auth' => 'login']),
                $request->is('register') => route('landing', ['auth' => 'register']),
                default => url()->previous() ?: route('landing'),
            };

            return redirect()->to($target)->with('warning', 'Your session expired. Please try again.');
        });
    })->create();

// ->withRouting(
// web: __DIR__.'/../routes/web.php',
// api: __DIR__.'/../routes/api.php',          // ← ADD THIS LINE
// apiPrefix: 'api',                           // ← optional but recommended (sets /api prefix automatically)
// commands: __DIR__.'/../routes/console.php',
// health: '/up',
// )
