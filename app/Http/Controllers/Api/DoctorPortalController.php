<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\DoctorPortalResponses;
use App\Http\Controllers\Controller;
use App\Support\ManagerPortalAccess;
use App\Models\Appointment;
use App\Models\AppointmentNote;
use App\Models\Doctor;
use App\Models\DoctorNote;
use App\Models\Medication;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class DoctorPortalController extends Controller
{
    use DoctorPortalResponses;

    /**
     * @var list<string>
     */
    private const PRESCRIPTION_STATUSES = ['active', 'completed', 'cancelled'];

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $emailInput = trim((string) ($request->input('email') ?? $request->input('username') ?? ''));
        if ($emailInput === '' || ! filter_var($emailInput, FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::withMessages([
                'email' => ['A valid email is required.'],
            ]);
        }

        $credentials = [
            'email' => $emailInput,
            'password' => (string) $request->input('password'),
        ];

        foreach (['web', 'admin', 'clinical_staff'] as $otherGuard) {
            if (Auth::guard($otherGuard)->check()) {
                Auth::guard($otherGuard)->logout();
            }
        }

        $manager = ManagerPortalAccess::authenticate($credentials);
        if ($manager) {
            Auth::guard('doctor')->login(ManagerPortalAccess::doctorIdentity($manager), true);
        } elseif (
            Auth::guard('admin')->validate($credentials)
            || Auth::guard('clinical_staff')->validate($credentials)
            || Auth::guard('web')->validate($credentials)
        ) {
            throw ValidationException::withMessages([
                'email' => [__('This email belongs to another portal. Sign in there instead.')],
            ]);
        } elseif (! Auth::guard('doctor')->attempt($credentials, true)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        $request->session()->regenerate();

        $doctor = Auth::guard('doctor')->user();
        if (! $doctor || ! $doctor->isActive()) {
            Auth::guard('doctor')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json([
                'message' => __('Your doctor account is not approved yet.'),
            ], 403);
        }

        return response()->json([
            'message' => __('Doctor login successful.'),
            'csrf_token' => csrf_token(),
            'doctor' => $this->doctorPayload($doctor),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('doctor')->logout();
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
            'doctor' => $this->doctorPayload($request->user('doctor')),
        ]);
    }

    public function dashboard(): JsonResponse
    {
        $doctor = $this->currentDoctor();

        $recentNotes = DoctorNote::query()
            ->with(['patient:id,name,email,phone', 'appointment:id,appointment_no,appointment_date,appointment_time,status'])
            ->where('doctor_id', $doctor->id)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $recentPrescriptions = Prescription::query()
            ->with(['patient:id,name,email,phone', 'items'])
            ->where('doctor_id', $doctor->id)
            ->orderByDesc('issued_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return response()->json([
            'stats' => [
                'total_patients' => Patient::query()->count(),
                'my_notes' => DoctorNote::query()->where('doctor_id', $doctor->id)->count(),
                'my_active_prescriptions' => Prescription::query()
                    ->where('doctor_id', $doctor->id)
                    ->where('status', 'active')
                    ->count(),
                'my_prescriptions_total' => Prescription::query()->where('doctor_id', $doctor->id)->count(),
                'appointments_today' => Appointment::query()->whereDate('appointment_date', now()->toDateString())->count(),
            ],
            'recent_notes' => $recentNotes->map(fn (DoctorNote $n) => $this->doctorNotePayload($n))->values(),
            'recent_prescriptions' => $recentPrescriptions
                ->map(fn (Prescription $p) => $this->prescriptionPayload($p))
                ->values(),
        ]);
    }

    public function patientsIndex(Request $request): JsonResponse
    {
        $search = trim($request->string('search')->toString());
        $limit = max(1, min((int) $request->integer('limit', 20), 50));

        $paginator = Patient::query()
            ->when($search !== '', function ($query) use ($search) {
                $term = '%'.addcslashes($search, '%_\\').'%';
                $query->where(function ($inner) use ($term) {
                    $inner->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('phone', 'like', $term);
                });
            })
            ->orderBy('name')
            ->paginate($limit)
            ->withQueryString();

        $patientIds = $paginator->getCollection()->pluck('id')->filter()->unique()->values();

        $appointmentCounts = $patientIds->isEmpty()
            ? collect()
            : Appointment::query()
                ->whereIn('patient_id', $patientIds)
                ->selectRaw('patient_id, COUNT(*) as total')
                ->groupBy('patient_id')
                ->pluck('total', 'patient_id');

        $lastAppointments = $patientIds->isEmpty()
            ? collect()
            : Appointment::query()
                ->with(['service:id,name', 'clinicalStaff:id,name'])
                ->whereIn('patient_id', $patientIds)
                ->orderByDesc('appointment_date')
                ->orderByDesc('appointment_time')
                ->get()
                ->unique('patient_id')
                ->keyBy('patient_id');

        $prescriptionCounts = $patientIds->isEmpty()
            ? collect()
            : Prescription::query()
                ->whereIn('patient_id', $patientIds)
                ->where('status', 'active')
                ->selectRaw('patient_id, COUNT(*) as total')
                ->groupBy('patient_id')
                ->pluck('total', 'patient_id');

        $records = $paginator->getCollection()->map(function (Patient $patient) use (
            $appointmentCounts,
            $lastAppointments,
            $prescriptionCounts
        ) {
            $last = $lastAppointments->get($patient->id);

            return [
                'patient' => $this->patientSummaryPayload($patient),
                'total_appointments' => (int) ($appointmentCounts[$patient->id] ?? 0),
                'active_prescriptions' => (int) ($prescriptionCounts[$patient->id] ?? 0),
                'last_appointment' => $last ? $this->appointmentPayload($last) : null,
            ];
        })->values();

        return response()->json([
            'records' => $records,
            'meta' => $this->doctorPaginationMeta($paginator),
            'search' => $search,
        ]);
    }

    /**
     * The full chart: demographics, every visit with everything the clinical staff
     * recorded, the vital-signs timeline, all doctor notes and all prescriptions.
     */
    public function patientShow(Patient $patient): JsonResponse
    {
        $appointments = $this->patientAppointments($patient);

        $today = now()->toDateString();
        $upcoming = $appointments->filter(fn (Appointment $a) => ($a->appointment_date?->toDateString() ?? '') >= $today)->values();
        $past = $appointments->filter(fn (Appointment $a) => ($a->appointment_date?->toDateString() ?? '') < $today)->values();

        $notesHistory = $appointments
            ->filter(fn (Appointment $a) => AppointmentNote::hasClinicalContent($a->note))
            ->map(fn (Appointment $a) => [
                'appointment' => $this->appointmentPayload($a),
                'note' => $a->note ? $this->notePayload($a->note) : null,
            ])
            ->values();

        $vitalSignsTimeline = $this->vitalSignsTimeline($appointments);
        $appointmentsPayload = [
            'upcoming' => $upcoming->map(fn (Appointment $a) => $this->appointmentPayload($a))->values(),
            'past' => $past->map(fn (Appointment $a) => $this->appointmentPayload($a))->values(),
        ];

        $doctorNotes = DoctorNote::query()
            ->with(['doctor:id,name,specialty,license_no', 'appointment:id,appointment_no,appointment_date,appointment_time,status'])
            ->where('patient_id', $patient->id)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $prescriptions = Prescription::query()
            ->with(['doctor:id,name,specialty,license_no', 'items', 'appointment:id,appointment_no,appointment_date,appointment_time,status'])
            ->where('patient_id', $patient->id)
            ->orderByDesc('issued_at')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'patient' => $this->patientProfilePayload($patient),
            'stats' => [
                'total_appointments' => $appointments->count(),
                'total_notes_on_file' => $notesHistory->count(),
                'total_doctor_notes' => $doctorNotes->count(),
                'active_prescriptions' => $prescriptions->where('status', 'active')->count(),
                'last_appointment' => $appointments->first() ? $this->appointmentPayload($appointments->first()) : null,
            ],
            'appointments' => $appointmentsPayload,
            'all_appointments' => $appointmentsPayload,
            'clinical_notes' => $notesHistory,
            'clinical_history' => $notesHistory,
            'vital_signs_timeline' => $vitalSignsTimeline,
            'vital_signs' => $vitalSignsTimeline,
            'doctor_notes' => $doctorNotes->map(fn (DoctorNote $n) => $this->doctorNotePayload($n))->values(),
            'prescriptions' => $prescriptions->map(fn (Prescription $p) => $this->prescriptionPayload($p))->values(),
        ]);
    }

    public function patientVitals(Patient $patient): JsonResponse
    {
        $appointments = $this->patientAppointments($patient);

        return response()->json([
            'patient' => $this->patientSummaryPayload($patient),
            'vital_signs_timeline' => $this->vitalSignsTimeline($appointments),
            'field_keys' => AppointmentNote::vitalSignFieldKeys(),
            'phases' => AppointmentNote::vitalSignPhases(),
        ]);
    }

    public function storePatientNote(Request $request, Patient $patient): JsonResponse
    {
        $doctor = $this->currentDoctor();

        $validated = $request->validate([
            'note' => ['required', 'string', 'max:20000'],
            'diagnosis' => ['nullable', 'string', 'max:5000'],
            'plan' => ['nullable', 'string', 'max:20000'],
            'appointment_id' => ['nullable', 'integer', 'exists:appointments,id'],
        ]);

        $appointmentId = $this->resolveAppointmentIdForPatient(
            $validated['appointment_id'] ?? null,
            (int) $patient->id,
        );

        $note = DoctorNote::query()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_id' => $appointmentId,
            'note' => $validated['note'],
            'diagnosis' => $validated['diagnosis'] ?? null,
            'plan' => $validated['plan'] ?? null,
        ]);

        $note->load(['patient:id,name,email,phone', 'doctor:id,name,specialty,license_no', 'appointment:id,appointment_no,appointment_date,appointment_time,status']);

        return response()->json([
            'message' => __('Note saved.'),
            'note' => $this->doctorNotePayload($note),
        ], 201);
    }

    public function notesIndex(Request $request): JsonResponse
    {
        $doctor = $this->currentDoctor();
        $limit = max(1, min((int) $request->integer('limit', 15), 50));
        $patientId = $request->integer('patient_id');

        $paginator = DoctorNote::query()
            ->with(['patient:id,name,email,phone', 'doctor:id,name,specialty,license_no', 'appointment:id,appointment_no,appointment_date,appointment_time,status'])
            ->where('doctor_id', $doctor->id)
            ->when($patientId > 0, fn ($query) => $query->where('patient_id', $patientId))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($limit)
            ->withQueryString();

        return response()->json([
            'notes' => $paginator->getCollection()->map(fn (DoctorNote $n) => $this->doctorNotePayload($n))->values(),
            'meta' => $this->doctorPaginationMeta($paginator),
            'filters' => [
                'patient_id' => $patientId > 0 ? $patientId : null,
            ],
        ]);
    }

    public function updateNote(Request $request, DoctorNote $note): JsonResponse
    {
        $this->authorizeOwnedByDoctor((int) $note->doctor_id);

        $validated = $request->validate([
            'note' => ['sometimes', 'required', 'string', 'max:20000'],
            'diagnosis' => ['nullable', 'string', 'max:5000'],
            'plan' => ['nullable', 'string', 'max:20000'],
            'appointment_id' => ['nullable', 'integer', 'exists:appointments,id'],
        ]);

        if (array_key_exists('appointment_id', $validated)) {
            $validated['appointment_id'] = $this->resolveAppointmentIdForPatient(
                $validated['appointment_id'],
                (int) $note->patient_id,
            );
        }

        $note->fill($validated)->save();
        $note->load(['patient:id,name,email,phone', 'doctor:id,name,specialty,license_no', 'appointment:id,appointment_no,appointment_date,appointment_time,status']);

        return response()->json([
            'message' => __('Note updated.'),
            'note' => $this->doctorNotePayload($note),
        ]);
    }

    public function destroyNote(DoctorNote $note): JsonResponse
    {
        $this->authorizeOwnedByDoctor((int) $note->doctor_id);

        $note->delete();

        return response()->json([
            'message' => __('Note deleted.'),
        ]);
    }

    public function medicationsIndex(Request $request): JsonResponse
    {
        $search = trim($request->string('search')->toString());
        $limit = max(1, min((int) $request->integer('limit', 100), 200));

        $medications = Medication::query()
            ->active()
            ->when($search !== '', function ($query) use ($search) {
                $term = '%'.addcslashes($search, '%_\\').'%';
                $query->where(function ($inner) use ($term) {
                    $inner->where('name', 'like', $term)
                        ->orWhere('generic_name', 'like', $term);
                });
            })
            ->orderBy('name')
            ->limit($limit)
            ->get();

        return response()->json([
            'medications' => $medications->map(fn (Medication $m) => $this->medicationPayload($m))->values(),
            'search' => $search,
        ]);
    }

    public function prescriptionsIndex(Request $request): JsonResponse
    {
        $doctor = $this->currentDoctor();
        $limit = max(1, min((int) $request->integer('limit', 15), 50));
        $patientId = $request->integer('patient_id');
        $status = $request->string('status')->toString();

        $paginator = Prescription::query()
            ->with(['patient:id,name,email,phone', 'doctor:id,name,specialty,license_no', 'items'])
            ->where('doctor_id', $doctor->id)
            ->when($patientId > 0, fn ($query) => $query->where('patient_id', $patientId))
            ->when(
                in_array($status, self::PRESCRIPTION_STATUSES, true),
                fn ($query) => $query->where('status', $status),
            )
            ->orderByDesc('issued_at')
            ->orderByDesc('id')
            ->paginate($limit)
            ->withQueryString();

        return response()->json([
            'prescriptions' => $paginator->getCollection()
                ->map(fn (Prescription $p) => $this->prescriptionPayload($p))
                ->values(),
            'meta' => $this->doctorPaginationMeta($paginator),
            'filters' => [
                'patient_id' => $patientId > 0 ? $patientId : null,
                'status' => in_array($status, self::PRESCRIPTION_STATUSES, true) ? $status : null,
            ],
            'status_options' => self::PRESCRIPTION_STATUSES,
        ]);
    }

    public function storePrescription(Request $request): JsonResponse
    {
        $doctor = $this->currentDoctor();

        $validated = $request->validate([
            'patient_id' => ['required', 'integer', 'exists:users,id'],
            'appointment_id' => ['nullable', 'integer', 'exists:appointments,id'],
            'issued_at' => ['nullable', 'date'],
            'diagnosis' => ['nullable', 'string', 'max:5000'],
            'notes' => ['nullable', 'string', 'max:20000'],
            'status' => ['nullable', 'string', Rule::in(self::PRESCRIPTION_STATUSES)],
            'items' => ['required', 'array', 'min:1'],
            'items.*.medication_id' => ['nullable', 'integer', 'exists:medications,id'],
            'items.*.medication_name' => ['nullable', 'string', 'max:255', 'required_without:items.*.medication_id'],
            'items.*.dosage' => ['nullable', 'string', 'max:255'],
            'items.*.frequency' => ['nullable', 'string', 'max:255'],
            'items.*.duration' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'items.*.instructions' => ['nullable', 'string', 'max:5000'],
        ]);

        $appointmentId = $this->resolveAppointmentIdForPatient(
            $validated['appointment_id'] ?? null,
            (int) $validated['patient_id'],
        );

        $prescription = DB::transaction(function () use ($validated, $doctor, $appointmentId) {
            $prescription = Prescription::query()->create([
                'patient_id' => (int) $validated['patient_id'],
                'doctor_id' => $doctor->id,
                'appointment_id' => $appointmentId,
                'issued_at' => $validated['issued_at'] ?? now(),
                'diagnosis' => $validated['diagnosis'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => $validated['status'] ?? 'active',
            ]);

            $this->syncPrescriptionItems($prescription, $validated['items']);

            return $prescription;
        });

        $prescription->load(['patient:id,name,email,phone', 'doctor:id,name,specialty,license_no', 'items', 'appointment:id,appointment_no,appointment_date,appointment_time,status']);

        return response()->json([
            'message' => __('Prescription created.'),
            'prescription' => $this->prescriptionPayload($prescription),
        ], 201);
    }

    public function showPrescription(Prescription $prescription): JsonResponse
    {
        $prescription->load([
            'patient:id,name,email,phone',
            'doctor:id,name,specialty,license_no',
            'items',
            'appointment:id,appointment_no,appointment_date,appointment_time,status',
        ]);

        return response()->json([
            'prescription' => $this->prescriptionPayload($prescription),
            'is_mine' => (int) $prescription->doctor_id === (int) $this->currentDoctor()->id,
        ]);
    }

    public function updatePrescription(Request $request, Prescription $prescription): JsonResponse
    {
        $this->authorizeOwnedByDoctor((int) $prescription->doctor_id);

        $validated = $request->validate([
            'diagnosis' => ['nullable', 'string', 'max:5000'],
            'notes' => ['nullable', 'string', 'max:20000'],
            'status' => ['nullable', 'string', Rule::in(self::PRESCRIPTION_STATUSES)],
            'issued_at' => ['nullable', 'date'],
            'items' => ['sometimes', 'array', 'min:1'],
            'items.*.medication_id' => ['nullable', 'integer', 'exists:medications,id'],
            'items.*.medication_name' => ['nullable', 'string', 'max:255', 'required_without:items.*.medication_id'],
            'items.*.dosage' => ['nullable', 'string', 'max:255'],
            'items.*.frequency' => ['nullable', 'string', 'max:255'],
            'items.*.duration' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'items.*.instructions' => ['nullable', 'string', 'max:5000'],
        ]);

        DB::transaction(function () use ($prescription, $validated) {
            // diagnosis/notes are nullable, so an explicit null clears them; status and
            // issued_at are NOT NULL, so only a filled value replaces the current one.
            foreach (['diagnosis', 'notes'] as $field) {
                if (array_key_exists($field, $validated)) {
                    $prescription->{$field} = $validated[$field];
                }
            }
            if (filled($validated['status'] ?? null)) {
                $prescription->status = $validated['status'];
            }
            if (filled($validated['issued_at'] ?? null)) {
                $prescription->issued_at = $validated['issued_at'];
            }
            $prescription->save();

            if (array_key_exists('items', $validated)) {
                $prescription->items()->delete();
                $this->syncPrescriptionItems($prescription, $validated['items']);
            }
        });

        $prescription->unsetRelation('items');
        $prescription->load(['patient:id,name,email,phone', 'doctor:id,name,specialty,license_no', 'items', 'appointment:id,appointment_no,appointment_date,appointment_time,status']);

        return response()->json([
            'message' => __('Prescription updated.'),
            'prescription' => $this->prescriptionPayload($prescription),
        ]);
    }

    public function cancelPrescription(Prescription $prescription): JsonResponse
    {
        $this->authorizeOwnedByDoctor((int) $prescription->doctor_id);

        $prescription->forceFill(['status' => 'cancelled'])->save();
        $prescription->load(['patient:id,name,email,phone', 'doctor:id,name,specialty,license_no', 'items']);

        return response()->json([
            'message' => __('Prescription cancelled.'),
            'prescription' => $this->prescriptionPayload($prescription),
        ]);
    }

    public function profileShow(): JsonResponse
    {
        return response()->json([
            'doctor' => $this->doctorPayload($this->currentDoctor()),
        ]);
    }

    /**
     * The SPA posts multipart with `_method=PATCH` because PHP only parses
     * multipart bodies on POST.
     */
    public function profileUpdate(Request $request): JsonResponse
    {
        $doctor = $this->currentDoctor();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('doctors', 'email')->ignore($doctor->id)],
            'phone' => ['nullable', 'string', 'max:32'],
            'specialty' => ['nullable', 'string', 'max:255'],
            'license_no' => ['nullable', 'string', 'max:255'],
            'prc_expiry' => ['nullable', 'date'],
            'ptr_no' => ['nullable', 'string', 'max:255'],
            's2_license_no' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:5000'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'signature' => ['nullable', 'image', 'max:2048'],
        ]);

        $data = collect($validated)->except(['photo', 'signature'])->all();

        if ($request->hasFile('photo')) {
            $data['image_path'] = $this->storeDoctorUpload($request->file('photo'), (int) $doctor->id, 'photo', $doctor->image_path);
        }

        if ($request->hasFile('signature')) {
            $data['signature_path'] = $this->storeDoctorUpload($request->file('signature'), (int) $doctor->id, 'signature', $doctor->signature_path);
        }

        $doctor->fill($data)->save();

        return response()->json([
            'message' => __('Profile updated successfully.'),
            'doctor' => $this->doctorPayload($doctor->refresh()),
        ]);
    }

    public function profileUpdatePassword(Request $request): JsonResponse
    {
        $doctor = $this->currentDoctor();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if (! Hash::check($validated['current_password'], (string) $doctor->password)) {
            throw ValidationException::withMessages([
                'current_password' => [__('The current password is incorrect.')],
            ]);
        }

        $doctor->forceFill([
            'password' => $validated['password'],
            'pending_password_plain' => null,
        ])->save();

        return response()->json([
            'message' => __('Password updated successfully.'),
        ]);
    }

    private function currentDoctor(): Doctor
    {
        $doctor = Auth::guard('doctor')->user();
        abort_if($doctor === null, 401);

        return $doctor;
    }

    private function authorizeOwnedByDoctor(int $ownerDoctorId): void
    {
        abort_unless($ownerDoctorId === (int) $this->currentDoctor()->id, 403);
    }

    /**
     * All visits for a patient, newest first, with everything notePayload() needs.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Appointment>
     */
    private function patientAppointments(Patient $patient)
    {
        return Appointment::query()
            ->with(['service:id,name', 'clinicalStaff:id,name', 'note'])
            ->where('patient_id', $patient->id)
            ->orderByDesc('appointment_date')
            ->orderByDesc('appointment_time')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * An appointment may only be attached to a note/prescription for its own patient.
     */
    private function resolveAppointmentIdForPatient(mixed $appointmentId, int $patientId): ?int
    {
        if ($appointmentId === null || $appointmentId === '') {
            return null;
        }

        $appointment = Appointment::query()->find((int) $appointmentId);
        if ($appointment === null || (int) $appointment->patient_id !== $patientId) {
            throw ValidationException::withMessages([
                'appointment_id' => [__('That appointment does not belong to this patient.')],
            ]);
        }

        return (int) $appointment->id;
    }

    /**
     * Create the item rows, snapshotting name/strength/form/route off the formulary so
     * later formulary edits cannot rewrite an already-issued prescription.
     *
     * @param  list<array<string, mixed>>  $items
     */
    private function syncPrescriptionItems(Prescription $prescription, array $items): void
    {
        $medicationIds = collect($items)
            ->pluck('medication_id')
            ->filter()
            ->map(static fn ($id): int => (int) $id)
            ->unique()
            ->values();

        $medications = $medicationIds->isEmpty()
            ? collect()
            : Medication::query()->whereIn('id', $medicationIds)->get()->keyBy('id');

        foreach (array_values($items) as $index => $row) {
            $medicationId = isset($row['medication_id']) && $row['medication_id'] !== null
                ? (int) $row['medication_id']
                : null;
            $medication = $medicationId !== null ? $medications->get($medicationId) : null;

            $name = $medication?->name ?? trim((string) ($row['medication_name'] ?? ''));
            if ($name === '') {
                throw ValidationException::withMessages([
                    "items.{$index}.medication_name" => [__('A medication name is required.')],
                ]);
            }

            PrescriptionItem::query()->create([
                'prescription_id' => $prescription->id,
                'medication_id' => $medicationId,
                'medication_name' => $name,
                'strength' => $medication?->strength ?? ($row['strength'] ?? null),
                'form' => $medication?->form ?? ($row['form'] ?? null),
                'route' => $medication?->route ?? ($row['route'] ?? null),
                'dosage' => $row['dosage'] ?? null,
                'frequency' => $row['frequency'] ?? null,
                'duration' => $row['duration'] ?? null,
                'quantity' => isset($row['quantity']) && $row['quantity'] !== null ? (int) $row['quantity'] : null,
                'instructions' => $row['instructions'] ?? null,
                'sort_order' => $index,
            ]);
        }
    }

    private function storeDoctorUpload(mixed $file, int $doctorId, string $kind, ?string $previousPath): string
    {
        $this->removeStoredUpload($previousPath);

        $dir = public_path('uploads/doctor-portal');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $ext = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'jpg';
        $filename = $doctorId.'_'.$kind.'_'.uniqid('', true).'.'.$ext;
        $file->move($dir, $filename);

        return 'uploads/doctor-portal/'.$filename;
    }

    private function removeStoredUpload(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        $normalized = ltrim($path, '/');
        if (! str_starts_with($normalized, 'uploads/')) {
            return;
        }

        $fullPath = public_path($normalized);
        if (is_file($fullPath)) {
            unlink($fullPath);
        }
    }
}
