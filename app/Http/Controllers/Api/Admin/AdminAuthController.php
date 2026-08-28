<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Concerns\AdminPortalResponses;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AdminLoginRequest;
use App\Models\Admin;
use App\Support\AdminPermissions;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class AdminAuthController extends Controller
{
    use AdminPortalResponses;

    public function login(AdminLoginRequest $request): JsonResponse
    {
        return $this->completePortalLogin($request, 'admin');
    }

    public function managerLogin(AdminLoginRequest $request): JsonResponse
    {
        return $this->completePortalLogin($request, 'manager');
    }

    /**
     * @param  'admin'|'manager'  $portal
     */
    private function completePortalLogin(AdminLoginRequest $request, string $portal): JsonResponse
    {
        // Clear other portal sessions so admin API routes are not blocked by
        // prevent_cross_guard while a patient/doctor cookie is still active.
        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
        }

        if (Auth::guard('doctor')->check()) {
            Auth::guard('doctor')->logout();
        }

        $request->authenticate();

        if (! Auth::guard('admin')->check()) {
            throw ValidationException::withMessages([
                'email' => [__('Use admin staff credentials for this portal.')],
            ]);
        }

        $admin = Auth::guard('admin')->user();
        $isManager = AdminPermissions::isManagerRole((string) ($admin->role ?? ''));

        if ($portal === 'manager' && ! $isManager) {
            $this->rejectWrongPortal($request, __('Use the admin portal to sign in with this account.'));
        }

        if ($portal === 'admin' && $isManager) {
            $this->rejectWrongPortal($request, __('Use the manager portal to sign in with this account.'));
        }

        $request->session()->regenerate();

        return response()->json([
            'message' => $portal === 'manager'
                ? __('Manager login successful.')
                : __('Admin login successful.'),
            'csrf_token' => csrf_token(),
            'admin' => $this->adminPayload($admin),
        ]);
    }

    private function rejectWrongPortal(AdminLoginRequest $request, string $message): never
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        throw ValidationException::withMessages([
            'email' => [$message],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => __('Logged out.'),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'csrf_token' => csrf_token(),
            'admin' => $this->adminPayload($request->user('admin')),
        ]);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::broker('admins')->sendResetLink(
            $request->only('email'),
            function ($user, $token) {
                $notification = new ResetPassword($token);
                $notification->createUrlUsing(function () use ($token, $user) {
                    return route('admin.password.reset', ['token' => $token, 'email' => $user->email]);
                });
                $user->notify($notification);
            }
        );

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json([
            'message' => __($status),
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::broker('admins')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (Admin $user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json([
            'message' => __($status),
        ]);
    }
}
