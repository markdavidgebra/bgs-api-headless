<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\NewPatientRegistrationPendingMail;
use App\Models\Patient;
use App\Support\AdminNotificationRecipients;
use App\Support\PageHeaderConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register', [
            'signUpPageHeaderBgUrl' => PageHeaderConfig::signUpPageBackgroundUrl(),
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
                Rule::unique('clinical_staff', 'email'),
            ],
            'birthdate' => ['required', 'date', 'before_or_equal:today'],
            'gender' => ['required', 'string', Rule::in(['male', 'female', 'other'])],
            'address' => ['required', 'string', 'max:500'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = Patient::create([
            'name' => $request->name,
            'email' => $request->email,
            'birthdate' => $request->birthdate,
            'gender' => $request->gender,
            'address' => $request->address,
            'password' => $request->password,
            'pending_password_plain' => Crypt::encryptString($request->password),
            'status' => 'pending',
        ]);

        $adminEmails = array_values(array_unique(array_merge(
            AdminNotificationRecipients::emailsForPermission('registrations.manage'),
            AdminNotificationRecipients::superAdminEmails(),
        )));
        if ($adminEmails !== []) {
            Mail::to($adminEmails)->send(new NewPatientRegistrationPendingMail($user));
        }

        return redirect()
            ->route('register')
            ->with('status', __('Registration submitted. Please wait for admin approval before you can login.'));
    }
}
