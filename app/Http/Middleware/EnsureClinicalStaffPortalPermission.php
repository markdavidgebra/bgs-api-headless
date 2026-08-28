<?php

namespace App\Http\Middleware;

use App\Support\ClinicalStaffPermissions;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureClinicalStaffPortalPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $doctor = $request->user('clinical_staff');
        if (! $doctor) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        $required = ClinicalStaffPermissions::permissionForRouteName($routeName);

        if ($required === null) {
            return $next($request);
        }

        if (! ClinicalStaffPermissions::can($doctor, $required)) {
            abort(403, __('You do not have access to this area.'));
        }

        return $next($request);
    }
}
