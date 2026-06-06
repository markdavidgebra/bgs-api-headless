<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\PatientRegistrationApprovedMail;
use App\Models\Patient;
use App\Support\PatientLogin;
use App\Support\SafeMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientRegistrationsController extends Controller
{
    public function index(Request $request): View
    {
        $query = Patient::query()
            ->where('status', 'pending')
            ->orderByDesc('created_at');

        if ($request->filled('search')) {
            $term = $request->string('search')->toString();
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            });
        }

        $registrations = $query->paginate(20)->withQueryString();

        return view('admin.registrations.index', compact('registrations'));
    }

    public function approve(int $id): RedirectResponse
    {
        $patient = Patient::query()->where('status', 'pending')->findOrFail($id);
        $plainPassword = PatientLogin::plainPasswordFromPending($patient);

        $patient->update([
            'status' => 'active',
            'pending_password_plain' => null,
            ...(is_string($plainPassword) && $plainPassword !== ''
                ? ['password' => $plainPassword]
                : []),
        ]);

        $sent = SafeMail::send(
            (string) $patient->email,
            new PatientRegistrationApprovedMail(
                name: (string) $patient->name,
                emailAddress: (string) $patient->email,
                plainPassword: (string) ($plainPassword ?? '')
            )
        );

        if (! $sent) {
            return redirect()
                ->route('admin.registrations')
                ->with('status', __('Registration approved.'))
                ->with('warning', __('Registration was approved, but the approval email could not be sent. Check MAIL_* settings in .env.'));
        }

        return redirect()
            ->route('admin.registrations')
            ->with('status', __('Registration approved. Approval email sent.'));
    }

    public function disapprove(int $id): RedirectResponse
    {
        $patient = Patient::query()->where('status', 'pending')->findOrFail($id);
        $patient->update(['status' => 'inactive']);

        return redirect()
            ->route('admin.registrations')
            ->with('status', __('Registration disapproved.'));
    }
}
