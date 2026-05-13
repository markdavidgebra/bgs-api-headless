<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentNote;
use App\Models\Product;
use App\Models\TreatmentPackageUsageHistory;
use App\Models\TreatmentPatientPackage;
use App\Notifications\Patient\AppointmentRescheduledPatientNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class DoctorAppointmentController extends Controller
{
    public function index(Request $request): View
    {
        $today = now()->toDateString();

        $dateFilter = $request->string('date_filter')->toString() ?: 'today';
        $status = $request->string('status')->toString();
        $search = trim($request->string('search')->toString());
        $customDate = $request->string('custom_date')->toString();
        $viewMode = $request->string('view')->toString() ?: 'table';

        $baseQuery = Appointment::query()
            ->with(['patient:id,name', 'service:id,name', 'note', 'doctor:id,name']);

        if ($status) {
            $baseQuery->where('status', $status);
        }

        if ($search !== '') {
            $baseQuery->whereHas('patient', function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%');
            });
        }

        $appointmentsQuery = clone $baseQuery;

        if ($dateFilter === 'today') {
            $appointmentsQuery->whereDate('appointment_date', $today);
        } elseif ($dateFilter === 'tomorrow') {
            $appointmentsQuery->whereDate('appointment_date', now()->addDay()->toDateString());
        } elseif ($dateFilter === 'custom' && $customDate) {
            $appointmentsQuery->whereDate('appointment_date', $customDate);
        }

        $appointments = $appointmentsQuery
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->paginate(10)
            ->withQueryString();

        $anchorDate = match ($dateFilter) {
            'tomorrow' => Carbon::tomorrow(),
            'custom' => filled($customDate) ? Carbon::parse($customDate) : Carbon::today(),
            default => Carbon::today(),
        };

        $weekStart = $anchorDate->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $anchorDate->copy()->endOfWeek(Carbon::SUNDAY);

        $weeklyAppointments = (clone $baseQuery)
            ->whereBetween('appointment_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->get();

        $calendarDays = collect(range(0, 6))->map(function (int $offset) use ($weekStart, $weeklyAppointments) {
            $date = $weekStart->copy()->addDays($offset);
            $dateKey = $date->toDateString();

            return [
                'date' => $date,
                'appointments' => $weeklyAppointments
                    ->filter(function ($appointment) use ($dateKey) {
                        return optional($appointment->appointment_date)->toDateString() === $dateKey;
                    })
                    ->values(),
            ];
        });

        $timelineDate = $anchorDate->toDateString();
        $timelineAppointments = (clone $baseQuery)
            ->whereDate('appointment_date', $timelineDate)
            ->orderBy('appointment_time')
            ->get();

        $timelineHours = collect(range(6, 20));

        $statusOptions = [
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];

        return view('doctor.appointments.index', compact(
            'appointments',
            'dateFilter',
            'status',
            'search',
            'customDate',
            'statusOptions',
            'viewMode',
            'calendarDays',
            'weekStart',
            'weekEnd',
            'timelineAppointments',
            'timelineHours',
            'timelineDate',
        ));
    }

    public function show(Appointment $appointment): View
    {
        $appointment = $appointment->load(['patient', 'service', 'note', 'timelines', 'prescribedProducts', 'doctor:id,name']);
        $patientPackages = TreatmentPatientPackage::query()
            ->where('patient_id', $appointment->patient_id)
            ->with('treatmentPackage:id,name')
            ->orderByDesc('start_date')
            ->orderByDesc('purchased_at')
            ->orderByDesc('id')
            ->get();

        $patientPackage = $this->resolvePatientPackageForAppointment($appointment);
        if ($patientPackage === null && $patientPackages->isNotEmpty()) {
            $patientPackage = $patientPackages->first();
        }
        $serviceChecklist = collect();
        $checkedServiceSessionKeys = [];

        if ($patientPackage !== null) {
            $patientPackage->loadMissing('treatmentPackage.services:id,name');
            $completedByService = TreatmentPackageUsageHistory::query()
                ->where('patient_package_id', $patientPackage->id)
                ->where('status', 'completed')
                ->selectRaw('service_id, COUNT(*) as total_done')
                ->groupBy('service_id')
                ->pluck('total_done', 'service_id')
                ->map(static fn ($v): int => (int) $v)
                ->all();

            foreach ($patientPackage->treatmentPackage?->services ?? [] as $service) {
                $serviceId = (int) $service->id;
                $requiredSessions = max(1, (int) ($service->pivot->sessions ?? 1));
                $doneSessions = min($requiredSessions, (int) ($completedByService[$serviceId] ?? 0));

                for ($sessionNo = 1; $sessionNo <= $requiredSessions; $sessionNo++) {
                    $serviceChecklist->push([
                        'key' => $serviceId.':'.$sessionNo,
                        'service_id' => $serviceId,
                        'service_name' => (string) $service->name,
                        'session_no' => $sessionNo,
                        'required_sessions' => $requiredSessions,
                        'is_done' => $sessionNo <= $doneSessions,
                    ]);
                }
            }

            $checkedServiceSessionKeys = $serviceChecklist
                ->filter(static fn (array $row): bool => $row['is_done'] === true)
                ->pluck('key')
                ->values()
                ->all();
        }

        return view('doctor.appointments.show', compact('appointment', 'patientPackage', 'patientPackages', 'serviceChecklist', 'checkedServiceSessionKeys'));
    }

    public function createNotes(Appointment $appointment): View
    {
        $appointment = $appointment->load(['patient', 'service', 'note', 'prescribedProducts', 'doctor:id,name']);
        $appointmentNote = $appointment->note;

        $products = Product::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'sku',
                'unit',
                'stock_quantity',
                'minimum_stock_alert',
                'selling_price',
                'discount_price',
            ]);

        return view('doctor.appointments.create', compact('appointment', 'appointmentNote', 'products'));
    }

    public function startSession(Request $request, Appointment $appointment): RedirectResponse
    {
        if ($request->isMethod('get')) {
            return redirect()
                ->route('doctor.appointments.show', $appointment)
                ->with('info', __('Opening this link in the browser does not start the session. Use the “Start session” button below or on the appointments list.'));
        }

        if ($appointment->status === 'pending' || $appointment->status === 'rescheduled') {
            $appointment->update(['status' => 'confirmed']);
        }

        return back()->with('success', 'Session started successfully.');
    }

    public function markCompleted(Appointment $appointment): RedirectResponse
    {
        $appointment->update(['status' => 'completed']);

        return back()->with('success', 'Appointment marked as completed.');
    }

    public function updateSessionDone(Request $request, Appointment $appointment): RedirectResponse
    {
        $validated = $request->validate([
            'session_done' => ['nullable', 'in:1'],
        ]);

        $isDone = ($validated['session_done'] ?? null) === '1';

        $appointment->update([
            'status' => $isDone ? 'completed' : 'confirmed',
        ]);

        return back()->with('success', $isDone
            ? 'Session marked as done.'
            : 'Session marked as not done.'
        );
    }

    public function markNoShow(Appointment $appointment): RedirectResponse
    {
        $appointment->update(['status' => 'cancelled']);

        return back()->with('success', 'Appointment marked as no-show.');
    }

    public function updateTreatmentProgress(Request $request, Appointment $appointment): RedirectResponse
    {
        $patientPackage = $this->resolvePatientPackageForAppointment($appointment);

        if ($patientPackage === null) {
            return back()->withErrors([
                'treatment_progress' => 'No treatment package is linked to this appointment service.',
            ]);
        }

        $patientPackage->loadMissing('treatmentPackage.services:id,name');
        $requiredByService = [];
        foreach ($patientPackage->treatmentPackage?->services ?? [] as $service) {
            $requiredByService[(int) $service->id] = max(1, (int) ($service->pivot->sessions ?? 1));
        }

        $validated = $request->validate([
            'checked_service_sessions' => ['nullable', 'array'],
            'checked_service_sessions.*' => ['string', 'regex:/^(\d+):(\d+)$/'],
        ]);

        $desiredDoneByService = [];
        foreach ($validated['checked_service_sessions'] ?? [] as $token) {
            if (! is_string($token)) {
                continue;
            }
            [$serviceIdRaw, $sessionNoRaw] = array_pad(explode(':', $token, 2), 2, null);
            $serviceId = (int) $serviceIdRaw;
            $sessionNo = (int) $sessionNoRaw;

            if ($serviceId < 1 || $sessionNo < 1 || ! isset($requiredByService[$serviceId])) {
                continue;
            }

            $required = $requiredByService[$serviceId];
            if ($sessionNo > $required) {
                continue;
            }

            $desiredDoneByService[$serviceId] = max($desiredDoneByService[$serviceId] ?? 0, $sessionNo);
        }

        $expectedTotal = array_sum($requiredByService);
        $totalSessions = max((int) ($patientPackage->total_sessions ?? 0), $expectedTotal);
        $newUsedSessions = array_sum($desiredDoneByService);
        $newRemainingSessions = max($totalSessions - $newUsedSessions, 0);
        $newStatus = $newUsedSessions >= $totalSessions && $totalSessions > 0
            ? 'completed'
            : ($newUsedSessions > 0 ? 'ongoing' : 'pending');

        DB::transaction(function () use ($patientPackage, $appointment, $requiredByService, $desiredDoneByService, $newUsedSessions, $newRemainingSessions, $newStatus, $totalSessions): void {
            $patientPackage->update([
                'total_sessions' => $totalSessions,
                'used_sessions' => $newUsedSessions,
                'remaining_sessions' => $newRemainingSessions,
                'status' => $newStatus,
            ]);

            $existingDoneByService = TreatmentPackageUsageHistory::query()
                ->where('patient_package_id', $patientPackage->id)
                ->where('status', 'completed')
                ->selectRaw('service_id, COUNT(*) as total_done')
                ->groupBy('service_id')
                ->pluck('total_done', 'service_id')
                ->map(static fn ($v): int => (int) $v)
                ->all();

            foreach ($requiredByService as $serviceId => $requiredSessions) {
                $desired = min($requiredSessions, (int) ($desiredDoneByService[$serviceId] ?? 0));
                $existing = min($requiredSessions, (int) ($existingDoneByService[$serviceId] ?? 0));
                $diff = $desired - $existing;

                if ($diff > 0) {
                    for ($i = 0; $i < $diff; $i++) {
                        TreatmentPackageUsageHistory::query()->create([
                            'patient_package_id' => $patientPackage->id,
                            'patient_id' => $appointment->patient_id,
                            'service_id' => $serviceId,
                            'used_on' => now()->toDateString(),
                            'session_change' => -1,
                            'status' => 'completed',
                            'notes' => 'Doctor progress checklist from appointment #'.$appointment->appointment_no,
                        ]);
                    }
                } elseif ($diff < 0) {
                    $removeCount = abs($diff);
                    $toDelete = TreatmentPackageUsageHistory::query()
                        ->where('patient_package_id', $patientPackage->id)
                        ->where('service_id', $serviceId)
                        ->where('status', 'completed')
                        ->orderByDesc('used_on')
                        ->orderByDesc('id')
                        ->limit($removeCount)
                        ->pluck('id');

                    if ($toDelete->isNotEmpty()) {
                        TreatmentPackageUsageHistory::query()
                            ->whereIn('id', $toDelete->all())
                            ->delete();
                    }
                }
            }
        });

        return back()->with('success', 'Treatment progress updated.');
    }

    public function addNotes(Request $request, Appointment $appointment): RedirectResponse
    {
        $validated = $request->validate([
            'patient_concern' => ['nullable', 'string', 'max:2000'],
            'appointment_remarks' => ['nullable', 'string', 'max:2000'],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
            'doctor_notes' => ['nullable', 'string', 'max:2000'],
            'instructions' => ['nullable', 'string', 'max:2000'],
            'alerts' => ['nullable', 'string', 'max:1000'],
            'observations' => ['nullable', 'string', 'max:2000'],
            'procedure_done' => ['nullable', 'string', 'max:2000'],
            'recommendation' => ['nullable', 'string', 'max:2000'],
            'follow_up_needed' => ['nullable', 'string', 'max:1000'],
            'prescribe' => ['nullable', 'array'],
            'qty' => ['nullable', 'array'],
            'qty.*' => ['nullable', 'integer', 'min:1', 'max:99999'],
        ]);

        $existingNote = AppointmentNote::query()
            ->where('appointment_id', $appointment->id)
            ->first();

        $prescribeSync = $this->buildPrescriptionSyncPayload(
            $request->input('prescribe', []),
            $request->input('qty', []),
        );

        $hasAnyNoteValue = collect([
            $validated['patient_concern'] ?? null,
            $validated['appointment_remarks'] ?? null,
            $validated['admin_notes'] ?? null,
            $validated['doctor_notes'] ?? null,
            $validated['instructions'] ?? null,
            $validated['alerts'] ?? null,
            $validated['observations'] ?? null,
            $validated['procedure_done'] ?? null,
            $validated['recommendation'] ?? null,
            $validated['follow_up_needed'] ?? null,
        ])->contains(fn ($value) => filled($value));

        if (! $hasAnyNoteValue && $prescribeSync === []) {
            return back()
                ->withErrors(['observations' => 'Please provide at least one treatment note field or prescribe a product.'])
                ->withInput();
        }

        $noteFieldKeys = ['patient_concern', 'appointment_remarks', 'admin_notes', 'doctor_notes', 'instructions', 'alerts'];

        $oldSnapshot = [];
        foreach ($noteFieldKeys as $key) {
            $oldSnapshot[$key] = $existingNote?->{$key};
        }

        $newPayload = [
            'patient_concern' => $validated['patient_concern'] ?? $existingNote?->patient_concern,
            'appointment_remarks' => $validated['appointment_remarks'] ?? $validated['procedure_done'] ?? $existingNote?->appointment_remarks,
            'admin_notes' => $validated['admin_notes'] ?? $existingNote?->admin_notes,
            'doctor_notes' => $validated['doctor_notes'] ?? $validated['observations'] ?? $existingNote?->doctor_notes,
            'instructions' => $validated['instructions'] ?? $validated['recommendation'] ?? $existingNote?->instructions,
            'alerts' => $validated['alerts'] ?? $validated['follow_up_needed'] ?? $existingNote?->alerts,
        ];

        $doctor = auth('doctor')->user();
        $newPayload['section_authors'] = AppointmentNote::mergeAuthorsOnFieldChanges(
            is_array($existingNote?->section_authors) ? $existingNote->section_authors : null,
            $oldSnapshot,
            $newPayload,
            $noteFieldKeys,
            AppointmentNote::authorPayloadFromUserName('doctor', $doctor?->name),
        );

        AppointmentNote::query()->updateOrCreate(
            ['appointment_id' => $appointment->id],
            $newPayload
        );

        $appointment->prescribedProducts()->sync($prescribeSync);

        return back()->with('success', 'Notes added successfully.');
    }

    public function reschedule(Request $request, Appointment $appointment): RedirectResponse
    {
        $validated = $request->validate([
            'appointment_date' => ['required', 'date'],
            'appointment_time' => ['required', 'date_format:H:i'],
        ]);

        $appointment->update([
            'appointment_date' => $validated['appointment_date'],
            'appointment_time' => $validated['appointment_time'],
            'status' => 'rescheduled',
            'reminder_sent_at' => null,
        ]);

        $appointment->load(['patient:id,name,email', 'doctor:id,name', 'service:id,name']);
        if ($appointment->patient && filled($appointment->patient->email)) {
            Notification::send($appointment->patient, new AppointmentRescheduledPatientNotification($appointment));
        }

        return back()->with('success', 'Appointment rescheduled successfully.');
    }

    private function resolvePatientPackageForAppointment(Appointment $appointment): ?TreatmentPatientPackage
    {
        return TreatmentPatientPackage::query()
            ->where('patient_id', $appointment->patient_id)
            ->whereHas('treatmentPackage.services', function ($q) use ($appointment): void {
                $q->where('services.id', $appointment->service_id);
            })
            ->orderByDesc('start_date')
            ->orderByDesc('purchased_at')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * @param  array<string|int, mixed>  $prescribe
     * @param  array<string|int, mixed>  $qty
     * @return array<int, array{quantity: int}>
     */
    private function buildPrescriptionSyncPayload(array $prescribe, array $qty): array
    {
        $allowedIds = Product::query()
            ->where('status', 'active')
            ->pluck('id')
            ->all();

        $allowedSet = array_fill_keys(array_map('intval', $allowedIds), true);

        $sync = [];
        foreach ($prescribe as $productId => $on) {
            if (! $this->prescribeCheckboxIsChecked($on)) {
                continue;
            }
            $pid = (int) $productId;
            if ($pid < 1 || ! isset($allowedSet[$pid])) {
                continue;
            }
            $q = isset($qty[$productId]) ? (int) $qty[$productId] : 1;

            $sync[$pid] = [
                'quantity' => max(1, min(99999, $q)),
            ];
        }

        return $sync;
    }

    private function prescribeCheckboxIsChecked(mixed $on): bool
    {
        return $on === true || $on === 1 || $on === '1' || $on === 'on' || $on === 'yes';
    }
}
