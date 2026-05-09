<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentNote;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DoctorAppointmentController extends Controller
{
    public function index(Request $request): View
    {
        $doctor = auth('doctor')->user();
        $today = now()->toDateString();

        $dateFilter = $request->string('date_filter')->toString() ?: 'today';
        $status = $request->string('status')->toString();
        $search = trim($request->string('search')->toString());
        $customDate = $request->string('custom_date')->toString();
        $viewMode = $request->string('view')->toString() ?: 'table';

        $baseQuery = Appointment::query()
            ->with(['patient:id,name', 'service:id,name', 'note'])
            ->where('doctor_id', $doctor?->id);

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
        $appointment = $this->ownedAppointment($appointment)->load(['patient', 'service', 'note', 'timelines', 'prescribedProducts']);

        return view('doctor.appointments.show', compact('appointment'));
    }

    public function createNotes(Appointment $appointment): View
    {
        $appointment = $this->ownedAppointment($appointment)->load(['patient', 'service', 'note', 'prescribedProducts']);
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

    public function startSession(Appointment $appointment): RedirectResponse
    {
        $appointment = $this->ownedAppointment($appointment);

        if ($appointment->status === 'pending' || $appointment->status === 'rescheduled') {
            $appointment->update(['status' => 'confirmed']);
        }

        return back()->with('success', 'Session started successfully.');
    }

    public function markCompleted(Appointment $appointment): RedirectResponse
    {
        $appointment = $this->ownedAppointment($appointment);
        $appointment->update(['status' => 'completed']);

        return back()->with('success', 'Appointment marked as completed.');
    }

    public function markNoShow(Appointment $appointment): RedirectResponse
    {
        $appointment = $this->ownedAppointment($appointment);
        $appointment->update(['status' => 'cancelled']);

        return back()->with('success', 'Appointment marked as no-show.');
    }

    public function addNotes(Request $request, Appointment $appointment): RedirectResponse
    {
        $appointment = $this->ownedAppointment($appointment);

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

        AppointmentNote::query()->updateOrCreate(
            ['appointment_id' => $appointment->id],
            [
                'patient_concern' => $validated['patient_concern'] ?? $existingNote?->patient_concern,
                'appointment_remarks' => $validated['appointment_remarks'] ?? $validated['procedure_done'] ?? $existingNote?->appointment_remarks,
                'admin_notes' => $validated['admin_notes'] ?? $existingNote?->admin_notes,
                'doctor_notes' => $validated['doctor_notes'] ?? $validated['observations'] ?? $existingNote?->doctor_notes,
                'instructions' => $validated['instructions'] ?? $validated['recommendation'] ?? $existingNote?->instructions,
                'alerts' => $validated['alerts'] ?? $validated['follow_up_needed'] ?? $existingNote?->alerts,
            ]
        );

        $appointment->prescribedProducts()->sync($prescribeSync);

        return back()->with('success', 'Notes added successfully.');
    }

    public function reschedule(Request $request, Appointment $appointment): RedirectResponse
    {
        $appointment = $this->ownedAppointment($appointment);

        $validated = $request->validate([
            'appointment_date' => ['required', 'date'],
            'appointment_time' => ['required', 'date_format:H:i'],
        ]);

        $appointment->update([
            'appointment_date' => $validated['appointment_date'],
            'appointment_time' => $validated['appointment_time'],
            'status' => 'rescheduled',
        ]);

        return back()->with('success', 'Appointment rescheduled successfully.');
    }

    private function ownedAppointment(Appointment $appointment): Appointment
    {
        abort_unless((int) $appointment->doctor_id === (int) auth('doctor')->id(), 403);

        return $appointment;
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
