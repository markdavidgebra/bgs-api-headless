<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\PatientRegistrationApprovedMail;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
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
        $plainPassword = null;
        if (! empty($patient->pending_password_plain)) {
            try {
                $plainPassword = Crypt::decryptString($patient->pending_password_plain);
            } catch (\Throwable) {
                $plainPassword = null;
            }
        }

        $patient->update([
            'status' => 'active',
            'pending_password_plain' => null,
        ]);

        if (is_string($plainPassword) && $plainPassword !== '') {
            Mail::to($patient->email)->send(
                new PatientRegistrationApprovedMail(
                    name: (string) $patient->name,
                    emailAddress: (string) $patient->email,
                    plainPassword: $plainPassword
                )
            );
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
