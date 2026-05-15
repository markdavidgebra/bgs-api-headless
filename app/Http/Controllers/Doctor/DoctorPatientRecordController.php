<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentNote;
use App\Models\AppointmentPayment;
use App\Models\Patient;
use App\Models\PatientSubscription;
use App\Models\Payment;
use App\Models\TreatmentPackageUsageHistory;
use App\Models\TreatmentPatientPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DoctorPatientRecordController extends Controller
{
    public function index(Request $request): View
    {
        $doctorId = auth('doctor')->id();
        $search = trim($request->string('search')->toString());

        $patientsPaginator = Patient::query()
            ->when($search !== '', function ($query) use ($search) {
                $term = '%'.addcslashes($search, '%_\\').'%';
                $query->where(function ($inner) use ($term) {
                    $inner->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('phone', 'like', $term);
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $patientIds = $patientsPaginator->getCollection()->pluck('id')->filter()->unique()->values();
        $today = now()->toDateString();

        $visitCounts = collect();
        $lastAppointments = collect();

        if ($patientIds->isNotEmpty()) {
            $visitCounts = Appointment::query()
                ->where('doctor_id', $doctorId)
                ->whereIn('patient_id', $patientIds)
                ->selectRaw('patient_id, COUNT(*) as visit_count')
                ->groupBy('patient_id')
                ->pluck('visit_count', 'patient_id');

            $lastAppointments = Appointment::query()
                ->where('doctor_id', $doctorId)
                ->whereIn('patient_id', $patientIds)
                ->orderByDesc('appointment_date')
                ->orderByDesc('appointment_time')
                ->get()
                ->unique('patient_id')
                ->keyBy('patient_id');
        }

        $activeMembershipByPatient = $patientIds->isEmpty()
            ? collect()
            : PatientSubscription::query()
                ->with('membershipPlan:id,name')
                ->whereIn('patient_id', $patientIds)
                ->where('status', 'active')
                ->where(function ($query) use ($today) {
                    $query->whereNull('end_date')
                        ->orWhereDate('end_date', '>=', $today);
                })
                ->orderByDesc('start_date')
                ->get()
                ->groupBy('patient_id')
                ->map(fn ($group) => $group->first());

        $activePackageByPatient = $patientIds->isEmpty()
            ? collect()
            : TreatmentPatientPackage::query()
                ->with('treatmentPackage:id,name')
                ->whereIn('patient_id', $patientIds)
                ->where('status', 'active')
                ->where(function ($query) use ($today) {
                    $query->whereNull('end_date')
                        ->orWhereDate('end_date', '>=', $today);
                })
                ->orderByDesc('start_date')
                ->get()
                ->groupBy('patient_id')
                ->map(fn ($group) => $group->first());

        $records = $patientsPaginator->through(function (Patient $patient) use (
            $visitCounts,
            $lastAppointments,
            $activeMembershipByPatient,
            $activePackageByPatient
        ) {
            $patientId = $patient->id;
            $activeMembership = $activeMembershipByPatient->get($patientId);
            $activePackage = $activePackageByPatient->get($patientId);

            if ($activeMembership?->membershipPlan?->name) {
                $activePlan = 'Membership: '.$activeMembership->membershipPlan->name;
            } elseif ($activePackage?->treatmentPackage?->name) {
                $activePlan = 'Package: '.$activePackage->treatmentPackage->name;
            } else {
                $activePlan = 'No active plan';
            }

            return (object) [
                'patient' => $patient,
                'total_appointments' => (int) ($visitCounts[$patientId] ?? 0),
                'last_appointment' => $lastAppointments->get($patientId),
                'active_plan' => $activePlan,
            ];
        });

        return view('doctor.patient-records.index', [
            'records' => $records,
            'search' => $search,
        ]);
    }

    public function show(Patient $patient): View
    {
        $doctorId = auth('doctor')->id();

        $myAppointments = Appointment::query()
            ->with(['service:id,name', 'doctor:id,name', 'note'])
            ->where('doctor_id', $doctorId)
            ->where('patient_id', $patient->id)
            ->orderByDesc('appointment_date')
            ->orderByDesc('appointment_time')
            ->get();

        $appointments = Appointment::query()
            ->with(['service:id,name', 'doctor:id,name', 'note'])
            ->where('patient_id', $patient->id)
            ->orderByDesc('appointment_date')
            ->orderByDesc('appointment_time')
            ->get();

        $lastVisit = $myAppointments->first();
        $totalVisits = $myAppointments->count();

        $latestNoteAppointment = $appointments->first(
            fn ($appt) => AppointmentNote::hasClinicalContent($appt->note)
        );
        $latestNote = $latestNoteAppointment?->note;
        $latestAlerts = $appointments
            ->map(fn ($appt) => $appt->note?->alerts)
            ->map(fn ($v) => is_string($v) ? trim($v) : '')
            ->filter(fn ($v) => $v !== '')
            ->first();

        $upcomingAppointments = $appointments
            ->filter(fn ($appt) => ($appt->appointment_date?->toDateString() ?? '') >= now()->toDateString())
            ->values();
        $pastAppointments = $appointments
            ->filter(fn ($appt) => ($appt->appointment_date?->toDateString() ?? '') < now()->toDateString())
            ->values();

        $subscriptions = PatientSubscription::query()
            ->with('membershipPlan:id,name')
            ->where('patient_id', $patient->id)
            ->orderByDesc('start_date')
            ->get();

        $packages = TreatmentPatientPackage::query()
            ->with(['treatmentPackage.services'])
            ->where('patient_id', $patient->id)
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->get();

        $patientPackageProgress = $packages->map(function (TreatmentPatientPackage $pkg) {
            $pkg->loadMissing('treatmentPackage.services');
            $completedByService = TreatmentPackageUsageHistory::query()
                ->where('patient_package_id', $pkg->id)
                ->where('status', 'completed')
                ->selectRaw('service_id, COUNT(*) as total_done')
                ->groupBy('service_id')
                ->pluck('total_done', 'service_id')
                ->map(static fn ($v): int => (int) $v)
                ->all();

            $rows = collect();
            foreach ($pkg->treatmentPackage?->services ?? [] as $service) {
                $serviceId = (int) $service->id;
                $requiredSessions = max(1, (int) ($service->pivot->sessions ?? 1));
                $doneSessions = min($requiredSessions, (int) ($completedByService[$serviceId] ?? 0));

                for ($sessionNo = 1; $sessionNo <= $requiredSessions; $sessionNo++) {
                    $rows->push([
                        'key' => $serviceId.':'.$sessionNo,
                        'service_id' => $serviceId,
                        'service_name' => (string) $service->name,
                        'session_no' => $sessionNo,
                        'required_sessions' => $requiredSessions,
                        'is_done' => $sessionNo <= $doneSessions,
                    ]);
                }
            }

            return (object) [
                'package' => $pkg,
                'rows' => $rows,
                'has_breakdown' => $rows->isNotEmpty(),
            ];
        })->values();

        $payments = Payment::query()
            ->where('patient_id', $patient->id)
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        $appointmentPayments = AppointmentPayment::query()
            ->whereHas('appointment', function ($query) use ($doctorId, $patient) {
                $query->where('doctor_id', $doctorId)
                    ->where('patient_id', $patient->id);
            })
            ->with('appointment:id,appointment_no,doctor_id,patient_id')
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        $notesHistory = $appointments
            ->filter(fn ($appt) => AppointmentNote::hasClinicalContent($appt->note))
            ->map(function ($appt) {
                return (object) [
                    'appointment' => $appt,
                    'note' => $appt->note,
                ];
            })
            ->values();

        $bodyAnalyzerImages = $appointments
            ->filter(function ($appt) {
                $url = $appt->note?->bodyAnalyzerImageUrl();

                return filled($url);
            })
            ->sortBy(function ($appt) {
                $date = $appt->appointment_date?->toDateString() ?? '1970-01-01';
                $raw = $appt->appointment_time;
                $timeSort = '00:00:00';
                if ($raw !== null && $raw !== '') {
                    $timeSort = is_string($raw) && strlen($raw) >= 8
                        ? substr($raw, 0, 8)
                        : \Illuminate\Support\Carbon::parse($raw)->format('H:i:s');
                }

                return $date.' '.$timeSort;
            })
            ->values()
            ->map(function ($appt) {
                return (object) [
                    'appointment' => $appt,
                    'url' => $appt->note->bodyAnalyzerImageUrl(),
                ];
            });

        $bottleCitrusImages = $appointments
            ->filter(function ($appt) {
                $url = $appt->note?->bottleCitrusImageUrl();

                return filled($url);
            })
            ->sortBy(function ($appt) {
                $date = $appt->appointment_date?->toDateString() ?? '1970-01-01';
                $raw = $appt->appointment_time;
                $timeSort = '00:00:00';
                if ($raw !== null && $raw !== '') {
                    $timeSort = is_string($raw) && strlen($raw) >= 8
                        ? substr($raw, 0, 8)
                        : \Illuminate\Support\Carbon::parse($raw)->format('H:i:s');
                }

                return $date.' '.$timeSort;
            })
            ->values()
            ->map(function ($appt) {
                return (object) [
                    'appointment' => $appt,
                    'url' => $appt->note->bottleCitrusImageUrl(),
                ];
            });

        $lemonBottleImages = $appointments
            ->filter(function ($appt) {
                $url = $appt->note?->lemonBottleImageUrl();

                return filled($url);
            })
            ->sortBy(function ($appt) {
                $date = $appt->appointment_date?->toDateString() ?? '1970-01-01';
                $raw = $appt->appointment_time;
                $timeSort = '00:00:00';
                if ($raw !== null && $raw !== '') {
                    $timeSort = is_string($raw) && strlen($raw) >= 8
                        ? substr($raw, 0, 8)
                        : \Illuminate\Support\Carbon::parse($raw)->format('H:i:s');
                }

                return $date.' '.$timeSort;
            })
            ->values()
            ->map(function ($appt) {
                return (object) [
                    'appointment' => $appt,
                    'url' => $appt->note->lemonBottleImageUrl(),
                ];
            });

        $aqualyxImages = $appointments
            ->filter(function ($appt) {
                $url = $appt->note?->aqualyxImageUrl();

                return filled($url);
            })
            ->sortBy(function ($appt) {
                $date = $appt->appointment_date?->toDateString() ?? '1970-01-01';
                $raw = $appt->appointment_time;
                $timeSort = '00:00:00';
                if ($raw !== null && $raw !== '') {
                    $timeSort = is_string($raw) && strlen($raw) >= 8
                        ? substr($raw, 0, 8)
                        : \Illuminate\Support\Carbon::parse($raw)->format('H:i:s');
                }

                return $date.' '.$timeSort;
            })
            ->values()
            ->map(function ($appt) {
                return (object) [
                    'appointment' => $appt,
                    'url' => $appt->note->aqualyxImageUrl(),
                ];
            });

        $dripImages = $appointments
            ->filter(function ($appt) {
                $url = $appt->note?->dripImageUrl();

                return filled($url);
            })
            ->sortBy(function ($appt) {
                $date = $appt->appointment_date?->toDateString() ?? '1970-01-01';
                $raw = $appt->appointment_time;
                $timeSort = '00:00:00';
                if ($raw !== null && $raw !== '') {
                    $timeSort = is_string($raw) && strlen($raw) >= 8
                        ? substr($raw, 0, 8)
                        : \Illuminate\Support\Carbon::parse($raw)->format('H:i:s');
                }

                return $date.' '.$timeSort;
            })
            ->values()
            ->map(function ($appt) {
                return (object) [
                    'appointment' => $appt,
                    'url' => $appt->note->dripImageUrl(),
                ];
            });

        $microNeedlingImages = $appointments
            ->filter(function ($appt) {
                $url = $appt->note?->microNeedlingImageUrl();

                return filled($url);
            })
            ->sortBy(function ($appt) {
                $date = $appt->appointment_date?->toDateString() ?? '1970-01-01';
                $raw = $appt->appointment_time;
                $timeSort = '00:00:00';
                if ($raw !== null && $raw !== '') {
                    $timeSort = is_string($raw) && strlen($raw) >= 8
                        ? substr($raw, 0, 8)
                        : \Illuminate\Support\Carbon::parse($raw)->format('H:i:s');
                }

                return $date.' '.$timeSort;
            })
            ->values()
            ->map(function ($appt) {
                return (object) [
                    'appointment' => $appt,
                    'url' => $appt->note->microNeedlingImageUrl(),
                ];
            });

        $assessmentHistory = $appointments->map(function (Appointment $appt) use ($doctorId) {
            return (object) [
                'appointment' => $appt,
                'mobility_label' => $appt->note?->mobilityLabel(),
                'can_edit' => (int) $appt->doctor_id === (int) $doctorId,
            ];
        })->values();

        return view('doctor.appointments.patient-record.show', compact(
            'patient',
            'appointments',
            'myAppointments',
            'lastVisit',
            'totalVisits',
            'latestNote',
            'latestNoteAppointment',
            'latestAlerts',
            'upcomingAppointments',
            'pastAppointments',
            'subscriptions',
            'patientPackageProgress',
            'payments',
            'appointmentPayments',
            'notesHistory',
            'bodyAnalyzerImages',
            'bottleCitrusImages',
            'lemonBottleImages',
            'aqualyxImages',
            'dripImages',
            'microNeedlingImages',
            'assessmentHistory',
        ));
    }

    public function storeNote(Request $request, Patient $patient): RedirectResponse
    {
        $doctorId = auth('doctor')->id();

        $validated = $request->validate([
            'appointment_id' => ['required', 'integer'],
            'observation' => ['nullable', 'string', 'max:2000'],
            'procedure_done' => ['nullable', 'string', 'max:2000'],
            'recommendation' => ['nullable', 'string', 'max:2000'],
            'follow_up' => ['nullable', 'string', 'max:1000'],
        ]);

        $appointment = Appointment::query()
            ->where('id', $validated['appointment_id'])
            ->where('doctor_id', $doctorId)
            ->where('patient_id', $patient->id)
            ->firstOrFail();

        $hasAny = collect([
            $validated['observation'] ?? null,
            $validated['procedure_done'] ?? null,
            $validated['recommendation'] ?? null,
            $validated['follow_up'] ?? null,
        ])->contains(fn ($value) => filled($value));

        if (! $hasAny) {
            return back()->withErrors(['observation' => 'Please fill at least one treatment note field.'])->withInput();
        }

        $existing = AppointmentNote::query()->where('appointment_id', $appointment->id)->first();

        $fieldKeys = ['doctor_notes', 'appointment_remarks', 'instructions', 'alerts'];
        $oldSnapshot = [];
        foreach ($fieldKeys as $key) {
            $oldSnapshot[$key] = $existing?->{$key};
        }

        $newPayload = [
            'doctor_notes' => $validated['observation'] ?? null,
            'appointment_remarks' => $validated['procedure_done'] ?? null,
            'instructions' => $validated['recommendation'] ?? null,
            'alerts' => $validated['follow_up'] ?? null,
        ];

        $doctor = auth('doctor')->user();
        $newPayload['section_authors'] = AppointmentNote::mergeAuthorsOnFieldChanges(
            is_array($existing?->section_authors) ? $existing->section_authors : null,
            $oldSnapshot,
            $newPayload,
            $fieldKeys,
            AppointmentNote::authorPayloadFromUserName('doctor', $doctor?->name),
        );

        AppointmentNote::query()->updateOrCreate(
            ['appointment_id' => $appointment->id],
            $newPayload
        );

        return redirect()
            ->route('doctor.patient-records.show', $patient)
            ->with('success', 'Treatment note saved successfully.');
    }

    public function updatePatientPackageSessions(Request $request, Patient $patient, TreatmentPatientPackage $patientPackage): RedirectResponse
    {
        if ((int) $patientPackage->patient_id !== (int) $patient->id) {
            abort(404);
        }

        $patientPackage->loadMissing('treatmentPackage.services');
        $requiredByService = [];
        foreach ($patientPackage->treatmentPackage?->services ?? [] as $service) {
            $requiredByService[(int) $service->id] = max(1, (int) ($service->pivot->sessions ?? 1));
        }

        if ($requiredByService !== []) {
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

            DB::transaction(function () use ($patient, $patientPackage, $requiredByService, $desiredDoneByService, $newUsedSessions, $newRemainingSessions, $newStatus, $totalSessions): void {
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
                                'patient_id' => $patient->id,
                                'service_id' => $serviceId,
                                'used_on' => now()->toDateString(),
                                'session_change' => -1,
                                'status' => 'completed',
                                'notes' => 'Patient record — package progress',
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

            return $this->redirectAfterPatientPackageSessionsSave($patient);
        }

        $validated = $request->validate([
            'used_sessions' => ['required', 'integer', 'min:0'],
        ]);

        $total = max(0, (int) $patientPackage->total_sessions);
        $used = min($total, max(0, (int) $validated['used_sessions']));
        $remaining = max(0, $total - $used);

        $status = $total > 0 && $used >= $total
            ? 'completed'
            : ($used > 0 ? 'ongoing' : 'pending');

        $patientPackage->update([
            'used_sessions' => $used,
            'remaining_sessions' => $remaining,
            'status' => $status,
        ]);

        return $this->redirectAfterPatientPackageSessionsSave($patient);
    }

    private function redirectAfterPatientPackageSessionsSave(Patient $patient): RedirectResponse
    {
        return redirect()
            ->route('doctor.patient-records.show', $patient)
            ->with('success', 'Package session progress updated.')
            ->withFragment('tab-packages');
    }
}
