<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\DoctorAccountCreatedMail;
use App\Mail\NewDoctorPendingAdminMail;
use App\Models\Doctor;
use App\Support\AdminNotificationRecipients;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DoctorsController extends Controller
{
    public function index(Request $request): View
    {
        $query = Doctor::query()->orderBy('name');

        if ($request->filled('search')) {
            $term = $request->string('search')->toString();
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%")
                    ->orWhere('specialty', 'like', "%{$term}%")
                    ->orWhere('bio', 'like', "%{$term}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('specialty')) {
            $query->where('specialty', 'like', '%'.$request->string('specialty').'%');
        }

        $doctors = $query->paginate(15)->withQueryString();

        return view('admin.doctors.index', compact('doctors'));
    }

    public function create(): View
    {
        return view('admin.doctors.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.Doctor::class],
            'phone' => ['nullable', 'string', 'max:32'],
        ]);

        $plainPassword = Str::password(12);

        $doctor = Doctor::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Str::password(32),
            'pending_password_plain' => Crypt::encryptString($plainPassword),
            'status' => 'pending',
            'approved_at' => null,
        ]);

        $notifyEmails = array_values(array_unique(array_merge(
            AdminNotificationRecipients::emailsForPermission('doctors.manage'),
            AdminNotificationRecipients::superAdminEmails(),
        )));
        if ($notifyEmails !== []) {
            Mail::to($notifyEmails)->send(new NewDoctorPendingAdminMail($doctor));
        }

        return redirect()
            ->route('admin.doctors.show', $doctor)
            ->with('status', __('Doctor created and saved as pending approval.'));
    }

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $doctor = Doctor::query()->findOrFail($id);
        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(['pending', 'active', 'inactive'])],
        ]);
        $targetStatus = strtolower(trim($validated['status']));
        $payload = [
            'status' => $targetStatus,
        ];
        $approvalPassword = null;

        if ($targetStatus === 'active') {
            if (($doctor->status ?? 'pending') !== 'active') {
                $payload['approved_at'] = now();
            }

            if (! empty($doctor->pending_password_plain)) {
                try {
                    $approvalPassword = Crypt::decryptString($doctor->pending_password_plain);
                    $payload['password'] = $approvalPassword;
                    $payload['pending_password_plain'] = null;
                } catch (\Throwable) {
                    // If decrypt fails, keep current password and continue.
                }
            }

            if ($approvalPassword === null || $approvalPassword === '') {
                $approvalPassword = Str::password(12);
                $payload['password'] = $approvalPassword;
                $payload['pending_password_plain'] = null;
            }
        } else {
            $payload['approved_at'] = null;
        }

        $doctor->update($payload);

        if ($targetStatus === 'active' && $doctor->wasChanged('status')) {
            Mail::to($doctor->email)->send(new DoctorAccountCreatedMail($doctor->fresh(), (string) $approvalPassword));
        }

        return redirect()
            ->route('admin.doctors')
            ->with('status', __('Doctor status updated.'));
    }

    public function show(int $id): View
    {
        $doctor = Doctor::query()
            ->with('weeklySchedules')
            ->findOrFail($id);

        // Example: Use real relationships when implemented.
        // For now, mock relationship data for the view
        $doctor->assigned_services = [
            'Facial Treatment',
            'Chemical peel',
            'Laser',
        ];

        $doctor->recent_appointments_sample = [
            ['code' => 'APT-0012', 'patient' => 'Maria Santos', 'date' => '2026-03-20', 'time' => '14:00', 'status' => 'Pending'],
            ['code' => 'APT-0010', 'patient' => 'Ana Reyes', 'date' => '2026-03-18', 'time' => '10:30', 'status' => 'Completed'],
        ];

        return view('admin.doctors.show', compact('doctor'));
    }
}
