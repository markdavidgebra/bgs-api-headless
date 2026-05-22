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
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Behind nginx/Cloudflare/Laragon, forwarded proto/host must be trusted or URLs,
        // session cookies (Secure), and Sanctum stateful detection can be wrong.
        $trusted = env('TRUSTED_PROXIES', '*');
        $middleware->trustProxies(at: $trusted === '*'
            ? '*'
            : array_values(array_filter(array_map('trim', explode(',', (string) $trusted)))));

        // Equivalent to adding EnsureFrontendRequestsAreStateful to the `api`
        // middleware group in app/Http/Kernel.php (Laravel 10 and earlier).
        $middleware->statefulApi();

        // POS SPA runs on another subdomain; browser cannot attach X-XSRF-TOKEN from
        // the API host’s cookie to cross-origin requests. Exempt api/pos/* (still protected
        // by auth:admin + admin_role). Web route aliases under /pos/* stay listed explicitly.
        $middleware->validateCsrfTokens(except: [
            'api/pos/*',
            'api/inventory/*',
            'api/doctor/*',
            'pos/login',
            'pos/logout',
            // Web aliases (same SPA as api/pos — session + auth middleware protect these)
            'pos/checkout',
            'pos/affiliate-codes/*',
        ]);

        $middleware->alias([
            'auth' => Authenticate::class,
            'guest' => RedirectIfAuthenticated::class,
            'prevent_cross_guard' => PreventCrossGuardAccess::class,
            'admin_role' => EnsureAdminHasRole::class,
            'admin_permission' => EnsureAdminHasPermission::class,
            'admin_approved' => EnsureAdminAccountApproved::class,
            'doctor_approved' => EnsureDoctorAccountApproved::class,
            'doctor.permission' => \App\Http\Middleware\EnsureDoctorApiPermission::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('notifications:appointment-reminders')->dailyAt('08:00');
        $schedule->command('notifications:admin-low-stock')->weekdays()->at('09:00');
        $schedule->command('notifications:admin-approval-digest')->dailyAt('07:30');
    })->create();
