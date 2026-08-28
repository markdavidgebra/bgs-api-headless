<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\BooksAppointments;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentNote;
use App\Models\AppointmentPayment;
use App\Models\ClinicalStaff;
use App\Models\Patient;
use App\Models\PatientSubscription;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Service;
use App\Models\TreatmentPackage;
use App\Models\TreatmentPatientPackage;
use App\Notifications\Patient\AppointmentBookedPatientNotification;
use App\Notifications\Patient\AppointmentRescheduledPatientNotification;
use App\Notifications\Patient\PatientPasswordResetLinkSentNotification;
use App\Rules\BookableAppointmentDate;
use App\Support\AppointmentBookingRules;
use App\Support\ClinicalStaffAppointmentAlerts;
use App\Support\PatientLogin;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

/**
 * JSON API for the React patient portal (mirrors web `/patient/*` controllers).
 *
 * Session-based auth on the `web` guard. Mounted at `/api/patient/*` from
 * `routes/web.php` so the SPA can run on the same domain (Sanctum stateful)
 * or via Vite proxy in local dev.
 */
class PatientPortalController extends Controller
{
    use BooksAppointments;

    /*
    |--------------------------------------------------------------------------
    | Auth
    |--------------------------------------------------------------------------
    */

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::guard('admin')->validate($credentials) || Auth::guard('doctor')->validate($credentials)) {
            throw ValidationException::withMessages([
                'email' => [__('Use the staff portal to sign in with this email.')],
            ]);
        }

        if (! PatientLogin::attempt(Auth::guard('web'), $credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $patient = Auth::guard('web')->user();
        $status = strtolower((string) ($patient->status ?? 'active'));
        if ($status !== 'active') {
            Auth::guard('web')->logout();

            throw ValidationException::withMessages([
                'email' => [__('Your account is awaiting admin approval.')],
            ]);
        }

        $request->session()->regenerate();

        return response()->json([
            'message' => __('Login successful.'),
            'csrf_token' => csrf_token(),
            'patient' => $this->patientPayload($patient),
        ]);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        try {
            $status = Password::broker('users')->sendResetLink(
                $request->only('email'),
                function (Patient $user, string $token) {
                    $url = $this->patientPortalResetUrl($token, $user->email);
                    $notification = new ResetPassword($token);
                    $notification->createUrlUsing(fn () => $url);
                    $user->notify($notification);
                }
            );
        } catch (\Throwable $e) {
            report($e);

            throw ValidationException::withMessages([
                'email' => [__('We could not send the reset email right now. Please try again later or contact support.')],
            ]);
        }

        if ($status === Password::RESET_LINK_SENT) {
            $patient = Patient::query()
                ->where('email', strtolower(trim((string) $request->input('email'))))
                ->first();
            if ($patient) {
                Notification::send($patient, new PatientPasswordResetLinkSentNotification);
            }
        }

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json([
            'message' => __($status),
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::broker('users')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (Patient $user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json([
            'message' => __($status),
        ]);
    }

    /**
     * Build the SPA password-reset URL for patient emails.
     * PATIENT_PORTAL_URL may be a portal origin or a /login path.
     */
    protected function patientPortalResetUrl(string $token, string $email): string
    {
        $base = rtrim((string) config('app.patient_portal_url'), '/');
        if ($base === '') {
            $base = rtrim((string) config('app.url'), '/');
        }

        $base = (string) preg_replace('#/login$#i', '', $base);
        $base = rtrim($base, '/');

        return $base.'/reset-password?'.http_build_query([
            'token' => $token,
            'email' => $email,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => __('Logged out.')]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'csrf_token' => csrf_token(),
            'patient' => $this->patientPayload($request->user('web')),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    public function dashboard(Request $request): JsonResponse
    {
        $patient = $request->user('web');
        $patientId = $patient->id;

        $upcomingAppointment = Appointment::query()
            ->where('patient_id', $patientId)
            ->whereIn('status', ['pending', 'confirmed', 'rescheduled'])
            ->whereDate('appointment_date', '>=', now()->toDateString())
            ->with(['doctor', 'service'])
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->first();

        $activePackage = TreatmentPatientPackage::query()
            ->where('patient_id', $patientId)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', now()->toDateString());
            })
            ->where('remaining_sessions', '>', 0)
            ->with('treatmentPackage')
            ->orderByRaw('end_date IS NULL')
            ->orderBy('end_date')
            ->first();

        if (! $activePackage) {
            $activePackage = TreatmentPatientPackage::query()
                ->where('patient_id', $patientId)
                ->where('status', 'active')
                ->with('treatmentPackage')
                ->orderByDesc('updated_at')
                ->first();
        }

        $activeMembership = PatientSubscription::query()
            ->where('patient_id', $patientId)
            ->where('status', 'active')
            ->with('membershipPlan.services')
            ->orderByDesc('start_date')
            ->first();

        $tablePayment = Payment::query()
            ->where('patient_id', $patientId)
            ->with(['referenceAppointment.doctor', 'referenceAppointment.service'])
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->first();

        $appointmentInvoice = AppointmentPayment::query()
            ->whereHas('appointment', fn ($q) => $q->where('patient_id', $patientId))
            ->with(['appointment.doctor', 'appointment.service'])
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->first();

        [$latestPaymentRecord, $latestPaymentKind] = $this->resolveLatestPaymentPair($tablePayment, $appointmentInvoice);

        return response()->json([
            'patient' => $this->patientPayload($patient),
            'upcomingAppointment' => $upcomingAppointment ? $this->appointmentPayload($upcomingAppointment) : null,
            'activePackage' => $activePackage ? $this->activePackagePayload($activePackage) : null,
            'activeMembership' => $activeMembership ? $this->membershipSubscriptionPayload($activeMembership, true) : null,
            'latestPaymentRecord' => $latestPaymentRecord
                ? ($latestPaymentKind === 'payment'
                    ? $this->paymentPayload($latestPaymentRecord)
                    : $this->appointmentPaymentPayload($latestPaymentRecord))
                : null,
            'latestPaymentKind' => $latestPaymentRecord
                ? ($latestPaymentKind === 'payment' ? 'payment' : 'appointment_payment')
                : null,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Appointments
    |--------------------------------------------------------------------------
    */

    public function appointments(Request $request): JsonResponse
    {
        $patientId = $request->user('web')->id;

        $upcoming = Appointment::query()
            ->where('patient_id', $patientId)
            ->whereIn('status', ['pending', 'confirmed', 'rescheduled'])
            ->whereDate('appointment_date', '>=', now()->toDateString())
            ->with(['doctor', 'service'])
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->get();

        $past = Appointment::query()
            ->where('patient_id', $patientId)
            ->where(function ($q) {
                $q->whereDate('appointment_date', '<', now()->toDateString())
                    ->orWhereIn('status', ['completed', 'cancelled']);
            })
            ->with(['doctor', 'service'])
            ->orderByDesc('appointment_date')
            ->orderByDesc('appointment_time')
            ->paginate(15);

        return response()->json([
            'upcomingAppointments' => $upcoming->map(fn (Appointment $a) => $this->appointmentPayload($a))->values(),
            'pastAppointments' => [
                'data' => $past->getCollection()->map(fn (Appointment $a) => $this->appointmentPayload($a))->values(),
                'current_page' => $past->currentPage(),
                'last_page' => $past->lastPage(),
                'per_page' => $past->perPage(),
                'total' => $past->total(),
            ],
        ]);
    }

    public function appointment(Request $request, Appointment $appointment): JsonResponse
    {
        $this->ensurePatientOwnsAppointment($request, $appointment);
        $appointment->load(['doctor', 'service']);

        return response()->json(['appointment' => $this->appointmentPayload($appointment)]);
    }

    public function bookOptions(Request $request): JsonResponse
    {
        $services = Service::query()
            ->where('status', 'active')
            ->where('is_bookable', true)
            ->orderBy('name')
            ->get(['id', 'name', 'duration_minutes', 'price']);

        $packages = TreatmentPackage::query()
            ->where('status', 'active')
            ->with(['services' => fn ($q) => $q
                ->where('services.status', 'active')
                ->where('services.is_bookable', true)
                ->orderBy('name')])
            ->orderBy('name')
            ->get(['id', 'name', 'description', 'price', 'validity_value', 'validity_type']);

        $products = Product::query()
            ->where('status', 'active')
            ->where('is_available_for_sale', true)
            ->orderBy('name')
            ->get(['id', 'name', 'brand', 'selling_price', 'discount_price', 'unit', 'stock_quantity']);

        $date = $request->query('date');
        $serviceId = $request->query('service_id');
        $doctors = $this->bookableDoctorsQuery($date, $serviceId ? (int) $serviceId : null)
            ->get(['id', 'name', 'specialty']);

        return response()->json([
            'services' => $services->map(fn (Service $s) => [
                'id' => (int) $s->id,
                'name' => (string) $s->name,
                'duration_minutes' => $s->duration_minutes !== null ? (int) $s->duration_minutes : null,
                'price' => $s->price !== null ? (float) $s->price : null,
            ])->values(),
            'packages' => $packages->map(fn (TreatmentPackage $p) => [
                'id' => (int) $p->id,
                'name' => (string) $p->name,
                'description' => (string) ($p->description ?? ''),
                'price' => $p->price !== null ? (float) $p->price : null,
                'validity_label' => (string) ($p->validity_label ?? ''),
                'services' => $p->services->map(fn (Service $s) => [
                    'id' => (int) $s->id,
                    'name' => (string) $s->name,
                    'sessions' => $s->pivot?->sessions !== null ? (int) $s->pivot->sessions : null,
                    'is_bookable' => true,
                ])->values(),
            ])->values(),
            'products' => $products->map(fn (Product $p) => [
                'id' => (int) $p->id,
                'name' => (string) $p->name,
                'brand' => (string) ($p->brand ?? ''),
                'price' => $p->final_price !== null ? (float) $p->final_price : ($p->selling_price !== null ? (float) $p->selling_price : null),
                'unit' => (string) ($p->unit ?? ''),
                'in_stock' => (int) ($p->stock_quantity ?? 0) > 0,
            ])->values(),
            'doctors' => $doctors->map(fn (ClinicalStaff $d) => [
                'id' => (int) $d->id,
                'name' => (string) $d->name,
                'specialty' => (string) ($d->specialty ?? ''),
            ])->values(),
        ]);
    }

    public function bookableDoctors(Request $request): JsonResponse
    {
        $data = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today', new BookableAppointmentDate],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'service_ids' => ['nullable', 'array'],
            'service_ids.*' => ['integer', 'exists:services,id'],
        ]);

        $serviceIds = array_values(array_unique(array_map(
            'intval',
            $data['service_ids'] ?? (isset($data['service_id']) ? [$data['service_id']] : [])
        )));

        $doctors = $this->bookableDoctorsQuery($data['date'], $serviceIds !== [] ? $serviceIds : null)
            ->get(['id', 'name', 'specialty']);

        return response()->json([
            'doctors' => $doctors->map(fn (ClinicalStaff $d) => [
                'id' => (int) $d->id,
                'name' => (string) $d->name,
                'specialty' => (string) ($d->specialty ?? ''),
            ])->values(),
        ]);
    }

    public function bookAppointment(Request $request): JsonResponse
    {
        $data = $request->validate([
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'service_ids' => ['nullable', 'array'],
            'service_ids.*' => ['integer', 'exists:services,id'],
            'package_ids' => ['nullable', 'array'],
            'package_ids.*' => ['integer', 'exists:treatment_packages,id'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
            'doctor_id' => ['required', 'exists:clinical_staff,id'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today', new BookableAppointmentDate],
            'appointment_time' => ['required', 'date_format:H:i'],
            'patient_concern' => ['nullable', 'string', 'max:2000'],
        ]);

        $patientId = $request->user('web')->id;
        $serviceIds = array_values(array_unique(array_map(
            'intval',
            $data['service_ids'] ?? (isset($data['service_id']) ? [$data['service_id']] : [])
        )));
        $packageIds = array_values(array_unique(array_map('intval', $data['package_ids'] ?? [])));
        $productIds = array_values(array_unique(array_map('intval', $data['product_ids'] ?? [])));

        $selectedPackages = collect();
        if ($packageIds !== []) {
            $selectedPackages = TreatmentPackage::query()
                ->whereIn('id', $packageIds)
                ->where('status', 'active')
                ->with(['services' => fn ($q) => $q
                    ->where('services.status', 'active')
                    ->where('services.is_bookable', true)])
                ->get();

            foreach ($selectedPackages as $package) {
                foreach ($package->services as $service) {
                    $serviceIds[] = (int) $service->id;
                }
            }
            $serviceIds = array_values(array_unique($serviceIds));
        }

        if ($serviceIds === []) {
            throw ValidationException::withMessages([
                'service_ids' => 'Please select at least one service or package with bookable services.',
            ]);
        }

        if (! $this->bookableDoctorsQuery($data['appointment_date'], $serviceIds)
            ->whereKey((int) $data['doctor_id'])
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'doctor_id' => 'This clinical staff member is not available on the selected date for the selected service(s).',
            ]);
        }

        $concernParts = [];
        if (! empty($data['patient_concern'])) {
            $concernParts[] = trim((string) $data['patient_concern']);
        }
        if ($selectedPackages->isNotEmpty()) {
            $concernParts[] = 'Requested packages: '.$selectedPackages->pluck('name')->filter()->implode(', ');
        }
        if ($productIds !== []) {
            $productNames = Product::query()
                ->whereIn('id', $productIds)
                ->where('status', 'active')
                ->pluck('name')
                ->filter()
                ->values();
            if ($productNames->isNotEmpty()) {
                $concernParts[] = 'Requested products: '.$productNames->implode(', ');
            }
        }
        $patientConcern = trim(implode("\n", array_filter($concernParts)));

        $appointments = DB::transaction(function () use ($data, $patientId, $serviceIds, $productIds, $patientConcern) {
            $created = [];
            $patient = $patientConcern !== ''
                ? Patient::query()->find($patientId)
                : null;

            foreach ($serviceIds as $index => $serviceId) {
                $appointment = Appointment::create([
                    'appointment_no' => $this->generateAppointmentNo(),
                    'patient_id' => $patientId,
                    'doctor_id' => (int) $data['doctor_id'],
                    'service_id' => $serviceId,
                    'appointment_date' => $data['appointment_date'],
                    'appointment_time' => $data['appointment_time'],
                    'status' => 'pending',
                ]);

                if ($index === 0 && $patientConcern !== '') {
                    AppointmentNote::create([
                        'appointment_id' => $appointment->id,
                        'patient_concern' => $patientConcern,
                        'section_authors' => [
                            'patient_concern' => AppointmentNote::authorPayloadFromUserName(
                                'patient',
                                $patient?->name,
                            ),
                        ],
                    ]);
                }

                if ($index === 0 && $productIds !== []) {
                    $sync = [];
                    foreach ($productIds as $productId) {
                        $sync[$productId] = ['quantity' => 1];
                    }
                    $appointment->prescribedProducts()->syncWithoutDetaching($sync);
                }

                $created[] = $appointment;
            }

            return $created;
        });

        foreach ($appointments as $appointment) {
            $appointment->load(['patient:id,name,email', 'doctor:id,name', 'service:id,name']);
            try {
                if ($appointment->patient) {
                    Notification::send($appointment->patient, new AppointmentBookedPatientNotification($appointment));
                }
                ClinicalStaffAppointmentAlerts::notifyDoctorOfNewBooking($appointment);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $primary = $appointments[0];

        return response()->json([
            'message' => count($appointments) > 1
                ? 'Appointments booked successfully.'
                : 'Appointment booked successfully.',
            'id' => (int) $primary->id,
            'ids' => array_map(fn (Appointment $a) => (int) $a->id, $appointments),
            'appointment' => $this->appointmentPayload($primary),
            'appointments' => array_map(fn (Appointment $a) => $this->appointmentPayload($a), $appointments),
        ], 201);
    }

    public function rescheduleAppointment(Request $request, Appointment $appointment): JsonResponse
    {
        $this->ensurePatientOwnsAppointment($request, $appointment);
        $this->ensureCanChangeAppointment($appointment);

        $data = $request->validate([
            'appointment_date' => ['required', 'date', 'after_or_equal:today', new BookableAppointmentDate],
            'appointment_time' => ['required'],
        ]);

        $appointment->fill([
            'appointment_date' => $data['appointment_date'],
            'appointment_time' => $data['appointment_time'],
            'status' => 'rescheduled',
            'reminder_sent_at' => null,
        ])->save();

        $appointment->load(['patient:id,name,email', 'doctor:id,name', 'service:id,name']);
        try {
            if ($appointment->patient) {
                Notification::send($appointment->patient, new AppointmentRescheduledPatientNotification($appointment));
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json([
            'message' => 'Appointment rescheduled.',
            'appointment' => $this->appointmentPayload($appointment),
        ]);
    }

    public function cancelAppointment(Request $request, Appointment $appointment): JsonResponse
    {
        $this->ensurePatientOwnsAppointment($request, $appointment);
        $this->ensureCanChangeAppointment($appointment);

        $appointment->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Appointment cancelled.',
            'appointment' => $this->appointmentPayload($appointment->fresh(['doctor', 'service'])),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */

    public function notifications(Request $request): JsonResponse
    {
        $paginator = $request->user('web')
            ->notifications()
            ->orderByDesc('created_at')
            ->paginate(20);

        $items = $paginator->getCollection()->map(function ($n): array {
            return [
                'id' => (string) $n->id,
                'type' => (string) $n->type,
                'read_at' => optional($n->read_at)->toIso8601String(),
                'created_at' => optional($n->created_at)->toIso8601String(),
                'data' => is_array($n->data) ? $n->data : (array) $n->data,
            ];
        });

        $unread = (int) $request->user('web')->unreadNotifications()->count();

        return response()->json([
            'notifications' => [
                'data' => $items->values(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
            'unread_count' => $unread,
        ]);
    }

    public function markNotificationRead(Request $request, string $notification): JsonResponse
    {
        $row = $request->user('web')
            ->notifications()
            ->whereKey($notification)
            ->firstOrFail();

        if ($row->read_at === null) {
            $row->markAsRead();
        }

        return response()->json(['message' => 'Notification marked as read.']);
    }

    public function markAllNotificationsRead(Request $request): JsonResponse
    {
        $request->user('web')->unreadNotifications->markAsRead();

        return response()->json(['message' => 'All notifications marked as read.']);
    }

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    public function profile(Request $request): JsonResponse
    {
        return response()->json(['patient' => $this->patientPayload($request->user('web'))]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $patient = $request->user('web');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($patient->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'birthdate' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:1000'],
            'emergency_contact' => ['nullable', 'string', 'max:255'],
            'skin_type' => ['nullable', 'string', 'max:255'],
            'skin_concerns' => ['nullable', 'string', 'max:1000'],
            'recovery_time' => ['nullable', 'string', 'max:255'],
            'max_appointments_per_day' => ['nullable', 'integer', 'min:0', 'max:20'],
            'history_summary' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $patient->fill($validated)->save();

        return response()->json([
            'message' => 'Profile updated successfully.',
            'patient' => $this->patientPayload($patient->fresh()),
        ]);
    }

    public function uploadAvatar(Request $request): JsonResponse
    {
        $patient = $request->user('web');

        $request->validate([
            'avatar' => ['required', 'image', 'max:5120'],
        ]);

        $this->removeStoredPatientAvatar($patient->avatar_path);

        $dir = public_path('uploads/patients');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $file = $request->file('avatar');
        $ext = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'jpg';
        $filename = $patient->id.'_'.uniqid('', true).'.'.$ext;
        $file->move($dir, $filename);

        $patient->avatar_path = 'uploads/patients/'.$filename;
        $patient->save();

        return response()->json([
            'message' => 'Avatar updated successfully.',
            'patient' => $this->patientPayload($patient->fresh()),
        ]);
    }

    public function removeAvatar(Request $request): JsonResponse
    {
        $patient = $request->user('web');

        if ($patient->avatar_path) {
            $this->removeStoredPatientAvatar($patient->avatar_path);
            $patient->avatar_path = null;
            $patient->save();
        }

        return response()->json([
            'message' => 'Avatar removed.',
            'patient' => $this->patientPayload($patient->fresh()),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Treatments
    |--------------------------------------------------------------------------
    */

    public function treatments(Request $request): JsonResponse
    {
        $filter = (string) $request->query('status', 'all');
        if (! in_array($filter, ['all', 'ongoing', 'completed', 'cancelled'], true)) {
            $filter = 'all';
        }

        $packages = TreatmentPatientPackage::query()
            ->where('patient_id', $request->user('web')->id)
            ->with([
                'treatmentPackage.doctors',
                'usageHistories' => fn ($q) => $q->whereNotNull('used_on')->orderByDesc('used_on'),
            ])
            ->orderByDesc('start_date')
            ->orderByDesc('purchased_at')
            ->orderByDesc('id')
            ->get();

        $rows = $packages->map(fn (TreatmentPatientPackage $p) => $this->treatmentRowPayload($p));

        if ($filter !== 'all') {
            $rows = $rows->filter(fn (array $r) => $r['display_status'] === $filter)->values();
        }

        return response()->json([
            'treatments' => $rows->values(),
            'filter' => $filter,
        ]);
    }

    public function treatment(Request $request, TreatmentPatientPackage $patientPackage): JsonResponse
    {
        if ((int) $patientPackage->patient_id !== (int) $request->user('web')->id) {
            abort(404);
        }

        $patientPackage->load([
            'treatmentPackage.doctors',
            'treatmentPackage.services',
            'usageHistories' => fn ($q) => $q->with('service')->orderByDesc('used_on')->orderByDesc('id'),
        ]);

        [$displayStatus, $displayLabel] = $this->resolveTreatmentDisplayStatus($patientPackage);

        $total = (int) $patientPackage->total_sessions;
        $done = (int) $patientPackage->used_sessions;
        $progress = $total > 0 ? min(100, (int) round(($done / $total) * 100)) : 0;

        $started = $patientPackage->start_date ?? $patientPackage->purchased_at;
        $lastSession = $patientPackage->usageHistories->first()?->used_on;

        $treatment = $patientPackage->treatmentPackage;

        return response()->json([
            'patientPackage' => [
                'id' => (int) $patientPackage->id,
                'status' => (string) ($patientPackage->status ?? 'active'),
                'notes' => (string) ($patientPackage->notes ?? ''),
                'usageHistories' => $patientPackage->usageHistories->map(fn ($h) => [
                    'id' => (int) $h->id,
                    'used_on' => optional($h->used_on)->toDateString(),
                    'notes' => (string) ($h->notes ?? ''),
                    'status' => (string) ($h->status ?? 'completed'),
                    'service' => $h->relationLoaded('service') && $h->service
                        ? ['id' => (int) $h->service->id, 'name' => (string) $h->service->name]
                        : null,
                ])->values(),
            ],
            'treatment' => $treatment ? [
                'id' => (int) $treatment->id,
                'name' => (string) $treatment->name,
                'category' => (string) ($treatment->category ?? ''),
                'description' => (string) ($treatment->description ?? ''),
                'aftercare' => (string) ($treatment->aftercare ?? ''),
                'price' => $treatment->price !== null ? (float) $treatment->price : null,
                'doctors' => $treatment->doctors->map(fn (ClinicalStaff $d) => [
                    'id' => (int) $d->id,
                    'name' => (string) $d->name,
                ])->values(),
            ] : null,
            'totalSessions' => $total,
            'sessionsDone' => $done,
            'progressPercent' => $progress,
            'dateStarted' => $started ? Carbon::parse((string) $started)->format('M j, Y') : '—',
            'endDate' => $patientPackage->end_date ? Carbon::parse((string) $patientPackage->end_date)->format('M j, Y') : '—',
            'lastSessionDate' => $lastSession ? Carbon::parse((string) $lastSession)->format('M j, Y') : '—',
            'displayStatus' => $displayStatus,
            'displayLabel' => $displayLabel,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Memberships
    |--------------------------------------------------------------------------
    */

    public function memberships(Request $request): JsonResponse
    {
        $patientId = $request->user('web')->id;

        $subscription = PatientSubscription::query()
            ->where('patient_id', $patientId)
            ->with('membershipPlan.services')
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->first();

        $history = PatientSubscription::query()
            ->where('patient_id', $patientId)
            ->with('membershipPlan')
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->get();

        $previousMemberships = $history->skip(1)->values();

        $latestMembershipPayment = null;
        if ($subscription?->membership_plan_id) {
            $latestMembershipPayment = Payment::query()
                ->where('patient_id', $patientId)
                ->where('reference_type', 'membership')
                ->where('reference_id', $subscription->membership_plan_id)
                ->orderByDesc('payment_date')
                ->orderByDesc('id')
                ->first();
        }

        if (! $latestMembershipPayment) {
            $latestMembershipPayment = Payment::query()
                ->where('patient_id', $patientId)
                ->where('reference_type', 'membership')
                ->orderByDesc('payment_date')
                ->orderByDesc('id')
                ->first();
        }

        return response()->json([
            'subscription' => $subscription ? $this->membershipSubscriptionPayload($subscription, true) : null,
            'previousMemberships' => $previousMemberships
                ->map(fn (PatientSubscription $s) => $this->membershipSubscriptionPayload($s, false))
                ->values(),
            'latestMembershipPayment' => $latestMembershipPayment ? [
                'payment_status' => (string) ($latestMembershipPayment->payment_status ?? 'unpaid'),
                'payment_date' => optional($latestMembershipPayment->payment_date)->toDateString(),
                'method_label' => (string) $latestMembershipPayment->method_label,
                'payment_id' => (string) ($latestMembershipPayment->payment_id ?? ''),
                'transaction_reference' => (string) ($latestMembershipPayment->transaction_reference ?? ''),
            ] : null,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Payments
    |--------------------------------------------------------------------------
    */

    public function payments(Request $request): JsonResponse
    {
        $patientId = $request->user('web')->id;
        $perPage = 15;
        $page = max(1, (int) $request->integer('page', 1));

        $posPayments = Payment::query()
            ->where('patient_id', $patientId)
            ->with([
                'referenceAppointment.doctor',
                'referenceAppointment.service',
                'referencePackage',
                'referenceMembership',
                'referenceProduct',
                'referenceService',
            ])
            ->get()
            ->map(function (Payment $p) {
                $payload = $this->paymentPayload($p);
                $payload['source'] = 'payment';

                return $payload;
            });

        $appointmentPayments = AppointmentPayment::query()
            ->whereHas('appointment', fn ($q) => $q->where('patient_id', $patientId))
            ->with(['appointment.doctor', 'appointment.service'])
            ->get()
            ->map(function (AppointmentPayment $p) {
                $payload = $this->appointmentPaymentPayload($p);
                $payload['source'] = 'appointment_payment';

                return $payload;
            });

        $merged = $posPayments
            ->concat($appointmentPayments)
            ->sortByDesc(fn (array $row) => $row['paid_at'] ?? $row['created_at'] ?? '')
            ->values();

        $latestPaidAt = $merged
            ->where('is_paid', true)
            ->pluck('paid_at')
            ->filter()
            ->sortDesc()
            ->first();

        $totals = [
            'record_count' => $merged->count(),
            'paid_sum' => (float) $merged->where('is_paid', true)->sum('amount'),
            'outstanding_sum' => (float) $merged->where('is_paid', false)->sum('amount'),
            'pending_sum' => (float) $merged
                ->filter(fn (array $row) => strtolower((string) ($row['payment_status'] ?? '')) === 'pending')
                ->sum('amount'),
            'latest_paid_at' => $latestPaidAt ? Carbon::parse($latestPaidAt)->toIso8601String() : null,
        ];

        $total = $merged->count();
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min($page, $lastPage);

        return response()->json([
            'data' => $merged->forPage($page, $perPage)->values(),
            'current_page' => $page,
            'last_page' => $lastPage,
            'per_page' => $perPage,
            'total' => $total,
            'totals' => $totals,
        ]);
    }

    public function payment(Request $request, string $source, int $payment): JsonResponse
    {
        $patientId = $request->user('web')->id;

        if ($source === 'payment') {
            $record = Payment::query()
                ->whereKey($payment)
                ->where('patient_id', $patientId)
                ->with([
                    'referenceAppointment.doctor',
                    'referenceAppointment.service',
                    'referencePackage',
                    'referenceMembership',
                    'referenceProduct',
                    'referenceService',
                ])
                ->firstOrFail();

            $payload = $this->paymentPayload($record);
            $payload['source'] = 'payment';

            return response()->json(['payment' => $payload]);
        }

        if ($source === 'appointment_payment') {
            $record = AppointmentPayment::query()
                ->whereKey($payment)
                ->whereHas('appointment', fn ($q) => $q->where('patient_id', $patientId))
                ->with(['appointment.doctor', 'appointment.service'])
                ->firstOrFail();

            $payload = $this->appointmentPaymentPayload($record);
            $payload['source'] = 'appointment_payment';

            return response()->json(['payment' => $payload]);
        }

        abort(404);
    }

    /*
    |--------------------------------------------------------------------------
    | Promotions
    |--------------------------------------------------------------------------
    */

    public function promotions(): JsonResponse
    {
        $today = now()->toDateString();

        $promotions = Promotion::query()
            ->where('status', 'active')
            ->where(function ($q) use ($today) {
                $q->whereNull('start_date')->orWhereDate('start_date', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', $today);
            })
            ->orderByDesc('discount_value')
            ->orderBy('end_date')
            ->get();

        $list = $promotions->map(fn (Promotion $p) => $this->promotionListPayload($p))->values();

        return response()->json([
            'promotions' => $list,
            'featuredPromo' => $list->first() ?? null,
        ]);
    }

    public function promotion(int $promotion): JsonResponse
    {
        $today = now()->toDateString();

        $record = Promotion::query()
            ->whereKey($promotion)
            ->where('status', 'active')
            ->where(function ($q) use ($today) {
                $q->whereNull('start_date')->orWhereDate('start_date', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', $today);
            })
            ->with(['services', 'treatmentPackages', 'membershipPlans', 'products'])
            ->firstOrFail();

        $payload = $this->promotionListPayload($record);
        $payload['terms_and_conditions'] = (string) ($record->terms_and_conditions ?? '');
        $payload['services'] = $record->services->map(fn ($s) => ['id' => (int) $s->id, 'name' => (string) $s->name])->values();
        $payload['treatmentPackages'] = $record->treatmentPackages->map(fn ($t) => ['id' => (int) $t->id, 'name' => (string) $t->name])->values();
        $payload['membershipPlans'] = $record->membershipPlans->map(fn ($m) => ['id' => (int) $m->id, 'name' => (string) $m->name])->values();
        $payload['products'] = $record->products->map(fn ($p) => ['id' => (int) $p->id, 'name' => (string) $p->name])->values();

        return response()->json(['promotion' => $payload]);
    }

    /*
    |--------------------------------------------------------------------------
    | Aftercare
    |--------------------------------------------------------------------------
    */

    public function aftercare(Request $request): JsonResponse
    {
        $patientId = $request->user('web')->id;

        $appointmentInstructions = AppointmentNote::query()
            ->whereHas('appointment', fn ($q) => $q->where('patient_id', $patientId))
            ->with(['appointment.service', 'appointment.doctor'])
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get()
            ->filter(fn (AppointmentNote $n) => filled($n->instructions))
            ->map(fn (AppointmentNote $n) => $this->aftercareFromAppointmentNote($n))
            ->values();

        $treatmentInstructions = TreatmentPatientPackage::query()
            ->where('patient_id', $patientId)
            ->with('treatmentPackage')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get()
            ->filter(fn (TreatmentPatientPackage $p) => filled($p->treatmentPackage?->aftercare))
            ->map(fn (TreatmentPatientPackage $p) => $this->aftercareFromTreatment($p))
            ->values();

        $membershipInstructions = PatientSubscription::query()
            ->where('patient_id', $patientId)
            ->with('membershipPlan')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get()
            ->filter(fn (PatientSubscription $s) => filled($s->membershipPlan?->aftercare))
            ->map(fn (PatientSubscription $s) => $this->aftercareFromMembership($s))
            ->values();

        $instructions = collect()
            ->merge($appointmentInstructions)
            ->merge($treatmentInstructions)
            ->merge($membershipInstructions)
            ->sortByDesc(fn (array $r) => $r['updated_at'] ?? '')
            ->values();

        return response()->json(['instructions' => $instructions]);
    }

    public function aftercareItem(Request $request, string $source, int $record): JsonResponse
    {
        $patientId = $request->user('web')->id;

        $item = match ($source) {
            'appointment' => $this->loadAftercareAppointment($patientId, $record),
            'treatment' => $this->loadAftercareTreatment($patientId, $record),
            'membership' => $this->loadAftercareMembership($patientId, $record),
            default => null,
        };

        if (! $item) {
            abort(404);
        }

        return response()->json(['item' => $item]);
    }

    /*
    |--------------------------------------------------------------------------
    | Payload helpers
    |--------------------------------------------------------------------------
    */

    protected function patientPayload(?Patient $patient): ?array
    {
        if (! $patient) {
            return null;
        }

        return [
            'id' => (int) $patient->id,
            'name' => (string) ($patient->name ?? ''),
            'email' => (string) ($patient->email ?? ''),
            'phone' => (string) ($patient->phone ?? ''),
            'avatar_url' => $patient->avatar_url,
            'status' => (string) ($patient->status ?? 'active'),
            'birthdate' => optional($patient->birthdate)->toDateString(),
            'gender' => (string) ($patient->gender ?? ''),
            'address' => (string) ($patient->address ?? ''),
            'emergency_contact' => (string) ($patient->emergency_contact ?? ''),
            'skin_type' => (string) ($patient->skin_type ?? ''),
            'skin_concerns' => (string) ($patient->skin_concerns ?? ''),
            'recovery_time' => (string) ($patient->recovery_time ?? ''),
            'max_appointments_per_day' => $patient->max_appointments_per_day !== null
                ? (int) $patient->max_appointments_per_day
                : null,
            'history_summary' => (string) ($patient->history_summary ?? ''),
            'notes' => (string) ($patient->notes ?? ''),
            'unread_notifications_count' => (int) $patient->unreadNotifications()->count(),
        ];
    }

    protected function appointmentPayload(Appointment $a): array
    {
        $time = $a->appointment_time;
        $timeIso = is_string($time) && strlen($time) >= 5 ? substr($time, 0, 5) : ($time ? Carbon::parse($time)->format('H:i') : null);
        $dateRaw = $a->getRawOriginal('appointment_date') ?: optional($a->appointment_date)->toDateString();

        return [
            'id' => (int) $a->id,
            'appointment_no' => (string) ($a->appointment_no ?? ''),
            'appointment_date' => $dateRaw,
            'appointment_time' => $timeIso,
            'date_display' => $a->appointment_date ? $a->appointment_date->format('F j, Y') : '—',
            'time_display' => $timeIso ? Carbon::parse($timeIso)->format('g:i A') : '—',
            'service_id' => $a->service_id,
            'doctor_id' => $a->doctor_id,
            'service_name' => (string) ($a->service?->name ?? '—'),
            'doctor_name' => (string) ($a->doctor?->name ?? '—'),
            'status' => (string) ($a->status ?? 'pending'),
            'status_label' => ucfirst((string) ($a->status ?? 'pending')),
        ];
    }

    protected function activePackagePayload(TreatmentPatientPackage $p): array
    {
        return [
            'id' => (int) $p->id,
            'status' => (string) ($p->status ?? 'active'),
            'total_sessions' => (int) $p->total_sessions,
            'used_sessions' => (int) $p->used_sessions,
            'remaining_sessions' => (int) $p->remaining_sessions,
            'start_date' => optional($p->start_date)->toDateString(),
            'end_date' => optional($p->end_date)->toDateString(),
            'treatmentPackage' => $p->treatmentPackage ? [
                'id' => (int) $p->treatmentPackage->id,
                'name' => (string) $p->treatmentPackage->name,
            ] : null,
        ];
    }

    protected function membershipSubscriptionPayload(PatientSubscription $s, bool $includePlanDetail): array
    {
        $plan = $s->membershipPlan;
        $planPayload = null;
        if ($plan) {
            $planPayload = [
                'id' => (int) $plan->id,
                'name' => (string) $plan->name,
                'price' => $plan->price !== null ? (float) $plan->price : 0,
                'description' => (string) ($plan->description ?? ''),
                'max_usage_per_month' => $plan->max_usage_per_month !== null ? (int) $plan->max_usage_per_month : null,
                'rollover_unused_sessions' => (bool) $plan->rollover_unused_sessions,
                'duration_label' => (string) ($plan->duration_label ?? ''),
                'cancellation_allowed' => (bool) $plan->cancellation_allowed,
                'terms_and_conditions' => (string) ($plan->terms_and_conditions ?? ''),
                'internal_notes' => (string) ($plan->internal_notes ?? ''),
                'aftercare' => (string) ($plan->aftercare ?? ''),
            ];

            if ($includePlanDetail) {
                $planPayload['services'] = ($plan->relationLoaded('services') ? $plan->services : collect())
                    ->map(fn ($svc) => [
                        'id' => (int) $svc->id,
                        'name' => (string) $svc->name,
                        'pivot' => ['sessions' => (int) ($svc->pivot->sessions ?? 0)],
                    ])->values();
            }
        }

        return [
            'id' => (int) $s->id,
            'status' => (string) ($s->status ?? 'active'),
            'start_date' => optional($s->start_date)->toDateString(),
            'end_date' => optional($s->end_date)->toDateString(),
            'renewal_date' => optional($s->renewal_date)->toDateString(),
            'sessions_used' => (int) $s->sessions_used,
            'sessions_remaining' => (int) $s->sessions_remaining,
            'notes' => (string) ($s->notes ?? ''),
            'membershipPlan' => $planPayload,
        ];
    }

    protected function paymentPayload(Payment $p): array
    {
        return [
            'id' => (int) $p->id,
            'reference_no' => (string) ($p->payment_id ?? ''),
            'invoice_no' => null,
            'amount' => $p->amount !== null ? (float) $p->amount : null,
            'formatted_amount' => (string) $p->formatted_amount,
            'payment_status' => (string) ($p->payment_status ?? 'unpaid'),
            'is_paid' => $p->payment_status === 'paid',
            'paid_at' => optional($p->payment_date)->toIso8601String(),
            'payment_date' => optional($p->payment_date)->toDateString(),
            'payment_method' => (string) ($p->payment_method ?? ''),
            'method_label' => (string) $p->method_label,
            'reference_type_label' => (string) $p->reference_type_label,
            'reference_name' => (string) ($p->reference_name ?? ''),
            'appointment' => $p->relationLoaded('referenceAppointment') && $p->referenceAppointment
                ? $this->appointmentPayload($p->referenceAppointment)
                : null,
            'appointment_id' => $p->reference_type === 'appointment' ? (int) ($p->reference_id ?? 0) : null,
            'deposit_notes' => (string) ($p->notes ?? ''),
            'created_at' => optional($p->created_at)->toIso8601String(),
        ];
    }

    protected function appointmentPaymentPayload(AppointmentPayment $ap): array
    {
        $appointment = $ap->relationLoaded('appointment') ? $ap->appointment : null;

        return [
            'id' => (int) $ap->id,
            'reference_no' => (string) ($ap->reference_no ?? ''),
            'invoice_no' => (string) ($ap->invoice_no ?? ''),
            'amount' => $ap->amount !== null ? (float) $ap->amount : null,
            'formatted_amount' => '₱'.number_format((float) ($ap->amount ?? 0), 2),
            'payment_status' => (string) ($ap->payment_status ?? 'pending'),
            'is_paid' => (bool) $ap->is_paid,
            'paid_at' => optional($ap->paid_at)->toIso8601String(),
            'payment_date' => optional($ap->paid_at)->toDateString(),
            'payment_method' => (string) ($ap->payment_method ?? ''),
            'method_label' => (string) ($ap->payment_method ?? ''),
            'reference_type_label' => 'Service',
            'reference_name' => (string) ($appointment?->service?->name ?? ''),
            'appointment' => $appointment ? $this->appointmentPayload($appointment) : null,
            'appointment_id' => (int) $ap->appointment_id,
            'deposit_notes' => (string) ($ap->deposit_notes ?? ''),
            'created_at' => optional($ap->created_at)->toIso8601String(),
        ];
    }

    protected function promotionListPayload(Promotion $p): array
    {
        return [
            'id' => (int) $p->id,
            'name' => (string) $p->name,
            'description' => (string) ($p->description ?? ''),
            'discount_label' => (string) $p->discount_label,
            'code' => (string) ($p->code ?? ''),
            'status' => (string) ($p->status ?? 'draft'),
            'start_date' => optional($p->start_date)->toDateString(),
            'end_date' => optional($p->end_date)->toDateString(),
            'image_url' => $p->image_url,
            'new_patients_only' => (bool) $p->new_patients_only,
            'validity_label' => (string) ($p->validity_label ?? 'Always available'),
            'scope_label' => (string) $p->scope_label,
            'limit_per_patient' => $p->limit_per_patient !== null ? (int) $p->limit_per_patient : null,
            'display_note' => (string) ($p->display_note ?? ''),
        ];
    }

    protected function treatmentRowPayload(TreatmentPatientPackage $p): array
    {
        $treatment = $p->treatmentPackage;
        $doctorNames = $treatment?->doctors?->pluck('name')->filter()->unique();

        $started = $p->start_date ?? $p->purchased_at;
        $lastSession = $p->usageHistories->first()?->used_on;

        [$displayStatus, $displayLabel] = $this->resolveTreatmentDisplayStatus($p);

        return [
            'id' => (int) $p->id,
            'treatment_name' => (string) ($treatment?->name ?? 'Treatment package'),
            'category' => (string) ($treatment?->category ?? ''),
            'doctors_label' => $doctorNames && $doctorNames->isNotEmpty() ? $doctorNames->implode(', ') : '—',
            'date_started' => $started ? Carbon::parse((string) $started)->format('M j, Y') : '—',
            'last_session' => $lastSession ? Carbon::parse((string) $lastSession)->format('M j, Y') : '—',
            'total_sessions' => (int) $p->total_sessions,
            'sessions_done' => (int) $p->used_sessions,
            'display_status' => $displayStatus,
            'display_label' => $displayLabel,
        ];
    }

    protected function aftercareFromAppointmentNote(AppointmentNote $note): array
    {
        $appointment = $note->appointment;

        return [
            'source' => 'appointment',
            'record_id' => (int) $note->id,
            'title' => (string) ($appointment?->service?->name ?: 'Appointment aftercare'),
            'subtitle' => 'Appointment '.($appointment?->appointment_no ?: '#'.$note->appointment_id),
            'instructions' => (string) $note->instructions,
            'updated_at' => optional($note->updated_at ?: $note->created_at)->toIso8601String(),
        ];
    }

    protected function aftercareFromTreatment(TreatmentPatientPackage $p): array
    {
        $treatment = $p->treatmentPackage;

        return [
            'source' => 'treatment',
            'record_id' => (int) $p->id,
            'title' => (string) ($treatment?->name ?: 'Treatment package aftercare'),
            'subtitle' => 'Treatment package #'.$p->id,
            'instructions' => (string) ($treatment->aftercare ?? ''),
            'updated_at' => optional($p->updated_at ?: $p->created_at)->toIso8601String(),
        ];
    }

    protected function aftercareFromMembership(PatientSubscription $s): array
    {
        $plan = $s->membershipPlan;

        return [
            'source' => 'membership',
            'record_id' => (int) $s->id,
            'title' => (string) ($plan?->name ?: 'Membership aftercare'),
            'subtitle' => 'Membership subscription #'.$s->id,
            'instructions' => (string) ($plan->aftercare ?? ''),
            'updated_at' => optional($s->updated_at ?: $s->created_at)->toIso8601String(),
        ];
    }

    protected function loadAftercareAppointment(int $patientId, int $record): ?array
    {
        $note = AppointmentNote::query()
            ->whereKey($record)
            ->whereHas('appointment', fn ($q) => $q->where('patient_id', $patientId))
            ->with(['appointment.service', 'appointment.doctor'])
            ->first();

        return $note && filled($note->instructions) ? $this->aftercareFromAppointmentNote($note) : null;
    }

    protected function loadAftercareTreatment(int $patientId, int $record): ?array
    {
        $pkg = TreatmentPatientPackage::query()
            ->whereKey($record)
            ->where('patient_id', $patientId)
            ->with('treatmentPackage')
            ->first();

        return $pkg && filled($pkg->treatmentPackage?->aftercare) ? $this->aftercareFromTreatment($pkg) : null;
    }

    protected function loadAftercareMembership(int $patientId, int $record): ?array
    {
        $sub = PatientSubscription::query()
            ->whereKey($record)
            ->where('patient_id', $patientId)
            ->with('membershipPlan')
            ->first();

        return $sub && filled($sub->membershipPlan?->aftercare) ? $this->aftercareFromMembership($sub) : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Shared helpers
    |--------------------------------------------------------------------------
    */

    /**
     * @return array{0: Payment|AppointmentPayment|null, 1: 'payment'|'appointment_payment'|null}
     */
    protected function resolveLatestPaymentPair(?Payment $payment, ?AppointmentPayment $invoice): array
    {
        if (! $payment && ! $invoice) {
            return [null, null];
        }
        if (! $payment) {
            return [$invoice, 'appointment_payment'];
        }
        if (! $invoice) {
            return [$payment, 'payment'];
        }

        $paymentAt = $payment->payment_date ?? $payment->created_at;
        $invoiceAt = $invoice->paid_at ?? $invoice->created_at;

        if ($paymentAt && $invoiceAt) {
            return $paymentAt->gte($invoiceAt)
                ? [$payment, 'payment']
                : [$invoice, 'appointment_payment'];
        }
        if ($paymentAt) {
            return [$payment, 'payment'];
        }
        if ($invoiceAt) {
            return [$invoice, 'appointment_payment'];
        }

        return $payment->id >= $invoice->id
            ? [$payment, 'payment']
            : [$invoice, 'appointment_payment'];
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected function resolveTreatmentDisplayStatus(TreatmentPatientPackage $p): array
    {
        $raw = strtolower((string) ($p->status ?? 'active'));
        $remaining = (int) $p->remaining_sessions;
        $used = (int) $p->used_sessions;

        if ($raw === 'cancelled') {
            return ['cancelled', 'Cancelled'];
        }
        if ($raw === 'completed' || $remaining <= 0) {
            return ['completed', 'Completed'];
        }
        if (in_array($raw, ['active', 'pending', 'ongoing'], true) && $used > 0 && $remaining > 0) {
            return ['ongoing', 'Ongoing'];
        }

        return ['pending', 'Pending'];
    }

    protected function ensurePatientOwnsAppointment(Request $request, Appointment $appointment): void
    {
        if ((int) $appointment->patient_id !== (int) $request->user('web')->id) {
            abort(404);
        }
    }

    protected function ensureCanChangeAppointment(Appointment $appointment): void
    {
        $status = $appointment->status ?? 'pending';
        $allowedStatuses = ['pending', 'confirmed', 'rescheduled'];

        if (! in_array($status, $allowedStatuses, true)) {
            abort(403, 'This appointment cannot be changed.');
        }

        $rawDate = $appointment->getRawOriginal('appointment_date');
        if ($rawDate && $rawDate < now()->toDateString()) {
            abort(403, 'Past appointments cannot be changed.');
        }
    }

    protected function removeStoredPatientAvatar(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        $normalized = ltrim($path, '/');

        if (str_starts_with($normalized, 'uploads/patients/')) {
            $fullPath = public_path($normalized);
            if (is_file($fullPath)) {
                unlink($fullPath);
            }

            return;
        }

        Storage::disk('public')->delete($normalized);
    }
}
