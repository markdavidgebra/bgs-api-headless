<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminLoginRequest extends FormRequest
{
    /**
     * Redirect failed rule validation back to the unified login page when the staff form was submitted from there.
     */
    protected function getRedirectUrl(): string
    {
        if ($this->cameFromPublicLoginPage()) {
            return route('login', ['tab' => 'staff']);
        }

        return parent::getRedirectUrl();
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $credentials = $this->only('email', 'password');
        $remember = $this->boolean('remember');

        $loggedIn = Auth::guard('admin')->attempt($credentials, $remember)
            || Auth::guard('clinical_staff')->attempt($credentials, $remember);

        if (! $loggedIn) {
            RateLimiter::hit($this->throttleKey());

            if (Auth::guard('web')->validate($credentials)) {
                $this->throwAuthValidation([
                    'email' => __('Use the Patient tab to sign in with this email.'),
                ]);
            }

            if (Auth::guard('doctor')->validate($credentials)) {
                $this->throwAuthValidation([
                    'email' => __('Use the doctor portal to sign in with this email.'),
                ]);
            }

            $this->throwAuthValidation([
                'email' => trans('auth.failed'),
            ]);
        }

        if (Auth::guard('admin')->check()) {
            $admin = Auth::guard('admin')->user();
            $status = strtolower((string) ($admin->status ?? 'approved'));

            if ($status !== 'approved') {
                Auth::guard('admin')->logout();
                RateLimiter::hit($this->throttleKey());

                $message = $status === 'disapproved'
                    ? __('Your admin access has been removed.')
                    : __('Your staff account is still in draft. Please ask a super admin to approve it first.');

                $this->throwAuthValidation([
                    'email' => $message,
                ]);
            }
        }

        if (Auth::guard('clinical_staff')->check()) {
            $doctor = Auth::guard('clinical_staff')->user();
            $status = strtolower((string) ($doctor->status ?? 'pending'));
            if ($status !== 'active') {
                Auth::guard('clinical_staff')->logout();
                RateLimiter::hit($this->throttleKey());

                $message = $status === 'inactive'
                    ? __('Your doctor portal access has been removed.')
                    : __('Your doctor account is awaiting admin approval.');

                $this->throwAuthValidation([
                    'email' => $message,
                ]);
            }
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        $this->throwAuthValidation([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }

    /**
     * @param  array<string, array<int, string>|string>  $messages
     */
    protected function throwAuthValidation(array $messages): never
    {
        $exception = ValidationException::withMessages($messages);

        if ($this->cameFromPublicLoginPage()) {
            $exception->redirectTo(route('login', ['tab' => 'staff']));
        }

        throw $exception;
    }

    protected function cameFromPublicLoginPage(): bool
    {
        $referer = $this->headers->get('referer');
        if (! $referer) {
            return false;
        }

        $path = parse_url($referer, PHP_URL_PATH) ?? '';
        if (str_contains($path, '/admin/login')) {
            return false;
        }

        $trimmed = rtrim($path, '/');

        return $trimmed === 'login' || str_ends_with($trimmed, '/login');
    }
}
