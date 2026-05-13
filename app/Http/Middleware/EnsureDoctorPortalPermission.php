<?php

namespace App\Http\Middleware;

use App\Support\DoctorPermissions;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDoctorPortalPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $doctor = $request->user('doctor');
        if (! $doctor) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        $required = DoctorPermissions::permissionForRouteName($routeName);

        if ($required === null) {
            return $next($request);
        }

        if (! DoctorPermissions::can($doctor, $required)) {
            abort(403, __('You do not have access to this area.'));
        }

        return $next($request);
    }
}
