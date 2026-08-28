<?php

namespace App\Http\Middleware;

use App\Support\ClinicalStaffPermissions;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureClinicalStaffApiPermission
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $doctor = $request->user('clinical_staff');

        if (! ClinicalStaffPermissions::can($doctor, $permission)) {
            return response()->json([
                'message' => __('You do not have access to this area.'),
            ], 403);
        }

        return $next($request);
    }
}
