<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\DoctorPortalResponses;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Doctor\DoctorAppointmentController;
use App\Http\Controllers\Doctor\DoctorAvailabilityController;
use App\Http\Controllers\Doctor\DoctorNotificationController;
use App\Http\Controllers\Doctor\DoctorPatientRecordController;
use App\Http\Controllers\Doctor\DoctorProductInventoryController;
use App\Http\Controllers\Doctor\DoctorProfileController;
use App\Http\Controllers\Doctor\DoctorServiceController;
use App\Models\Appointment;
use App\Models\AppointmentNote;
use App\Models\Doctor;
use App\Models\DoctorBlockedDate;
use App\Models\DoctorNotification;
use App\Models\DoctorWeeklySchedule;
use App\Models\Patient;
use App\Models\PatientSubscription;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Service;
use App\Models\TreatmentPackageUsageHistory;
use App\Models\TreatmentPatientPackage;
use App\Support\AppointmentBookingRules;
use Illuminate\Contracts\Support\MessageBag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DoctorPortalController extends Controller
{
    use DoctorPortalResponses;

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

        if (! Auth::guard('doctor')->attempt($credentials, true)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        $request->session()->regenerate();

        $doctor = Auth::guard('doctor')->user();
        if (! $doctor || strtolower((string) ($doctor->status ?? 'pending')) !== 'active') {
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
        $doctor = auth('doctor')->user();
        $today = now()->toDateString();

        $baseQuery = Appointment::query()
            ->with(['patient:id,name', 'service:id,name', 'note', 'doctor:id,name']);

        $todayAppointmentsCount = (clone $baseQuery)->whereDate('appointment_date', $today)->count();
        $upcomingAppointmentsCount = (clone $baseQuery)
            ->whereDate('appointment_date', '>', $today)
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->count();
        $patientsTodayCount = (clone $baseQuery)
            ->whereDate('appointment_date', $today)
            ->distinct('patient_id')
            ->count('patient_id');
        $pendingNotesCount = Appointment::query()
            ->whereDate('appointment_date', '<=', $today)
            ->where(function ($q) {
                $q->whereDoesntHave('note')
                    ->orWhereHas('note', function ($nq) {
                        $nq->whereNull('doctor_notes')->orWhere('doctor_notes', '');
                    });
            })
            ->count();

        $scheduleToday = (clone $baseQuery)
            ->whereDate('appointment_date', $today)
            ->orderBy('appointment_time')
            ->orderBy('id')
            ->get();

        $upcomingAppointments = (clone $baseQuery)
            ->whereDate('appointment_date', '>=', $today)
            ->whereNotIn('status', ['cancelled'])
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->limit(5)
            ->get();

        $notificationsUnreadCount = DoctorNotification::query()
            ->forDoctor((int) $doctor->id)
            ->unread()
            ->count();

        return response()->json([
            'stats' => [
                'today_appointments' => $todayAppointmentsCount,
                'upcoming_appointments' => $upcomingAppointmentsCount,
                'patients_today' => $patientsTodayCount,
                'pending_notes' => $pendingNotesCount,
                'notifications_unread' => $notificationsUnreadCount,
            ],
            'schedule_today' => $scheduleToday->map(fn (Appointment $a) => $this->appointmentPayload($a))->values(),
            'upcoming_appointments' => $upcomingAppointments->map(fn (Appointment $a) => $this->appointmentPayload($a))->values(),
        ]);
    }

    public function appointmentsIndex(Request $request): JsonResponse
    {
        $today = now()->toDateString();
        $dateFilter = $request->string('date_filter')->toString() ?: 'today';
        $status = $request->string('status')->toString();
        $search = trim($request->string('search')->toString());
        $customDate = $request->string('custom_date')->toString();
        $viewMode = $request->string('view')->toString() ?: 'list';
        $limit = max(1, min((int) $request->integer('limit', 10), 50));

        $baseQuery = Appointment::query()
            ->with(['patient:id,name', 'service:id,name', 'note', 'doctor:id,name']);

        if ($status) {
            $baseQuery->where('status', $status);
        }

        if ($search !== '') {
            $baseQuery->whereHas('patient', fn ($q) => $q->where('name', 'like', '%'.$search.'%'));
        }

        $appointmentsQuery = clone $baseQuery;

        if ($dateFilter === 'today') {
            $appointmentsQuery->whereDate('appointment_date', $today);
        } elseif ($dateFilter === 'tomorrow') {
            $appointmentsQuery->whereDate('appointment_date', now()->addDay()->toDateString());
        } elseif ($dateFilter === 'custom' && $customDate) {
            $appointmentsQuery->whereDate('appointment_date', $customDate);
        }

        $paginator = $appointmentsQuery
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->paginate($limit)
            ->withQueryString();

        $calendar = null;
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

            $calendar = [
                'month' => $monthCursor->format('Y-m'),
                'prev_month' => $monthCursor->copy()->subMonth()->format('Y-m'),
                'next_month' => $monthCursor->copy()->addMonth()->format('Y-m'),
                'by_date' => $calendarAppointments
                    ->groupBy(fn (Appointment $a) => $a->appointment_date?->toDateString() ?? '')
                    ->map(fn ($group) => $group->map(fn (Appointment $a) => $this->appointmentPayload($a))->values())
                    ->all(),
            ];
        }

        return response()->json([
            'appointments' => $paginator->getCollection()->map(fn (Appointment $a) => $this->appointmentPayload($a))->values(),
            'meta' => $this->paginationMeta($paginator),
            'filters' => [
                'date_filter' => $dateFilter,
                'status' => $status,
                'search' => $search,
                'custom_date' => $customDate,
                'view' => $viewMode,
            ],
            'status_options' => ['pending', 'confirmed', 'completed', 'cancelled'],
            'calendar' => $calendar,
        ]);
    }

    public function appointmentShow(Appointment $appointment): JsonResponse
    {
        $appointment->load(['patient', 'service', 'note', 'timelines', 'prescribedProducts', 'doctor:id,name']);

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

        $serviceChecklist = $this->buildServiceChecklist($patientPackage);

        return response()->json([
            'appointment' => $this->appointmentPayload($appointment, detailed: true),
            'patient_packages' => $patientPackages->map(fn (TreatmentPatientPackage $pkg) => [
                'id' => $pkg->id,
                'name' => $pkg->treatmentPackage?->name,
                'status' => $pkg->status,
                'start_date' => $pkg->start_date?->toDateString(),
                'end_date' => $pkg->end_date?->toDateString(),
            ])->values(),
            'active_patient_package_id' => $patientPackage?->id,
            'service_checklist' => $serviceChecklist,
        ]);
    }

    public function appointmentNotesForm(Appointment $appointment): JsonResponse
    {
        if (in_array($appointment->status, ['pending', 'rescheduled'], true)) {
            return response()->json([
                'message' => __('Please approve this appointment before adding a note.'),
            ], 422);
        }

        $appointment->load(['patient', 'service', 'note', 'prescribedProducts', 'doctor:id,name']);

        $products = Product::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get([
                'id', 'name', 'sku', 'unit', 'stock_quantity',
                'minimum_stock_alert', 'selling_price', 'discount_price',
            ]);

        return response()->json([
            'appointment' => $this->appointmentPayload($appointment, detailed: true),
            'products' => $products->map(fn (Product $p) => $this->productPayload($p))->values(),
        ]);
    }

    public function appointmentApprove(Appointment $appointment): JsonResponse
    {
        return $this->fromWeb(fn () => app(DoctorAppointmentController::class)->approve($appointment));
    }

    public function appointmentStartSession(Request $request, Appointment $appointment): JsonResponse
    {
        return $this->fromWeb(fn () => app(DoctorAppointmentController::class)->startSession($request, $appointment));
    }

    public function appointmentComplete(Appointment $appointment): JsonResponse
    {
        return $this->fromWeb(fn () => app(DoctorAppointmentController::class)->markCompleted($appointment));
    }

    public function appointmentSessionDone(Request $request, Appointment $appointment): JsonResponse
    {
        return $this->fromWeb(fn () => app(DoctorAppointmentController::class)->updateSessionDone($request, $appointment));
    }

    public function appointmentNoShow(Appointment $appointment): JsonResponse
    {
        return $this->fromWeb(fn () => app(DoctorAppointmentController::class)->markNoShow($appointment));
    }

    public function appointmentReschedule(Request $request, Appointment $appointment): JsonResponse
    {
        return $this->fromWeb(fn () => app(DoctorAppointmentController::class)->reschedule($request, $appointment));
    }

    public function appointmentTreatmentProgress(Request $request, Appointment $appointment): JsonResponse
    {
        return $this->fromWeb(fn () => app(DoctorAppointmentController::class)->updateTreatmentProgress($request, $appointment));
    }

    public function appointmentNotes(Request $request, Appointment $appointment): JsonResponse
    {
        return $this->fromWeb(fn () => app(DoctorAppointmentController::class)->addNotes($request, $appointment));
    }

    public function appointmentAssessment(Request $request, Appointment $appointment): JsonResponse
    {
        return $this->fromWeb(fn () => app(DoctorAppointmentController::class)->updateAssessmentChecklist($request, $appointment));
    }

    public function clinicalImage(Request $request, Appointment $appointment, string $type): BinaryFileResponse
    {
        $appointment->loadMissing('note');

        $path = AppointmentNote::clinicalImagePathForType($appointment->note, $type);
        if ($path === null) {
            abort(404);
        }

        $absolutePath = Storage::disk('public')->path($path);

        return response()->file($absolutePath, [
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    public function patientRecordsIndex(Request $request): JsonResponse
    {
        $doctorId = auth('doctor')->id();
        $search = trim($request->string('search')->toString());
        $limit = max(1, min((int) $request->integer('limit', 20), 50));

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
            ->paginate($limit)
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
                    $query->whereNull('end_date')->orWhereDate('end_date', '>=', $today);
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
                    $query->whereNull('end_date')->orWhereDate('end_date', '>=', $today);
                })
                ->orderByDesc('start_date')
                ->get()
                ->groupBy('patient_id')
                ->map(fn ($group) => $group->first());

        $records = $patientsPaginator->getCollection()->map(function (Patient $patient) use (
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

            $last = $lastAppointments->get($patientId);

            return [
                'patient' => $this->patientSummaryPayload($patient),
                'total_appointments' => (int) ($visitCounts[$patientId] ?? 0),
                'last_appointment' => $last ? $this->appointmentPayload($last) : null,
                'active_plan' => $activePlan,
            ];
        })->values();

        return response()->json([
            'records' => $records,
            'meta' => $this->paginationMeta($patientsPaginator),
            'search' => $search,
        ]);
    }

    public function patientRecordShow(Patient $patient): JsonResponse
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

        $today = now()->toDateString();
        $upcoming = $appointments->filter(fn ($a) => ($a->appointment_date?->toDateString() ?? '') >= $today)->values();
        $past = $appointments->filter(fn ($a) => ($a->appointment_date?->toDateString() ?? '') < $today)->values();

        $packages = TreatmentPatientPackage::query()
            ->with(['treatmentPackage.services'])
            ->where('patient_id', $patient->id)
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->get();

        $packageProgress = $packages->map(function (TreatmentPatientPackage $pkg) {
            return [
                'package' => [
                    'id' => $pkg->id,
                    'name' => $pkg->treatmentPackage?->name,
                    'status' => $pkg->status,
                    'start_date' => $pkg->start_date?->toDateString(),
                    'end_date' => $pkg->end_date?->toDateString(),
                ],
                'checklist' => $this->buildServiceChecklist($pkg),
            ];
        })->values();

        $subscriptions = PatientSubscription::query()
            ->with('membershipPlan:id,name')
            ->where('patient_id', $patient->id)
            ->orderByDesc('start_date')
            ->get()
            ->map(fn (PatientSubscription $sub) => [
                'id' => $sub->id,
                'status' => $sub->status,
                'plan' => $sub->membershipPlan?->name,
                'start_date' => $sub->start_date?->toDateString(),
                'end_date' => $sub->end_date?->toDateString(),
            ])
            ->values();

        $payments = Payment::query()
            ->with([
                'referenceProduct:id,name',
                'referencePackage:id,name',
                'referenceMembership:id,name',
                'referenceService:id,name',
            ])
            ->where('patient_id', $patient->id)
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->limit(20)
            ->get()
            ->map(fn (Payment $p) => [
                'id' => $p->id,
                'amount' => (float) $p->amount,
                'payment_date' => $p->payment_date?->toDateString(),
                'method' => $p->payment_method,
                'reference' => $p->reference_name,
            ])
            ->values();

        $notesHistory = $appointments
            ->filter(fn ($appt) => AppointmentNote::hasClinicalContent($appt->note))
            ->map(fn ($appt) => [
                'appointment' => $this->appointmentPayload($appt),
                'note' => $appt->note ? $this->notePayload($appt->note) : null,
            ])
            ->values();

        return response()->json([
            'patient' => $this->patientSummaryPayload($patient),
            'stats' => [
                'total_visits_with_me' => $myAppointments->count(),
                'last_visit' => $myAppointments->first() ? $this->appointmentPayload($myAppointments->first()) : null,
            ],
            'my_appointments' => $myAppointments->map(fn (Appointment $a) => $this->appointmentPayload($a))->values(),
            'all_appointments' => [
                'upcoming' => $upcoming->map(fn (Appointment $a) => $this->appointmentPayload($a))->values(),
                'past' => $past->map(fn (Appointment $a) => $this->appointmentPayload($a))->values(),
            ],
            'subscriptions' => $subscriptions,
            'packages' => $packageProgress,
            'payments' => $payments,
            'notes_history' => $notesHistory,
        ]);
    }

    public function patientRecordStoreNote(Request $request, Patient $patient): JsonResponse
    {
        return $this->fromWeb(fn () => app(DoctorPatientRecordController::class)->storeNote($request, $patient));
    }

    public function patientRecordUpdatePackageSessions(
        Request $request,
        Patient $patient,
        TreatmentPatientPackage $patientPackage,
    ): JsonResponse {
        return $this->fromWeb(fn () => app(DoctorPatientRecordController::class)
            ->updatePatientPackageSessions($request, $patient, $patientPackage));
    }

    public function treatmentNotesIndex(Request $request): JsonResponse
    {
        $doctorId = auth('doctor')->id();
        $search = trim($request->string('search')->toString());
        $date = $request->string('date')->toString();
        $limit = max(1, min((int) $request->integer('limit', 12), 50));

        $query = Appointment::query()
            ->with(['patient:id,name,email', 'service:id,name', 'note'])
            ->where('doctor_id', $doctorId)
            ->whereHas('note')
            ->orderByDesc('appointment_date')
            ->orderByDesc('appointment_time');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('appointment_no', 'like', '%'.$search.'%')
                    ->orWhereHas('patient', function ($pq) use ($search) {
                        $pq->where('name', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('service', fn ($sq) => $sq->where('name', 'like', '%'.$search.'%'));
            });
        }

        if ($date !== '') {
            $query->whereDate('appointment_date', $date);
        }

        $paginator = $query->paginate($limit)->withQueryString();

        return response()->json([
            'notes' => $paginator->getCollection()->map(function (Appointment $appt) {
                return [
                    'appointment' => $this->appointmentPayload($appt),
                    'note' => $appt->note ? $this->notePayload($appt->note) : null,
                ];
            })->values(),
            'meta' => $this->paginationMeta($paginator),
            'search' => $search,
            'date' => $date,
        ]);
    }

    public function treatmentNoteShow(Appointment $appointment): JsonResponse
    {
        abort_unless((int) $appointment->doctor_id === (int) auth('doctor')->id(), 403);
        $appointment->load(['patient:id,name,email,phone', 'service:id,name', 'note', 'prescribedProducts']);
        abort_unless($appointment->note !== null, 404);

        return response()->json([
            'appointment' => $this->appointmentPayload($appointment, detailed: true),
            'note' => $this->notePayload($appointment->note),
        ]);
    }

    public function notificationsIndex(Request $request): JsonResponse
    {
        $doctor = auth('doctor')->user();
        $tab = in_array($request->query('tab'), ['all', 'unread', 'appointments', 'follow_ups', 'reminders'], true)
            ? $request->query('tab')
            : 'all';
        $limit = max(1, min((int) $request->integer('limit', 15), 50));

        $paginator = DoctorNotification::query()
            ->forDoctor((int) $doctor->id)
            ->with(['appointment:id,appointment_no,patient_id,service_id,appointment_date,appointment_time', 'patient:id,name'])
            ->tab($tab)
            ->orderByDesc('created_at')
            ->paginate($limit)
            ->withQueryString();

        $unreadCount = DoctorNotification::query()
            ->forDoctor((int) $doctor->id)
            ->unread()
            ->count();

        return response()->json([
            'notifications' => $paginator->getCollection()->map(fn (DoctorNotification $n) => $this->notificationPayload($n))->values(),
            'meta' => $this->paginationMeta($paginator),
            'tab' => $tab,
            'unread_count' => $unreadCount,
        ]);
    }

    public function notificationShow(DoctorNotification $notification): JsonResponse
    {
        abort_unless($notification->doctor_id === (int) auth('doctor')->id(), 403);

        if ($notification->read_at === null) {
            $notification->forceFill(['read_at' => now()])->save();
        }

        $notification->load(['appointment.patient', 'appointment.service', 'patient']);

        return response()->json([
            'notification' => $this->notificationPayload($notification),
        ]);
    }

    public function notificationMarkRead(DoctorNotification $notification): JsonResponse
    {
        return $this->fromWeb(fn () => app(DoctorNotificationController::class)->markRead($notification));
    }

    public function notificationsMarkAllRead(Request $request): JsonResponse
    {
        return $this->fromWeb(fn () => app(DoctorNotificationController::class)->markAllRead($request));
    }

    public function notificationsClearRead(Request $request): JsonResponse
    {
        return $this->fromWeb(fn () => app(DoctorNotificationController::class)->clearRead($request));
    }

    public function notificationDestroy(DoctorNotification $notification): JsonResponse
    {
        return $this->fromWeb(fn () => app(DoctorNotificationController::class)->destroy($notification));
    }

    public function productsIndex(): JsonResponse
    {
        $products = Product::query()
            ->with('categoryItem:id,name')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return response()->json([
            'products' => $products->map(fn (Product $p) => $this->productPayload($p))->values(),
            'summary' => DoctorProductInventoryController::inventorySummary(),
        ]);
    }

    public function servicesIndex(): JsonResponse
    {
        $doctor = auth('doctor')->user();
        $services = $doctor?->services()->orderBy('name')->get() ?? collect();
        $activeCount = $services->where('status', 'active')->count();
        $avgPrice = (float) $services
            ->map(fn ($service) => $service->promo_price ?? $service->price)
            ->filter(fn ($price) => $price !== null)
            ->avg();

        return response()->json([
            'services' => $services->map(fn (Service $s) => $this->servicePayload($s))->values(),
            'stats' => [
                'active_count' => $activeCount,
                'inactive_count' => max(0, $services->count() - $activeCount),
                'avg_price' => $avgPrice,
            ],
        ]);
    }

    public function profileShow(): JsonResponse
    {
        return response()->json([
            'doctor' => $this->doctorPayload(auth('doctor')->user()),
        ]);
    }

    public function profileUpdate(Request $request): JsonResponse
    {
        return $this->fromWeb(fn () => app(DoctorProfileController::class)->update($request));
    }

    public function profileUpdatePassword(Request $request): JsonResponse
    {
        return $this->fromWeb(fn () => app(DoctorProfileController::class)->updatePassword($request));
    }

    public function availabilityIndex(): JsonResponse
    {
        $doctor = auth('doctor')->user();
        $this->ensureDefaultWeeklySchedule((int) $doctor->id);

        $weeklySchedules = DoctorWeeklySchedule::query()
            ->where('doctor_id', $doctor->id)
            ->orderBy('weekday')
            ->get();

        $blockedDates = DoctorBlockedDate::query()
            ->where('doctor_id', $doctor->id)
            ->orderBy('blocked_date')
            ->get();

        return response()->json([
            'weekly_schedules' => $weeklySchedules->map(fn ($s) => $this->weeklySchedulePayload($s))->values(),
            'blocked_dates' => $blockedDates->map(fn ($b) => $this->blockedDatePayload($b))->values(),
            'closed_weekday' => AppointmentBookingRules::CLOSED_WEEKDAY,
        ]);
    }

    public function availabilityWeekday(int $weekday): JsonResponse
    {
        abort_unless($weekday >= 1 && $weekday <= 7, 404);

        $doctorId = (int) auth('doctor')->id();
        $this->ensureDefaultWeeklySchedule($doctorId);

        $schedule = DoctorWeeklySchedule::query()
            ->where('doctor_id', $doctorId)
            ->where('weekday', $weekday)
            ->firstOrFail();

        return response()->json([
            'schedule' => $this->weeklySchedulePayload($schedule),
        ]);
    }

    public function availabilityUpdateWeekday(Request $request, int $weekday): JsonResponse
    {
        return $this->fromWeb(fn () => app(DoctorAvailabilityController::class)->updateWeekday($request, $weekday));
    }

    public function availabilityToggleDay(Request $request, int $weekday): JsonResponse
    {
        return $this->fromWeb(fn () => app(DoctorAvailabilityController::class)->toggleDay($request, $weekday));
    }

    public function availabilityStoreBlockedDate(Request $request): JsonResponse
    {
        return $this->fromWeb(fn () => app(DoctorAvailabilityController::class)->storeBlockedDate($request));
    }

    public function availabilityDestroyBlockedDate(DoctorBlockedDate $blockedDate): JsonResponse
    {
        return $this->fromWeb(fn () => app(DoctorAvailabilityController::class)->destroyBlockedDate($blockedDate));
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildServiceChecklist(?TreatmentPatientPackage $patientPackage): array
    {
        if ($patientPackage === null) {
            return [];
        }

        $patientPackage->loadMissing('treatmentPackage.services:id,name');
        $completedByService = TreatmentPackageUsageHistory::query()
            ->where('patient_package_id', $patientPackage->id)
            ->where('status', 'completed')
            ->selectRaw('service_id, COUNT(*) as total_done')
            ->groupBy('service_id')
            ->pluck('total_done', 'service_id')
            ->map(static fn ($v): int => (int) $v)
            ->all();

        $rows = [];
        foreach ($patientPackage->treatmentPackage?->services ?? [] as $service) {
            $serviceId = (int) $service->id;
            $requiredSessions = max(1, (int) ($service->pivot->sessions ?? 1));
            $doneSessions = min($requiredSessions, (int) ($completedByService[$serviceId] ?? 0));

            for ($sessionNo = 1; $sessionNo <= $requiredSessions; $sessionNo++) {
                $rows[] = [
                    'key' => $serviceId.':'.$sessionNo,
                    'service_id' => $serviceId,
                    'service_name' => (string) $service->name,
                    'session_no' => $sessionNo,
                    'required_sessions' => $requiredSessions,
                    'is_done' => $sessionNo <= $doneSessions,
                ];
            }
        }

        return $rows;
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

    private function ensureDefaultWeeklySchedule(int $doctorId): void
    {
        if (DoctorWeeklySchedule::query()->where('doctor_id', $doctorId)->exists()) {
            return;
        }

        for ($d = 1; $d <= 7; $d++) {
            $isUnavailable = $d >= 6 || $d === AppointmentBookingRules::CLOSED_WEEKDAY;
            DoctorWeeklySchedule::query()->create([
                'doctor_id' => $doctorId,
                'weekday' => $d,
                'is_active' => ! $isUnavailable,
                'start_time' => $isUnavailable ? null : '09:00:00',
                'end_time' => $isUnavailable ? null : '17:00:00',
            ]);
        }
    }

    /**
     * Run an existing Blade portal action and normalize redirects to JSON.
     */
    protected function fromWeb(callable $action): JsonResponse
    {
        try {
            $result = $action();
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        }

        if ($result instanceof JsonResponse) {
            return $result;
        }

        if ($result instanceof RedirectResponse) {
            $session = $result->getSession();
            if ($session?->has('errors')) {
                $errors = $session->get('errors');
                $messages = $errors instanceof MessageBag
                    ? $errors->getMessages()
                    : (is_array($errors) ? $errors : []);

                return response()->json([
                    'message' => __('Validation failed.'),
                    'errors' => $messages,
                ], 422);
            }

            return response()->json([
                'message' => $session?->get('success') ?? $session?->get('info') ?? __('OK'),
            ]);
        }

        return response()->json([
            'message' => __('OK'),
        ]);
    }
}
