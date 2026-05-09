<?php

use App\Http\Middleware\EnsureAdminHasRole;
use App\Http\Middleware\EnsureAdminHasPermission;
use App\Http\Middleware\PreventCrossGuardAccess;
use App\Http\Middleware\RedirectIfAuthenticated;
use Illuminate\Auth\Middleware\Authenticate;
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
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'api/pos/login',
            'api/pos/logout',
            'pos/login',
            'pos/logout',
        ]);

        $middleware->alias([
            'auth' => Authenticate::class,
            'guest' => RedirectIfAuthenticated::class,
            'prevent_cross_guard' => PreventCrossGuardAccess::class,
            'admin_role' => EnsureAdminHasRole::class,
            'admin_permission' => EnsureAdminHasPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
