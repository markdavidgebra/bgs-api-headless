<?php

use App\Http\Middleware\EnsureAdminAccountApproved;
use App\Http\Middleware\EnsureAdminHasPermission;
use App\Http\Middleware\EnsureAdminHasRole;
use App\Http\Middleware\EnsureDoctorAccountApproved;
use App\Http\Middleware\PreventCrossGuardAccess;
use App\Http\Middleware\RedirectIfAuthenticated;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Console\Scheduling\Schedule;
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
            'admin_approved' => EnsureAdminAccountApproved::class,
            'doctor_approved' => EnsureDoctorAccountApproved::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('notifications:appointment-reminders')->dailyAt('08:00');
        $schedule->command('notifications:admin-low-stock')->weekdays()->at('09:00');
        $schedule->command('notifications:admin-approval-digest')->dailyAt('07:30');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
