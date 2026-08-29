<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ClinicalStaffAccountCreatedMail;
use App\Mail\NewClinicalStaffPendingAdminMail;
use App\Models\Appointment;
use App\Models\ClinicalStaff;
use App\Models\ClinicalStaffRole;
use App\Support\AdminNotificationRecipients;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClinicalStaffController extends Controller
{
    public function index(Request $request): View
    {
        $query = ClinicalStaff::query()->notManagerAlias()->with('role')->orderBy('name');

        if ($request->filled('search')) {
            $term = $request->string('search')->toString();
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%")
                    ->orWhere('specialty', 'like', "%{$term}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('specialty')) {
            $query->where('specialty', 'like', '%'.$request->string('specialty').'%');
        }

        $clinicalStaff = $query->paginate(15)->withQueryString();

        return view('admin.clinical-staff.index', compact('clinicalStaff'));
    }

    public function create(): View
    {
        $clinicalStaffRoles = ClinicalStaffRole::query()->orderBy('name')->get();

        return view('admin.clinical-staff.create', compact('clinicalStaffRoles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'clinical_staff_role_id' => $request->filled('clinical_staff_role_id') ? $request->integer('clinical_staff_role_id') : null,
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.ClinicalStaff::class],
            'phone' => ['nullable', 'string', 'max:32'],
            'clinical_staff_role_id' => ['nullable', 'integer', Rule::exists('clinical_staff_roles', 'id')],
        ]);

        $plainPassword = Str::password(12);

        $doctor = ClinicalStaff::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'clinical_staff_role_id' => $validated['clinical_staff_role_id'],
            'password' => Str::password(32),
            'pending_password_plain' => Crypt::encryptString($plainPassword),
            'status' => 'pending',
            'approved_at' => null,
        ]);

        $notifyEmails = array_values(array_unique(array_merge(
            AdminNotificationRecipients::emailsForPermission('clinical_staff.manage'),
            AdminNotificationRecipients::superAdminEmails(),
        )));
        $redirect = redirect()
            ->route('admin.clinical-staff.show', $doctor)
            ->with('status', __('Clinical staff created and saved as pending approval.'));

        if ($notifyEmails !== []) {
            try {
                Mail::to($notifyEmails)->send(new NewClinicalStaffPendingAdminMail($doctor));
            } catch (\Throwable $e) {
                report($e);
                try {
                    Mail::mailer('log')->to($notifyEmails)->send(new NewClinicalStaffPendingAdminMail($doctor));
                } catch (\Throwable $e2) {
                    report($e2);
                }

                return $redirect->with(
                    'warning',
                    __('Clinical staff was saved, but admin notification email failed (SMTP). A copy may be in :path if the log mailer worked. Fix MAIL_* or use a Gmail App Password.', ['path' => 'storage/logs/laravel.log'])
                );
            }
        }

        return $redirect;
    }

    public function edit(int $id): View
    {
        $doctor = ClinicalStaff::query()->with('role')->findOrFail($id);
        $clinicalStaffRoles = ClinicalStaffRole::query()->orderBy('name')->get();

        return view('admin.clinical-staff.edit', compact('doctor', 'clinicalStaffRoles'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $doctor = ClinicalStaff::query()->findOrFail($id);

        if ($request->exists('experience_years')) {
            $request->merge([
                'experience_years' => $request->filled('experience_years')
                    ? $request->integer('experience_years')
                    : null,
            ]);
        }

        if ($request->exists('clinical_staff_role_id')) {
            $request->merge([
                'clinical_staff_role_id' => $request->filled('clinical_staff_role_id')
                    ? $request->integer('clinical_staff_role_id')
                    : null,
            ]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('clinical_staff', 'email')->ignore($doctor->id)],
            'phone' => ['nullable', 'string', 'max:32'],
            'specialty' => ['nullable', 'string', 'max:255'],
            'license_no' => ['nullable', 'string', 'max:255'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:80'],
            'bio' => ['nullable', 'string', 'max:5000'],
            'clinical_staff_role_id' => ['nullable', 'integer', Rule::exists('clinical_staff_roles', 'id')],
            'photo' => ['nullable', 'image', 'max:2048'],
            'remove_photo' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $data = collect($validated)->except(['photo', 'remove_photo', 'password'])->all();

        if (! $request->exists('clinical_staff_role_id')) {
            unset($data['clinical_staff_role_id']);
        }

        foreach (['specialty', 'license_no', 'experience_years'] as $optionalField) {
            if (! $request->exists($optionalField)) {
                unset($data[$optionalField]);
            }
        }

        if (! empty($validated['password'])) {
            if (strtolower((string) ($doctor->status ?? 'pending')) === 'active') {
                $data['password'] = $validated['password'];
                $data['pending_password_plain'] = null;
            } else {
                $data['pending_password_plain'] = Crypt::encryptString($validated['password']);
            }
        }

        if ($request->boolean('remove_photo')) {
            $this->removeStoredDoctorImage($doctor->image_path);
            $data['image_path'] = null;
        }

        if ($request->hasFile('photo')) {
            $this->removeStoredDoctorImage($doctor->image_path);

            $dir = public_path('uploads/doctors');
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $file = $request->file('photo');
            $ext = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'jpg';
            $filename = $doctor->id.'_'.uniqid('', true).'.'.$ext;
            $file->move($dir, $filename);

            $data['image_path'] = 'uploads/doctors/'.$filename;
        }

        $doctor->update($data);

        return redirect()
            ->route('admin.clinical-staff.show', $doctor)
            ->with('status', __('Clinical staff updated.'));
    }

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $doctor = ClinicalStaff::query()->findOrFail($id);
        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(['pending', 'active', 'inactive'])],
        ]);
        $targetStatus = strtolower(trim($validated['status']));
        $previousStatus = strtolower((string) ($doctor->status ?? 'pending'));
        $payload = [
            'status' => $targetStatus,
        ];
        $approvalPassword = null;

        if ($targetStatus === 'active') {
            if ($previousStatus !== 'active') {
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

        $redirect = redirect()->route('admin.clinical-staff')->with('status', __('Clinical staff status updated.'));

        // Send welcome email when moving into "active", not only when wasChanged() reports it (avoids missed sends).
        if ($targetStatus === 'active' && $previousStatus !== 'active') {
            $email = trim((string) $doctor->email);
            if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $redirect->with('warning', __('Clinical staff approved, but no valid email is on file, so the welcome message was not sent.'));
            }

            try {
                Mail::to($email)->send(
                    new ClinicalStaffAccountCreatedMail($doctor->fresh(), (string) $approvalPassword)
                );
            } catch (\Throwable $e) {
                report($e);

                $loggedToFile = false;
                try {
                    Mail::mailer('log')->to($email)->send(
                        new ClinicalStaffAccountCreatedMail($doctor->fresh(), (string) $approvalPassword)
                    );
                    $loggedToFile = true;
                } catch (\Throwable $e2) {
                    report($e2);
                }

                $warning = $loggedToFile
                    ? __('Clinical staff approved. Inbox email failed (usually wrong Gmail password—use an App Password, not your normal login). The same message was written to :path. Share login details with the doctor manually below.', ['path' => 'storage/logs/laravel.log'])
                    : __('Clinical staff approved, but the welcome email could not be sent or logged. Fix MAIL_* in .env (Gmail: App Password + smtp.gmail.com:587 + MAIL_SCHEME=tls). Full error is in the application log.');

                return $redirect
                    ->with('warning', $warning)
                    ->with('clinical_staff_portal_credentials', [
                        'email' => $email,
                        'password' => (string) $approvalPassword,
                        'login_url' => url('/login?tab=staff'),
                    ]);
            }
        }

        return $redirect;
    }

    public function destroy(int $id): RedirectResponse
    {
        $doctor = ClinicalStaff::query()->findOrFail($id);

        if (Appointment::query()->where('clinical_staff_id', $doctor->id)->exists()) {
            return redirect()
                ->route('admin.clinical-staff')
                ->with('error', __('This clinical staff member cannot be deleted while they have appointments. Reassign or remove those appointments first.'));
        }

        DB::transaction(function () use ($doctor) {
            DB::table('treatment_clinical_staff_package')->where('clinical_staff_id', $doctor->id)->delete();
            $doctor->services()->detach();
            $doctor->delete();
        });

        return redirect()
            ->route('admin.clinical-staff')
            ->with('status', __('Clinical staff deleted.'));
    }

    public function updateRole(Request $request, int $id): RedirectResponse
    {
        $doctor = ClinicalStaff::query()->findOrFail($id);

        $request->merge([
            'clinical_staff_role_id' => $request->filled('clinical_staff_role_id') ? $request->integer('clinical_staff_role_id') : null,
        ]);

        $validated = $request->validate([
            'clinical_staff_role_id' => ['nullable', 'integer', Rule::exists('clinical_staff_roles', 'id')],
        ]);

        $doctor->update([
            'clinical_staff_role_id' => $validated['clinical_staff_role_id'],
        ]);

        return redirect()
            ->route('admin.clinical-staff.show', $doctor)
            ->with('status', __('Clinical portal role updated.'));
    }

    public function show(int $id): View
    {
        $doctor = ClinicalStaff::query()
            ->with(['weeklySchedules', 'services', 'role'])
            ->findOrFail($id);

        $doctor->assigned_services = $doctor->services->pluck('name')->all();

        $doctor->recent_appointments_sample = Appointment::query()
            ->where('clinical_staff_id', $doctor->id)
            ->with('patient:id,name')
            ->orderByDesc('appointment_date')
            ->orderByDesc('appointment_time')
            ->limit(25)
            ->get()
            ->map(static fn (Appointment $a): array => [
                'code' => $a->appointment_no ?? '—',
                'patient' => $a->patient?->name ?? '—',
                'date' => $a->date_display,
                'time' => $a->time_display,
                'status' => $a->status_label,
            ])
            ->all();

        $clinicalStaffRoles = ClinicalStaffRole::query()->orderBy('name')->get();

        $decryptedPendingPassword = null;
        if (! empty($doctor->pending_password_plain)) {
            try {
                $decryptedPendingPassword = Crypt::decryptString($doctor->pending_password_plain);
            } catch (\Throwable) {
                $decryptedPendingPassword = null;
            }
        }

        return view('admin.clinical-staff.show', compact('doctor', 'clinicalStaffRoles', 'decryptedPendingPassword'));
    }

    private function removeStoredDoctorImage(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        $normalized = ltrim($path, '/');

        if (str_starts_with($normalized, 'uploads/doctors/')) {
            $fullPath = public_path($normalized);
            if (is_file($fullPath)) {
                unlink($fullPath);
            }

            return;
        }

        if (str_starts_with($normalized, 'doctor/profile/')) {
            $fullPath = public_path($normalized);
            if (is_file($fullPath)) {
                unlink($fullPath);
            }

            return;
        }

        Storage::disk('public')->delete($normalized);
    }
}
