<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\PatientRegistrationApprovedMail;
use App\Models\Appointment;
use App\Models\AppointmentNote;
use App\Models\AppointmentPayment;
use App\Models\Patient;
use App\Models\PatientSubscription;
use App\Models\Payment;
use App\Models\TreatmentPackage;
use App\Models\TreatmentPackageUsageHistory;
use App\Models\TreatmentPatientPackage;
use App\Notifications\Patient\PatientPasswordResetLinkSentNotification;
use App\Support\AdminPermissions;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class PatientsController extends Controller
{
    public function index(Request $request): View
    {
        $this->autoDeactivateInactivePatients();

        $query = Patient::query()
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if ($request->filled('search')) {
            $term = $request->string('search')->toString();
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('subscription')) {
            $query->where('subscription', 'like', '%'.$request->string('subscription').'%');
        }

        $patients = $query->paginate(15)->withQueryString();
        $canManageStatus = $this->canManageStatus();
        $canManagePatientRecords = AdminPermissions::canAccess(auth('admin')->user(), 'patients.manage');

        return view('admin.patients.index', compact('patients', 'canManageStatus', 'canManagePatientRecords'));
    }

    public function create(): View
    {
        $canManageStatus = $this->canManageStatus();

        return view('admin.patients.create', compact('canManageStatus'));
    }

    public function store(Request $request): RedirectResponse
    {
        $canManageStatus = $this->canManageStatus();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
                Rule::unique('doctors', 'email'),
            ],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'phone' => ['nullable', 'string', 'max:30'],
            'birthdate' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', Rule::in(['male', 'female', 'other'])],
            'address' => ['nullable', 'string', 'max:500'],
            'emergency_contact' => ['nullable', 'string', 'max:255'],
            'history_summary' => ['nullable', 'string'],
        ];

        if ($canManageStatus) {
            $rules['status'] = ['required', 'string', Rule::in(['pending', 'active', 'inactive'])];
        }

        $validated = $request->validate($rules);

        $status = $canManageStatus
            ? strtolower((string) $validated['status'])
            : 'active';

        $plainPassword = (string) $validated['password'];

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'birthdate' => $validated['birthdate'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'address' => $validated['address'] ?? null,
            'emergency_contact' => $validated['emergency_contact'] ?? null,
            'history_summary' => $validated['history_summary'] ?? null,
            'status' => $status,
            'password' => $plainPassword,
            'pending_password_plain' => $status === 'pending'
                ? Crypt::encryptString($plainPassword)
                : null,
            'email_verified_at' => $status === 'active' ? now() : null,
        ];

        $patient = Patient::query()->create($payload);

        if ($status === 'active') {
            Mail::to($patient->email)->send(
                new PatientRegistrationApprovedMail(
                    name: (string) $patient->name,
                    emailAddress: (string) $patient->email,
                    plainPassword: $plainPassword
                )
            );
        }

        return redirect()
            ->route('admin.patients.show', $patient->id)
            ->with('status', __('Patient created.'));
    }

    public function show(int $id): View
    {
        $this->autoDeactivateInactivePatients();

        $patient = Patient::query()->findOrFail($id);

        $legacySubscription = null;
        if ($patient->subscription) {
            $decoded = json_decode($patient->subscription, true);
            $legacySubscription = is_array($decoded)
                ? $decoded
                : ['plan' => $patient->subscription];
        }

        $legacyAppointmentHistory = [];
        if ($patient->appointment_history) {
            $decoded = json_decode($patient->appointment_history, true);
            $legacyAppointmentHistory = is_array($decoded) ? $decoded : [];
        }

        $legacyNotes = [];
        if ($patient->notes) {
            $decoded = json_decode($patient->notes, true);
            $legacyNotes = is_array($decoded) ? $decoded : ['admin_notes' => $patient->notes];
        }

        $appointments = Appointment::query()
            ->with(['service:id,name', 'doctor:id,name', 'patient:id,name', 'note'])
            ->where('patient_id', $patient->id)
            ->orderByDesc('appointment_date')
            ->orderByDesc('id')
            ->get();

        $appointmentPayments = AppointmentPayment::query()
            ->whereHas('appointment', fn ($q) => $q->where('patient_id', $patient->id))
            ->with('appointment:id,appointment_no,appointment_date')
            ->orderByDesc('id')
            ->get();

        $payments = Payment::query()
            ->with('referenceProduct:id,name,sku')
            ->where('patient_id', $patient->id)
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->get();

        $productOrders = $payments
            ->where('reference_type', 'product')
            ->values();

        $subscriptions = PatientSubscription::query()
            ->with('membershipPlan:id,name,price,billing_cycle')
            ->where('patient_id', $patient->id)
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->get();

        $patientPackages = TreatmentPatientPackage::query()
            ->with('treatmentPackage:id,name')
            ->where('patient_id', $patient->id)
            ->orderByDesc('purchased_at')
            ->orderByDesc('id')
            ->get();

        $packageUsageHistory = TreatmentPackageUsageHistory::query()
            ->with(['service:id,name', 'patientPackage:id,treatment_package_id', 'patientPackage.treatmentPackage:id,name'])
            ->where('patient_id', $patient->id)
            ->orderByDesc('used_on')
            ->orderByDesc('id')
            ->get();

        $canManageStatus = $this->canManageStatus();
        $canManagePatientRecords = AdminPermissions::canAccess(auth('admin')->user(), 'patients.manage');

        return view('admin.patients.show', compact(
            'patient',
            'legacySubscription',
            'legacyAppointmentHistory',
            'legacyNotes',
            'appointments',
            'appointmentPayments',
            'payments',
            'productOrders',
            'subscriptions',
            'patientPackages',
            'packageUsageHistory',
            'canManageStatus',
            'canManagePatientRecords',
        ));
    }

    public function edit(int $id): View
    {
        $this->autoDeactivateInactivePatients();

        $patient = Patient::query()->findOrFail($id);
        $canManageStatus = $this->canManageStatus();

        $treatmentPackagesForAssign = TreatmentPackage::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'validity_type', 'validity_value']);

        $patientPackages = TreatmentPatientPackage::query()
            ->with('treatmentPackage:id,name')
            ->where('patient_id', $patient->id)
            ->orderByDesc('purchased_at')
            ->orderByDesc('id')
            ->get();

        return view('admin.patients.edit', compact(
            'patient',
            'canManageStatus',
            'treatmentPackagesForAssign',
            'patientPackages',
        ));
    }

    public function storePatientTreatmentPackage(Request $request, int $id): RedirectResponse
    {
        $patient = Patient::query()->findOrFail($id);

        $validated = $request->validate([
            'treatment_package_id' => ['required', 'integer', 'exists:treatment_packages,id'],
            'purchased_at' => ['nullable', 'date'],
            'start_date' => ['nullable', 'date'],
            'package_admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $package = TreatmentPackage::query()
            ->where('status', 'active')
            ->whereKey($validated['treatment_package_id'])
            ->firstOrFail();

        $purchasedAt = isset($validated['purchased_at'])
            ? Carbon::parse($validated['purchased_at'])->startOfDay()
            : now()->startOfDay();

        $startDate = isset($validated['start_date'])
            ? Carbon::parse($validated['start_date'])->startOfDay()
            : $purchasedAt;

        $endDate = null;
        if (! empty($package->validity_value) && ! empty($package->validity_type)) {
            $endDateCarbon = $package->validity_type === 'year'
                ? $purchasedAt->copy()->addYears((int) $package->validity_value)
                : $purchasedAt->copy()->addMonths((int) $package->validity_value);
            $endDate = $endDateCarbon->toDateString();
        }

        $totalSessions = (int) DB::table('treatment_service_package')
            ->where('treatment_package_id', $package->id)
            ->sum('sessions');

        $noteParts = ['Assigned from admin patient edit'];
        if (! empty($validated['package_admin_notes'])) {
            $noteParts[] = trim((string) $validated['package_admin_notes']);
        }

        TreatmentPatientPackage::query()->create([
            'patient_id' => $patient->id,
            'treatment_package_id' => $package->id,
            'purchased_at' => $purchasedAt->toDateString(),
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate,
            'status' => 'active',
            'total_sessions' => max(0, $totalSessions),
            'used_sessions' => 0,
            'remaining_sessions' => max(0, $totalSessions),
            'notes' => implode(' | ', $noteParts),
        ]);

        return redirect()
            ->route('admin.patients.edit', $patient->id)
            ->with('status', __('Treatment package added for this patient.'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $patient = Patient::query()->findOrFail($id);
        $canManageStatus = $this->canManageStatus();

        $statusRules = ['nullable', 'string', Rule::in(['active', 'inactive'])];
        if (! $canManageStatus) {
            $statusRules[] = Rule::in([$patient->status ?? 'active']);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($patient->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'birthdate' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', Rule::in(['male', 'female', 'other'])],
            'address' => ['nullable', 'string', 'max:255'],
            'emergency_contact' => ['nullable', 'string', 'max:255'],
            'history_summary' => ['nullable', 'string'],
            'status' => $statusRules,
        ]);

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'birthdate' => $validated['birthdate'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'address' => $validated['address'] ?? null,
            'emergency_contact' => $validated['emergency_contact'] ?? null,
            'history_summary' => $validated['history_summary'] ?? null,
        ];

        if ($canManageStatus && isset($validated['status'])) {
            $payload['status'] = strtolower((string) $validated['status']);
        }

        $patient->update($payload);

        return redirect()
            ->route('admin.patients.show', $patient->id)
            ->with('status', __('Patient profile updated.'));
    }

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        if (! $this->canManageStatus()) {
            return redirect()
                ->route('admin.patients')
                ->withErrors(['status' => __('Your role is not allowed to update patient status.')]);
        }

        $patient = Patient::query()->findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(['pending', 'active', 'inactive'])],
        ]);

        $targetStatus = strtolower(trim($validated['status']));
        $payload = [
            'status' => $targetStatus,
        ];
        $approvalPassword = null;

        if ($targetStatus === 'active') {
            if (! empty($patient->pending_password_plain)) {
                try {
                    $approvalPassword = Crypt::decryptString($patient->pending_password_plain);
                    $payload['password'] = $approvalPassword;
                    $payload['pending_password_plain'] = null;
                } catch (\Throwable) {
                    // If decrypt fails, fallback to a generated temporary password.
                }
            }

            if ($approvalPassword === null || $approvalPassword === '') {
                $approvalPassword = Str::password(12);
                $payload['password'] = $approvalPassword;
                $payload['pending_password_plain'] = null;
            }
        }

        $patient->update($payload);

        if ($targetStatus === 'active' && $patient->wasChanged('status')) {
            Mail::to($patient->email)->send(
                new PatientRegistrationApprovedMail(
                    name: (string) $patient->name,
                    emailAddress: (string) $patient->email,
                    plainPassword: (string) $approvalPassword
                )
            );
        }

        return redirect()
            ->route('admin.patients')
            ->with('status', __('Patient status updated.'));
    }

    public function updatePassword(Request $request, int $id): RedirectResponse
    {
        $patient = Patient::query()->findOrFail($id);

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $patient->update([
            'password' => $validated['password'],
            'pending_password_plain' => null,
        ]);

        return redirect()
            ->route('admin.patients.show', $patient->id)
            ->with('status', __('Patient password updated successfully.'));
    }

    public function sendPasswordReset(int $id): RedirectResponse
    {
        $patient = Patient::query()->findOrFail($id);

        $status = Password::broker('users')->sendResetLink([
            'email' => (string) $patient->email,
        ]);

        if ($status !== Password::RESET_LINK_SENT) {
            return redirect()
                ->route('admin.patients.show', $patient->id)
                ->withErrors(['email' => __($status)]);
        }

        Notification::send($patient, new PatientPasswordResetLinkSentNotification);

        return redirect()
            ->route('admin.patients.show', $patient->id)
            ->with('status', __('Password reset link sent to :email', ['email' => (string) $patient->email]));
    }

    public function upsertAppointmentNote(Request $request, int $patient, int $appointment): RedirectResponse
    {
        Patient::query()->findOrFail($patient);

        $appt = Appointment::query()
            ->whereKey($appointment)
            ->where('patient_id', $patient)
            ->firstOrFail();

        $keys = ['patient_concern', 'doctor_notes', 'instructions', 'alerts', 'appointment_remarks', 'admin_notes'];
        $rules = [];
        foreach ($keys as $key) {
            $rules[$key] = ['nullable', 'string', 'max:65535'];
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $errorKeys = array_keys($validator->errors()->getMessages());
            $firstErrorField = $errorKeys[0] ?? 'patient_concern';

            return redirect()
                ->route('admin.patients.show', $patient)
                ->withFragment('tab-patient-appointments')
                ->withErrors($validator)
                ->withInput($request->only($keys))
                ->with('appointment_note_error_id', $appt->id)
                ->with('appointment_note_error_field', $firstErrorField);
        }

        $data = collect($validator->validated())
            ->map(static function ($value) {
                if ($value === null) {
                    return null;
                }
                $trimmed = trim((string) $value);

                return $trimmed === '' ? null : $trimmed;
            })
            ->all();

        $hasContent = collect($data)->contains(static fn ($v) => $v !== null && $v !== '');

        if (! $hasContent) {
            $existingNote = AppointmentNote::query()->where('appointment_id', $appt->id)->first();
            if ($existingNote) {
                $existingNote->delete();
            }

            return redirect()
                ->route('admin.patients.show', $patient)
                ->withFragment('tab-patient-appointments')
                ->with('status', __('Appointment note cleared (empty fields).'));
        }

        $existing = AppointmentNote::query()->where('appointment_id', $appt->id)->first();
        $oldSnapshot = [];
        foreach ($keys as $key) {
            $oldSnapshot[$key] = $existing?->{$key};
        }

        $admin = auth('admin')->user();
        $data['section_authors'] = AppointmentNote::mergeAuthorsOnFieldChanges(
            is_array($existing?->section_authors) ? $existing->section_authors : null,
            $oldSnapshot,
            $data,
            $keys,
            AppointmentNote::authorPayloadFromUserName('admin', $admin?->name),
        );

        AppointmentNote::query()->updateOrCreate(
            ['appointment_id' => $appt->id],
            $data
        );

        return redirect()
            ->route('admin.patients.show', $patient)
            ->withFragment('tab-patient-appointments')
            ->with('status', __('Appointment note saved.'));
    }

    public function clearAppointmentNoteField(int $patient, int $appointment, string $field): RedirectResponse
    {
        $allowed = ['patient_concern', 'doctor_notes', 'instructions', 'alerts', 'appointment_remarks', 'admin_notes'];
        if (! in_array($field, $allowed, true)) {
            abort(404);
        }

        Patient::query()->findOrFail($patient);

        $appt = Appointment::query()
            ->whereKey($appointment)
            ->where('patient_id', $patient)
            ->firstOrFail();

        $note = AppointmentNote::query()->where('appointment_id', $appt->id)->first();

        if (! $note) {
            return redirect()
                ->route('admin.patients.show', $patient)
                ->withFragment('tab-patient-appointments')
                ->withErrors(['appointment_note' => __('No note exists for this appointment.')]);
        }

        $authors = is_array($note->section_authors) ? $note->section_authors : [];
        unset($authors[$field]);

        $note->update([
            $field => null,
            'section_authors' => $authors,
        ]);
        $note->refresh();

        $allEmpty = collect($allowed)->every(static fn (string $k): bool => blank($note->{$k}));

        if ($allEmpty) {
            $note->delete();
        }

        return redirect()
            ->route('admin.patients.show', $patient)
            ->withFragment('tab-patient-appointments')
            ->with('status', __('Note section removed.'));
    }

    public function destroyAppointmentNote(int $patient, int $appointment): RedirectResponse
    {
        Patient::query()->findOrFail($patient);

        $appt = Appointment::query()
            ->whereKey($appointment)
            ->where('patient_id', $patient)
            ->firstOrFail();

        $note = AppointmentNote::query()->where('appointment_id', $appt->id)->first();
        if ($note) {
            $note->delete();
        }

        return redirect()
            ->route('admin.patients.show', $patient)
            ->withFragment('tab-patient-appointments')
            ->with('status', __('Appointment note deleted.'));
    }

    private function canManageStatus(): bool
    {
        $admin = auth('admin')->user();

        return $admin?->hasRole('super admin', 'superadmin', 'admin') ?? false;
    }

    private function autoDeactivateInactivePatients(): void
    {
        $cutoff = Carbon::now()->subMonthsNoOverflow(6)->startOfDay()->toDateString();

        $activePatients = Patient::query()
            ->select('id', 'created_at', 'updated_at')
            ->where('status', 'active')
            ->get();

        foreach ($activePatients as $activePatient) {
            $latestAppointment = Appointment::query()
                ->where('patient_id', $activePatient->id)
                ->whereNotNull('appointment_date')
                ->max('appointment_date');

            $lastActivity = $latestAppointment
                ? Carbon::parse((string) $latestAppointment)
                : Carbon::parse((string) ($activePatient->updated_at ?? $activePatient->created_at));

            if ($lastActivity->toDateString() < $cutoff) {
                Patient::query()
                    ->whereKey($activePatient->id)
                    ->update(['status' => 'inactive']);
            }
        }
    }
}
