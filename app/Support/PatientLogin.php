<?php

namespace App\Support;

use App\Models\Patient;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

/**
 * Patient portal login helpers.
 *
 * Ensures passwords set at registration (plain + model "hashed" cast) work at login,
 * and repairs legacy rows where approval overwrote the hash with a random password.
 */
class PatientLogin
{
    /**
     * @param  array{email: string, password: string}  $credentials
     */
    public static function attempt(Guard $guard, array $credentials, bool $remember = false): bool
    {
        if ($guard->attempt($credentials, $remember)) {
            return true;
        }

        $email = $credentials['email'] ?? null;
        $password = $credentials['password'] ?? null;

        if (! is_string($email) || ! is_string($password) || $password === '') {
            return false;
        }

        $patient = Patient::query()->where('email', $email)->first();
        if (! $patient instanceof Patient) {
            return false;
        }

        if (self::repairStoredPassword($patient, $password)) {
            return $guard->attempt($credentials, $remember);
        }

        return false;
    }

    /**
     * Re-store the password from pending_password_plain when the hash does not match.
     */
    public static function repairStoredPassword(Patient $patient, string $attemptedPlain): bool
    {
        $stored = (string) $patient->getAuthPassword();

        if ($stored !== '' && Hash::check($attemptedPlain, $stored)) {
            return false;
        }

        if (empty($patient->pending_password_plain)) {
            return false;
        }

        try {
            $plain = Crypt::decryptString((string) $patient->pending_password_plain);
        } catch (\Throwable) {
            return false;
        }

        if ($plain === '' || ! hash_equals($plain, $attemptedPlain)) {
            return false;
        }

        $patient->forceFill(['password' => $plain])->save();

        return true;
    }

    /**
     * Apply a plain-text password once (uses the model hashed cast).
     */
    public static function applyPlainPassword(Patient $patient, string $plainPassword): void
    {
        $patient->forceFill(['password' => $plainPassword])->save();
    }

    /**
     * Read the registration password saved for admin approval emails.
     */
    public static function plainPasswordFromPending(Patient $patient): ?string
    {
        if (empty($patient->pending_password_plain)) {
            return null;
        }

        try {
            $plain = Crypt::decryptString((string) $patient->pending_password_plain);
        } catch (\Throwable) {
            return null;
        }

        return $plain !== '' ? $plain : null;
    }
}
