<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PreventCrossGuardAccess
{
    /**
     * Every portal guard, with the label used in the "sign out first" message and
     * the route to bounce a conflicting session back to.
     *
     * @var array<string, array{label: string, dashboard: string}>
     */
    protected const GUARDS = [
        'admin' => ['label' => 'admin dashboard', 'dashboard' => 'admin.dashboard'],
        'web' => ['label' => 'patient portal', 'dashboard' => 'patient.dashboard'],
        'clinical_staff' => ['label' => 'clinical staff portal', 'dashboard' => 'clinical_staff.dashboard'],
        'doctor' => ['label' => 'doctor portal', 'dashboard' => 'doctor.dashboard'],
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $allowedGuard): Response
    {
        if (! isset(self::GUARDS[$allowedGuard])) {
            return $next($request);
        }

        // An active session on the requested guard always wins, even if another
        // portal's cookie is still lying around.
        if (Auth::guard($allowedGuard)->check()) {
            return $next($request);
        }

        $target = self::GUARDS[$allowedGuard]['label'];

        foreach (self::GUARDS as $guard => $meta) {
            if ($guard === $allowedGuard) {
                continue;
            }

            if (! Auth::guard($guard)->check()) {
                continue;
            }

            return $this->blockCrossGuard(
                $request,
                __('Sign out of the :current before using the :target.', [
                    'current' => $meta['label'],
                    'target' => $target,
                ]),
                $meta['dashboard'],
            );
        }

        return $next($request);
    }

    protected function blockCrossGuard(Request $request, string $message, string $redirectRoute): Response
    {
        if ($request->expectsJson() || str_starts_with($request->path(), 'api/')) {
            return response()->json(['message' => $message], 403);
        }

        return redirect()->route($redirectRoute);
    }
}
