<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Api\Concerns\AdminPortalResponses;
use App\Http\Controllers\Api\Concerns\ConvertsAdminWebResponses;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminProfileController extends Controller
{
    use AdminPortalResponses;
    use ConvertsAdminWebResponses;

    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'admin' => $this->adminPayload($request->user('admin')),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        return $this->adminWebJson(app(ProfileController::class)->update($request));
    }

    /**
     * Change the signed-in admin's password.
     *
     * Mirrors the web `PasswordController::update` action but:
     *   - validates against the `admin` guard (so the rule sees the right user),
     *   - returns JSON rather than a redirect.
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password:admin'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $admin = $request->user('admin');

        if (! $admin) {
            return response()->json(['message' => __('Not authenticated.')], 401);
        }

        $admin->forceFill([
            'password' => Hash::make($validated['password']),
        ])->save();

        return response()->json(['message' => __('Password updated.')]);
    }
}
