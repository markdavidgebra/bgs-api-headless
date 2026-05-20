<?php

namespace App\Http\Middleware;

use App\Support\DoctorPermissions;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDoctorApiPermission
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $doctor = $request->user('doctor');

        if (! DoctorPermissions::can($doctor, $permission)) {
            return response()->json([
                'message' => __('You do not have access to this area.'),
            ], 403);
        }

        return $next($request);
    }
}
