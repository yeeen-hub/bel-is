<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        // ── Session ownership validation ──────────────────────────────────────
        // Runs on every authenticated web request. Terminates sessions whose
        // ID no longer matches the one stored on the user record — this covers:
        //   • Forced logout by admin (current_session_id set to NULL)
        //   • Login from a second device (session ID replaced in DB)
        //   • Any stale auth cookie that survived a tab close on HTTP
        $middleware->web(append: [
            \App\Http\Middleware\ValidateSessionOwnership::class,
        ]);

        // ── Spatie permission middleware aliases ──────────────────────────────
        $middleware->alias([
            'role'       => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();