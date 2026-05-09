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
            if (Auth::guard('web')->check()) {
                return redirect()->route('patient.dashboard');
            }
            if (Auth::guard('doctor')->check()) {
                return redirect()->route('doctor.dashboard');
            }
        }

        if ($allowedGuard === 'web') {
            if (Auth::guard('admin')->check()) {
                return redirect()->route('admin.dashboard');
            }
            if (Auth::guard('doctor')->check()) {
                return redirect()->route('doctor.dashboard');
            }
        }

        if ($allowedGuard === 'doctor') {
            if (Auth::guard('admin')->check()) {
                return redirect()->route('admin.dashboard');
            }
            if (Auth::guard('web')->check()) {
                return redirect()->route('patient.dashboard');
            }
        }

        return $next($request);
    }
}
