<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * The callback that should be used to generate the authentication redirect path.
     *
     * @var callable|null
     */
    protected static $redirectToCallback;

    /**
     * Specify the guards for the middleware.
     *
     * @param  string  $guard
     * @param  string  $others
     * @return string
     */
    public static function using($guard, ...$others)
    {
        return static::class.':'.implode(',', [$guard, ...$others]);
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return redirect($this->redirectTo($request, $guard));
            }
        }

        return $next($request);
    }

    /**
     * Get the path the user should be redirected to when they are authenticated.
     */
    protected function redirectTo(Request $request, ?string $guard): ?string
    {
        return static::$redirectToCallback
            ? call_user_func(static::$redirectToCallback, $request)
            : $this->defaultRedirectUri($guard);
    }

    /**
     * Get the default URI the user should be redirected to when they are authenticated.
     */
    protected function defaultRedirectUri(?string $guard): string
    {
        if ($guard === 'admin' && Route::has('admin.dashboard')) {
            return route('admin.dashboard');
        }

        if ($guard === 'web' && Route::has('patient.dashboard')) {
            return route('patient.dashboard');
        }

        if ($guard === 'clinical_staff' && Route::has('clinical_staff.dashboard')) {
            return route('clinical_staff.dashboard');
        }

        if ($guard === 'doctor' && Route::has('doctor.dashboard')) {
            return route('doctor.dashboard');
        }

        if ($guard === 'doctor' && Route::has('doctor.dashboard')) {
            return route('doctor.dashboard');
        }

        // foreach (['admin.dashboard', 'dashboard'] as $uri) {
        //     if (Route::has($uri)) {
        //         return route($uri);
        //     }
        // }

        // $routes = Route::getRoutes()->get('GET');

        // foreach (['admin.dashboard', 'dashboard'] as $uri) {
        //     if (isset($routes[$uri])) {
        //         return '/'.$uri;
        //     }
        // }

        return '/';
    }

    /**
     * Specify the callback that should be used to generate the redirect path.
     *
     * @return void
     */
    public static function redirectUsing(callable $redirectToCallback)
    {
        static::$redirectToCallback = $redirectToCallback;
    }
}
