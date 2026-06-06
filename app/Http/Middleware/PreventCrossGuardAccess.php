<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PreventCrossGuardAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $allowedGuard): Response
    {
        if ($allowedGuard === 'admin') {
            // Admin session takes precedence — do not redirect to patient/doctor portals.
            if (Auth::guard('admin')->check()) {
                return $next($request);
            }

            if (Auth::guard('web')->check()) {
                return $this->blockCrossGuard($request, __('Sign out of the patient portal before using the admin dashboard.'), 'patient.dashboard');
            }

            if (Auth::guard('doctor')->check()) {
                return $this->blockCrossGuard($request, __('Sign out of the doctor portal before using the admin dashboard.'), 'doctor.dashboard');
            }
        }

        if ($allowedGuard === 'web') {
            if (Auth::guard('web')->check()) {
                return $next($request);
            }

            if (Auth::guard('admin')->check()) {
                return $this->blockCrossGuard($request, __('Sign out of the admin portal before using the patient portal.'), 'admin.dashboard');
            }

            if (Auth::guard('doctor')->check()) {
                return $this->blockCrossGuard($request, __('Sign out of the doctor portal before using the patient portal.'), 'doctor.dashboard');
            }
        }

        if ($allowedGuard === 'doctor') {
            if (Auth::guard('doctor')->check()) {
                return $next($request);
            }

            if (Auth::guard('admin')->check()) {
                return $this->blockCrossGuard($request, __('Sign out of the admin portal before using the doctor portal.'), 'admin.dashboard');
            }

            if (Auth::guard('web')->check()) {
                return $this->blockCrossGuard($request, __('Sign out of the patient portal before using the doctor portal.'), 'patient.dashboard');
            }
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
