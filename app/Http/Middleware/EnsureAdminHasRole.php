<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminHasRole
{
    /**
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $admin = Auth::guard('admin')->user();

        if (! $admin) {
            abort(401, 'Unauthenticated.');
        }

        $allowed = array_map(
            static fn (string $role): string => strtolower(trim($role)),
            $roles
        );

        $adminRole = strtolower((string) ($admin->role ?? ''));
        if ($allowed !== [] && ! in_array($adminRole, $allowed, true)) {
            abort(403, 'Forbidden.');
        }

        return $next($request);
    }
}
