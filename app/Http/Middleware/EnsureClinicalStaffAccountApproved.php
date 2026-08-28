<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureClinicalStaffAccountApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $doctor = Auth::guard('doctor')->user();

        if ($doctor !== null && strtolower((string) ($doctor->status ?? 'pending')) !== 'active') {
            Auth::guard('doctor')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson() || $request->is('api/doctor/*')) {
                return response()->json([
                    'message' => __('Your doctor account is not approved yet.'),
                ], 403);
            }

            return redirect()->route('login')->withErrors([
                'email' => __('Your doctor account is not approved yet.'),
            ]);
        }

        return $next($request);
    }
}
