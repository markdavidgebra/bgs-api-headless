<?php

namespace App\Http\Controllers\ClinicalStaff;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentNote;
use App\Models\Product;
use App\Models\TreatmentPackageUsageHistory;
use App\Models\TreatmentPatientPackage;
use App\Notifications\Patient\AppointmentRescheduledPatientNotification;
use App\Rules\BookableAppointmentDate;
use App\Support\ManagerPortalAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClinicalStaffAppointmentController extends Controller
{
    public function index(Request $request): View
    {
        $today = now()->toDateString();

        $dateFilter = $request->string('date_filter')->toString() ?: 'today';
        $status = $request->string('status')->toString();
        $search = trim($request->string('search')->toString());
        $customDate = $request->string('custom_date')->toString();
        $viewMode = $request->string('view')->toString() ?: 'calendar';

        $baseQuery = Appointment::query()
            ->with(['patient:id,name', 'service:id,name', 'note', 'clinicalStaff:id,name']);

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

        $monthCursor = null;
        $appointmentsByDate = null;
        $prevMonth = null;
        $nextMonth = null;

        if ($viewMode === 'calendar') {
            $defaultMonth = match ($dateFilter) {
                'tomorrow' => now()->addDay()->format('Y-m'),
                'custom' => filled($customDate) ? Carbon::parse($customDate)->format('Y-m') : now()->format('Y-m'),
                default => now()->format('Y-m'),
            };
            $monthInput = (string) $request->input('month', $defaultMonth);
            try {
                $monthCursor = Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth();
            } catch (\Throwable) {
                $monthCursor = now()->startOfMonth();
            }

            $rangeStart = $monthCursor->copy()->startOfMonth();
            $rangeEnd = $monthCursor->copy()->endOfMonth();

            $calendarAppointments = (clone $baseQuery)
                ->whereBetween('appointment_date', [$rangeStart->toDateString(), $rangeEnd->toDateString()])
                ->orderBy('appointment_date')
                ->orderBy('appointment_time')
                ->get();

            $appointmentsByDate = $calendarAppointments
                ->groupBy(static function (Appointment $a): string {
                    if (empty($a->appointment_date)) {
                        return '';
                    }

                    return Carbon::parse((string) $a->appointment_date)->toDateString();
                });

            $prevMonth = $monthCursor->copy()->subMonth()->format('Y-m');
            $nextMonth = $monthCursor->copy()->addMonth()->format('Y-m');
        }

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

        return view('clinical-staff.appointments.index', compact(
            'appointments',
            'dateFilter',
            'status',
            'search',
            'customDate',
            'statusOptions',
            'viewMode',
            'monthCursor',
            'appointmentsByDate',
            'prevMonth',
            'nextMonth',
            'timelineAppointments',
            'timelineHours',
            'timelineDate',
        ));
    }

    public function show(Appointment $appointment): View
    {
        $appointment = $appointment->load(['patient', 'service', 'note', 'timelines', 'prescribedProducts', 'clinicalStaff:id,name']);
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

        return view('clinical-staff.appointments.show', compact('appointment', 'patientPackage', 'patientPackages', 'serviceChecklist', 'checkedServiceSessionKeys'));
    }

    public function createNotes(Appointment $appointment): View|RedirectResponse
    {
        if (in_array($appointment->status, ['pending', 'rescheduled'], true)) {
            return back()->with('info', __('Please wait for a manager to approve this appointment before adding a note.'));
        }

        $appointment = $appointment->load(['patient', 'service', 'note', 'prescribedProducts', 'clinicalStaff:id,name']);
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

        return view('clinical-staff.appointments.create', compact('appointment', 'appointmentNote', 'products'));
    }

    public function approve(Appointment $appointment): RedirectResponse
    {
        $staff = auth('clinical_staff')->user();
        if (! ManagerPortalAccess::canApproveAppointments($staff)) {
            return back()->with('error', __('Only a manager or CEO can approve appointments.'));
        }

        $status = strtolower((string) ($appointment->status ?? ''));
        if (! in_array($status, ['pending', 'rescheduled'], true)) {
            return back()->with('error', __('This appointment cannot be approved.'));
        }

        $appointment->update([
            'status' => 'confirmed',
            'updated_by' => ManagerPortalAccess::linkedManagerId($staff),
        ]);

        return back()->with('status', __('Appointment approved successfully.'));
    }

    public function startSession(Request $request, Appointment $appointment): RedirectResponse
    {
        if ($request->isMethod('get')) {
            return redirect()
                ->route('clinical_staff.appointments.show', $appointment)
                ->with('info', __('Opening this link in the browser does not start the session. Use the “Start session” button below or on the appointments list.'));
        }

        if ($appointment->status === 'pending' || $appointment->status === 'rescheduled') {
            return back()->with('info', __('Please wait for a manager to approve this appointment before starting the session.'));
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
                            'notes' => 'Clinical staff progress checklist from appointment #'.$appointment->appointment_no,
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
            'clinical_notes' => ['nullable', 'string', 'max:2000'],
            'instructions' => ['nullable', 'string', 'max:2000'],
            'alerts' => ['nullable', 'string', 'max:1000'],
            'observations' => ['nullable', 'string', 'max:2000'],
            'procedure_done' => ['nullable', 'string', 'max:2000'],
            'recommendation' => ['nullable', 'string', 'max:2000'],
            'follow_up_needed' => ['nullable', 'string', 'max:1000'],
            'vital_blood_pressure' => ['nullable', 'string', 'max:50'],
            'vital_heart_rate' => ['nullable', 'string', 'max:32'],
            'vital_temperature' => ['nullable', 'string', 'max:32'],
            'vital_respiratory_rate' => ['nullable', 'string', 'max:32'],
            'vital_oxygen_saturation' => ['nullable', 'string', 'max:32'],
            'vital_weight' => ['nullable', 'string', 'max:32'],
            'vital_height' => ['nullable', 'string', 'max:32'],
            'vital_signs' => ['nullable', 'array'],
            'vital_signs.before' => ['nullable', 'array'],
            'vital_signs.during' => ['nullable', 'array'],
            'vital_signs.after' => ['nullable', 'array'],
            'vital_signs.extra' => ['nullable', 'array'],
            'vital_signs.extra.*.id' => ['nullable', 'string', 'max:64'],
            'vital_signs.extra.*.time' => ['nullable', 'string', 'max:32'],
            'vital_signs.extra.*.vital_blood_pressure' => ['nullable', 'string', 'max:50'],
            'vital_signs.extra.*.vital_heart_rate' => ['nullable', 'string', 'max:32'],
            'vital_signs.extra.*.vital_temperature' => ['nullable', 'string', 'max:32'],
            'vital_signs.extra.*.vital_respiratory_rate' => ['nullable', 'string', 'max:32'],
            'vital_signs.extra.*.vital_oxygen_saturation' => ['nullable', 'string', 'max:32'],
            'vital_signs.extra.*.vital_weight' => ['nullable', 'string', 'max:32'],
            'vital_signs.extra.*.vital_height' => ['nullable', 'string', 'max:32'],
            'vital_signs.*.vital_blood_pressure' => ['nullable', 'string', 'max:50'],
            'vital_signs.*.vital_heart_rate' => ['nullable', 'string', 'max:32'],
            'vital_signs.*.vital_temperature' => ['nullable', 'string', 'max:32'],
            'vital_signs.*.vital_respiratory_rate' => ['nullable', 'string', 'max:32'],
            'vital_signs.*.vital_oxygen_saturation' => ['nullable', 'string', 'max:32'],
            'vital_signs.*.vital_weight' => ['nullable', 'string', 'max:32'],
            'vital_signs.*.vital_height' => ['nullable', 'string', 'max:32'],
            'body_analyzer_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:3072'],
            'bottle_citrus_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:3072'],
            'lemon_bottle_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:3072'],
            'aqualyx_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:3072'],
            'drip_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:3072'],
            'micro_needling_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:3072'],
            'prescribe' => ['nullable', 'array'],
            'qty' => ['nullable', 'array'],
            'qty.*' => ['nullable', 'integer', 'min:1', 'max:99999'],
            'mobility' => ['nullable', 'string', Rule::in(['ambulatory', 'with_assistive', 'wheelchair'])],
        ]);

        $existingNote = AppointmentNote::query()
            ->where('appointment_id', $appointment->id)
            ->first();

        $prescribeSync = $this->buildPrescriptionSyncPayload(
            $request->input('prescribe', []),
            $request->input('qty', []),
        );

        $hasVitalRequest = $request->exists('vital_signs')
            || collect(AppointmentNote::vitalSignFieldKeys())->contains(
                fn (string $key) => $request->exists($key)
            );

        $phasedVitals = $hasVitalRequest
            ? ($request->exists('vital_signs')
                ? AppointmentNote::normalizeVitalSignsInput($request->input('vital_signs'), $existingNote)
                : AppointmentNote::mergeLegacyVitalSignsIntoPhases($validated, $existingNote))
            : ($existingNote?->resolvedVitalSigns() ?? AppointmentNote::emptyVitalSigns());

        $flatVitals = AppointmentNote::flattenPrimaryVitalSigns($phasedVitals);

        $hasAnyNoteValue = collect([
            $validated['patient_concern'] ?? null,
            $validated['appointment_remarks'] ?? null,
            $validated['admin_notes'] ?? null,
            $validated['clinical_notes'] ?? null,
            $validated['instructions'] ?? null,
            $validated['alerts'] ?? null,
            $validated['observations'] ?? null,
            $validated['procedure_done'] ?? null,
            $validated['recommendation'] ?? null,
            $validated['follow_up_needed'] ?? null,
            ...array_values($flatVitals),
        ])->contains(fn ($value) => filled($value));

        $hasAnyNoteValue = $hasAnyNoteValue || AppointmentNote::vitalSignsHaveValues($phasedVitals);

        $hasAnyNoteValue = $hasAnyNoteValue || $request->filled('mobility');

        $hasAnyNoteValue = $hasAnyNoteValue || $request->hasFile('body_analyzer_image') || filled($existingNote?->body_analyzer_image_path);
        $hasAnyNoteValue = $hasAnyNoteValue || $request->hasFile('bottle_citrus_image') || filled($existingNote?->bottle_citrus_image_path);
        $hasAnyNoteValue = $hasAnyNoteValue || $request->hasFile('lemon_bottle_image') || filled($existingNote?->lemon_bottle_image_path);
        $hasAnyNoteValue = $hasAnyNoteValue || $request->hasFile('aqualyx_image') || filled($existingNote?->aqualyx_image_path);
        $hasAnyNoteValue = $hasAnyNoteValue || $request->hasFile('drip_image') || filled($existingNote?->drip_image_path);
        $hasAnyNoteValue = $hasAnyNoteValue || $request->hasFile('micro_needling_image') || filled($existingNote?->micro_needling_image_path);

        if (! $hasAnyNoteValue && $prescribeSync === []) {
            return back()
                ->withErrors(['observations' => __('Please provide at least one treatment note field, vital sign, prescribe a product, or an optional clinical image (body analyzer, bottle citrus, lemon bottle, Aqualyx, drip, or micro needling).')])
                ->withInput();
        }

        $noteFieldKeys = array_merge(
            ['patient_concern', 'appointment_remarks', 'admin_notes', 'clinical_notes', 'instructions', 'alerts', 'mobility'],
            AppointmentNote::vitalSignFieldKeys(),
        );

        $oldSnapshot = [];
        foreach ($noteFieldKeys as $key) {
            $oldSnapshot[$key] = $existingNote?->{$key};
        }

        $newPayload = [
            'patient_concern' => $validated['patient_concern'] ?? $existingNote?->patient_concern,
            'appointment_remarks' => $validated['appointment_remarks'] ?? $validated['procedure_done'] ?? $existingNote?->appointment_remarks,
            'admin_notes' => $validated['admin_notes'] ?? $existingNote?->admin_notes,
            'clinical_notes' => $validated['clinical_notes'] ?? $validated['observations'] ?? $existingNote?->clinical_notes,
            'instructions' => $validated['instructions'] ?? $validated['recommendation'] ?? $existingNote?->instructions,
            'alerts' => $validated['alerts'] ?? $validated['follow_up_needed'] ?? $existingNote?->alerts,
            'vital_blood_pressure' => $flatVitals['vital_blood_pressure'],
            'vital_heart_rate' => $flatVitals['vital_heart_rate'],
            'vital_temperature' => $flatVitals['vital_temperature'],
            'vital_respiratory_rate' => $flatVitals['vital_respiratory_rate'],
            'vital_oxygen_saturation' => $flatVitals['vital_oxygen_saturation'],
            'vital_weight' => $flatVitals['vital_weight'],
            'vital_height' => $flatVitals['vital_height'],
            'vital_signs' => $phasedVitals,
        ];

        $newPayload['mobility'] = $request->has('mobility')
            ? (filled($validated['mobility'] ?? null) ? $validated['mobility'] : null)
            : $existingNote?->mobility;

        $bodyAnalyzerPath = $existingNote?->body_analyzer_image_path;
        if ($request->hasFile('body_analyzer_image')) {
            if (filled($bodyAnalyzerPath)) {
                Storage::disk('public')->delete($bodyAnalyzerPath);
            }
            $bodyAnalyzerPath = $request->file('body_analyzer_image')->store(
                'appointment-notes/body-analyzer/'.$appointment->id,
                'public'
            );
        }
        $newPayload['body_analyzer_image_path'] = $bodyAnalyzerPath;

        $bottleCitrusPath = $existingNote?->bottle_citrus_image_path;
        if ($request->hasFile('bottle_citrus_image')) {
            if (filled($bottleCitrusPath)) {
                Storage::disk('public')->delete($bottleCitrusPath);
            }
            $bottleCitrusPath = $request->file('bottle_citrus_image')->store(
                'appointment-notes/bottle-citrus/'.$appointment->id,
                'public'
            );
        }
        $newPayload['bottle_citrus_image_path'] = $bottleCitrusPath;

        $lemonBottlePath = $existingNote?->lemon_bottle_image_path;
        if ($request->hasFile('lemon_bottle_image')) {
            if (filled($lemonBottlePath)) {
                Storage::disk('public')->delete($lemonBottlePath);
            }
            $lemonBottlePath = $request->file('lemon_bottle_image')->store(
                'appointment-notes/lemon-bottle/'.$appointment->id,
                'public'
            );
        }
        $newPayload['lemon_bottle_image_path'] = $lemonBottlePath;

        $aqualyxPath = $existingNote?->aqualyx_image_path;
        if ($request->hasFile('aqualyx_image')) {
            if (filled($aqualyxPath)) {
                Storage::disk('public')->delete($aqualyxPath);
            }
            $aqualyxPath = $request->file('aqualyx_image')->store(
                'appointment-notes/aqualyx/'.$appointment->id,
                'public'
            );
        }
        $newPayload['aqualyx_image_path'] = $aqualyxPath;

        $dripPath = $existingNote?->drip_image_path;
        if ($request->hasFile('drip_image')) {
            if (filled($dripPath)) {
                Storage::disk('public')->delete($dripPath);
            }
            $dripPath = $request->file('drip_image')->store(
                'appointment-notes/drip/'.$appointment->id,
                'public'
            );
        }
        $newPayload['drip_image_path'] = $dripPath;

        $microNeedlingPath = $existingNote?->micro_needling_image_path;
        if ($request->hasFile('micro_needling_image')) {
            if (filled($microNeedlingPath)) {
                Storage::disk('public')->delete($microNeedlingPath);
            }
            $microNeedlingPath = $request->file('micro_needling_image')->store(
                'appointment-notes/micro-needling/'.$appointment->id,
                'public'
            );
        }
        $newPayload['micro_needling_image_path'] = $microNeedlingPath;

        $doctor = auth('clinical_staff')->user();
        $staffAuthor = AppointmentNote::authorPayloadFromUserName(
            'clinical_staff',
            $doctor?->name,
            $doctor?->id,
        );
        $newPayload['section_authors'] = AppointmentNote::mergeAuthorsOnFieldChanges(
            is_array($existingNote?->section_authors) ? $existingNote->section_authors : null,
            $oldSnapshot,
            $newPayload,
            $noteFieldKeys,
            $staffAuthor,
        );
        if ($hasVitalRequest) {
            $newPayload['section_authors']['vital_signs'] = $staffAuthor;
        }

        AppointmentNote::query()->updateOrCreate(
            ['appointment_id' => $appointment->id],
            $newPayload
        );

        $appointment->prescribedProducts()->sync($prescribeSync);

        return back()->with('success', 'Notes added successfully.');
    }

    public function updateAssessmentChecklist(Request $request, Appointment $appointment): RedirectResponse
    {
        $validated = $request->validate([
            'mobility' => ['required', 'string', Rule::in(['ambulatory', 'with_assistive', 'wheelchair'])],
            'iv_line_type' => ['nullable', 'string', Rule::in(['iv_cannula_g16', 'scalp_vein'])],
            'procedure_drip' => ['nullable', 'boolean'],
            'procedure_peptides' => ['nullable', 'boolean'],
            'informed_consent' => ['nullable', 'string', Rule::in(['yes', 'no'])],
            'drip_type' => ['nullable', 'string', 'max:255'],
            'drip_nod' => ['nullable', 'string', 'max:64'],
            'drip_remarks' => ['nullable', 'string', 'max:5000'],
            'peptides_type' => ['nullable', 'string', 'max:255'],
            'peptides_routes' => ['nullable', 'array'],
            'peptides_routes.*' => ['string', Rule::in(['sq', 'iv', 'mg', 'units'])],
            'peptides_md' => ['nullable', 'string', 'max:255'],
            'peptides_remarks' => ['nullable', 'string', 'max:5000'],
            'has_reaction' => ['nullable', 'string', Rule::in(['yes', 'no'])],
            'reaction_time' => ['nullable', 'string', 'max:64'],
            'reaction_referred' => ['nullable', 'string', 'max:255'],
            'reaction_notes' => ['nullable', 'string', 'max:5000'],
            'reaction_md' => ['nullable', 'string', 'max:255'],
        ]);

        $existingNote = AppointmentNote::query()->where('appointment_id', $appointment->id)->first();
        $fieldKeys = AppointmentNote::assessmentChecklistFieldKeys();
        $normalizeForAuthor = static function (string $key, mixed $value): ?string {
            if (in_array($key, ['procedure_drip', 'procedure_peptides'], true)) {
                return (bool) $value ? '1' : '0';
            }
            if ($key === 'peptides_routes') {
                $routes = is_array($value) ? array_values($value) : [];
                sort($routes);

                return $routes === [] ? null : json_encode($routes);
            }

            return AppointmentNote::normalizeNoteValue($value);
        };
        $oldSnapshot = [];
        foreach ($fieldKeys as $key) {
            $oldSnapshot[$key] = $normalizeForAuthor($key, $existingNote?->{$key});
        }

        $peptidesRoutes = collect($validated['peptides_routes'] ?? [])
            ->filter(fn ($route) => is_string($route) && $route !== '')
            ->unique()
            ->values()
            ->all();

        $newPayload = [
            'mobility' => $validated['mobility'],
            'iv_line_type' => filled($validated['iv_line_type'] ?? null) ? $validated['iv_line_type'] : null,
            'procedure_drip' => (bool) ($validated['procedure_drip'] ?? false),
            'procedure_peptides' => (bool) ($validated['procedure_peptides'] ?? false),
            'informed_consent' => filled($validated['informed_consent'] ?? null) ? $validated['informed_consent'] : null,
            'drip_type' => AppointmentNote::normalizeNoteValue($validated['drip_type'] ?? null),
            'drip_nod' => AppointmentNote::normalizeNoteValue($validated['drip_nod'] ?? null),
            'drip_remarks' => AppointmentNote::normalizeNoteValue($validated['drip_remarks'] ?? null),
            'peptides_type' => AppointmentNote::normalizeNoteValue($validated['peptides_type'] ?? null),
            'peptides_routes' => $peptidesRoutes === [] ? null : $peptidesRoutes,
            'peptides_md' => AppointmentNote::normalizeNoteValue($validated['peptides_md'] ?? null),
            'peptides_remarks' => AppointmentNote::normalizeNoteValue($validated['peptides_remarks'] ?? null),
            'has_reaction' => filled($validated['has_reaction'] ?? null) ? $validated['has_reaction'] : null,
            'reaction_time' => AppointmentNote::normalizeNoteValue($validated['reaction_time'] ?? null),
            'reaction_referred' => AppointmentNote::normalizeNoteValue($validated['reaction_referred'] ?? null),
            'reaction_notes' => AppointmentNote::normalizeNoteValue($validated['reaction_notes'] ?? null),
            'reaction_md' => AppointmentNote::normalizeNoteValue($validated['reaction_md'] ?? null),
        ];

        $doctor = auth('clinical_staff')->user();
        $newSnapshot = [];
        foreach ($fieldKeys as $key) {
            $newSnapshot[$key] = $normalizeForAuthor($key, $newPayload[$key]);
        }
        $sectionAuthors = AppointmentNote::mergeAuthorsOnFieldChanges(
            is_array($existingNote?->section_authors) ? $existingNote->section_authors : null,
            $oldSnapshot,
            $newSnapshot,
            $fieldKeys,
            AppointmentNote::authorPayloadFromUserName('clinical_staff', $doctor?->name, $doctor?->id),
        );

        AppointmentNote::query()->updateOrCreate(
            ['appointment_id' => $appointment->id],
            [
                ...$newPayload,
                'section_authors' => $sectionAuthors,
            ]
        );

        return redirect()
            ->route('clinical_staff.appointments.show', $appointment)
            ->with('success', __('Assessment checklist saved.'))
            ->withFragment('clinical-notes-assessment');
    }

    public function updateConsent(Request $request, Appointment $appointment): RedirectResponse
    {
        $validated = $request->validate([
            'consent_letter' => ['nullable', 'string', 'max:20000'],
            'consent_send_letter' => ['nullable', 'boolean'],
            'consent_signature_data' => ['nullable', 'string', 'max:500000'],
            'consent_signer_name' => ['nullable', 'string', 'max:255'],
        ]);

        $existingNote = AppointmentNote::query()->where('appointment_id', $appointment->id)->first();
        $shouldSendLetter = (bool) ($validated['consent_send_letter'] ?? false);
        $signatureData = AppointmentNote::normalizeNoteValue($validated['consent_signature_data'] ?? null);
        $letter = AppointmentNote::normalizeNoteValue($validated['consent_letter'] ?? null);
        $signerName = AppointmentNote::normalizeNoteValue($validated['consent_signer_name'] ?? null);

        if ($shouldSendLetter && $letter === null) {
            return back()->withErrors([
                'consent_letter' => __('Consent letter content is required before sending.'),
            ]);
        }

        if ($signatureData !== null && ! str_starts_with($signatureData, 'data:image/png;base64,')) {
            return back()->withErrors([
                'consent_signature_data' => __('Signature must be a PNG data URL.'),
            ]);
        }

        $newPayload = [
            'consent_letter' => $letter,
            'consent_sent_at' => $shouldSendLetter
                ? ($existingNote?->consent_sent_at ?? now())
                : $existingNote?->consent_sent_at,
            'consent_signature_data' => $signatureData,
            'consent_signed_at' => $signatureData !== null ? now() : null,
            'consent_signer_name' => $signatureData !== null ? $signerName : null,
        ];

        AppointmentNote::query()->updateOrCreate(
            ['appointment_id' => $appointment->id],
            $newPayload
        );

        return redirect()
            ->route('clinical_staff.appointments.show', $appointment)
            ->with('success', __('Consent saved.'))
            ->withFragment('clinical-notes-assessment');
    }

    public function reschedule(Request $request, Appointment $appointment): RedirectResponse
    {
        $validated = $request->validate([
            'appointment_date' => ['required', 'date', new BookableAppointmentDate],
            'appointment_time' => ['required', 'date_format:H:i'],
        ]);

        $appointment->update([
            'appointment_date' => $validated['appointment_date'],
            'appointment_time' => $validated['appointment_time'],
            'status' => 'rescheduled',
            'reminder_sent_at' => null,
        ]);

        $appointment->load(['patient:id,name,email', 'clinicalStaff:id,name', 'service:id,name']);
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
