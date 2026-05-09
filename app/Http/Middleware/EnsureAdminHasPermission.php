<?php

namespace App\Http\Middleware;

use App\Support\AdminPermissions;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminHasPermission
{
    /**
     * @param  string  ...$permissions
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        $admin = Auth::guard('admin')->user();
        if (! $admin) {
            abort(401, 'Unauthenticated.');
        }

        if ($permissions === []) {
            return $next($request);
        }

        foreach ($permissions as $permission) {
            if (AdminPermissions::canAccess($admin, $permission)) {
                return $next($request);
            }
        }

        abort(403, 'Forbidden.');
    }
}
