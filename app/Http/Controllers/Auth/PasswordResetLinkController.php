<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Notifications\Patient\PatientPasswordResetLinkSentNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $email = $request->only('email');

        $status = Password::broker('users')->sendResetLink($email);

        if ($status === Password::RESET_LINK_SENT) {
            $patient = Patient::query()->where('email', strtolower(trim((string) $request->input('email'))))->first();
            if ($patient) {
                Notification::send($patient, new PatientPasswordResetLinkSentNotification);
            }
        }

        if ($status !== Password::RESET_LINK_SENT) {
            $status = Password::broker('clinical_staff')->sendResetLink($email);
        }

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', __($status))
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
    }
}
