<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Admin\PatientsController;
use App\Http\Controllers\Api\Concerns\AdminPortalResponses;
use App\Http\Controllers\Api\Concerns\ConvertsAdminWebResponses;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentPayment;
use App\Models\Patient;
use App\Models\PatientSubscription;
use App\Models\Payment;
use App\Models\TreatmentPackage;
use App\Models\TreatmentPackageUsageHistory;
use App\Models\TreatmentPatientPackage;
use App\Support\AdminPermissions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminPatientsController extends Controller
{
    use AdminPortalResponses;
    use ConvertsAdminWebResponses;

    public function index(Request $request): JsonResponse
    {
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

        $perPage = max(1, min((int) $request->integer('limit', 15), 100));
        $paginator = $query->paginate($perPage)->withQueryString();

        return response()->json([
            'data' => $paginator->getCollection()
                ->map(fn (Patient $p) => $this->patientDetailPayload($p))
                ->values(),
            'meta' => $this->paginationMeta($paginator),
            'can_manage_status' => $this->canManageStatus(),
            'can_manage_records' => AdminPermissions::canAccess(auth('admin')->user(), 'patients.manage'),
        ]);
    }

    public function create(): JsonResponse
    {
        return response()->json([
            'can_manage_status' => $this->canManageStatus(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        return $this->adminWebJson(
            app(PatientsController::class)->store($request),
            201
        );
    }

    public function show(int $id): JsonResponse
    {
        $patient = Patient::query()->findOrFail($id);

        $appointments = Appointment::query()
            ->with(['service:id,name', 'doctor:id,name', 'note'])
            ->where('patient_id', $patient->id)
            ->orderByDesc('appointment_date')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Appointment $a) => $this->appointmentPayload($a))
            ->values();

        $appointmentPayments = AppointmentPayment::query()
            ->whereHas('appointment', fn ($q) => $q->where('patient_id', $patient->id))
            ->with('appointment:id,appointment_no,appointment_date')
            ->orderByDesc('id')
            ->get()
            ->map(fn ($p) => $this->appointmentPaymentPayload($p))
            ->values();

        $payments = Payment::query()
            ->where('patient_id', $patient->id)
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Payment $p) => $this->paymentPayload($p))
            ->values();

        $subscriptions = PatientSubscription::query()
            ->with('membershipPlan:id,name,price,billing_cycle')
            ->where('patient_id', $patient->id)
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->get()
            ->map(fn (PatientSubscription $s) => [
                'id' => $s->id,
                'status' => $s->status,
                'start_date' => $s->start_date?->toDateString(),
                'end_date' => $s->end_date?->toDateString(),
                'renewal_date' => $s->renewal_date?->toDateString(),
                'plan' => $s->membershipPlan ? [
                    'id' => $s->membershipPlan->id,
                    'name' => $s->membershipPlan->name,
                ] : null,
            ])
            ->values();

        $patientPackages = TreatmentPatientPackage::query()
            ->with('treatmentPackage:id,name')
            ->where('patient_id', $patient->id)
            ->orderByDesc('purchased_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (TreatmentPatientPackage $pp) => [
                'id' => $pp->id,
                'sessions_total' => $pp->sessions_total,
                'sessions_used' => $pp->sessions_used,
                'package' => $pp->treatmentPackage ? [
                    'id' => $pp->treatmentPackage->id,
                    'name' => $pp->treatmentPackage->name,
                ] : null,
            ])
            ->values();

        return response()->json([
            'patient' => $this->patientDetailPayload($patient),
            'appointments' => $appointments,
            'appointment_payments' => $appointmentPayments,
            'payments' => $payments,
            'subscriptions' => $subscriptions,
            'treatment_packages' => $patientPackages,
            'can_manage_status' => $this->canManageStatus(),
            'can_manage_records' => AdminPermissions::canAccess(auth('admin')->user(), 'patients.manage'),
        ]);
    }

    public function edit(int $id): JsonResponse
    {
        $patient = Patient::query()->findOrFail($id);

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

        return response()->json([
            'patient' => $this->patientDetailPayload($patient),
            'can_manage_status' => $this->canManageStatus(),
            'treatment_packages_for_assign' => $treatmentPackagesForAssign,
            'patient_packages' => $patientPackages,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        return $this->adminWebJson(
            app(PatientsController::class)->update($request, $id)
        );
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        return $this->adminWebJson(
            app(PatientsController::class)->updateStatus($request, $id)
        );
    }

    public function destroy(int $id): JsonResponse
    {
        return $this->adminWebJson(
            app(PatientsController::class)->destroy($id)
        );
    }

    public function updatePassword(Request $request, int $id): JsonResponse
    {
        return $this->adminWebJson(
            app(PatientsController::class)->updatePassword($request, $id)
        );
    }

    public function sendPasswordReset(int $id): JsonResponse
    {
        return $this->adminWebJson(
            app(PatientsController::class)->sendPasswordReset($id)
        );
    }

    public function storeTreatmentPackage(Request $request, int $id): JsonResponse
    {
        return $this->adminWebJson(
            app(PatientsController::class)->storePatientTreatmentPackage($request, $id),
            201
        );
    }

    public function upsertAppointmentNote(Request $request, int $patient, int $appointment): JsonResponse
    {
        return $this->adminWebJson(
            app(PatientsController::class)->upsertAppointmentNote($request, $patient, $appointment)
        );
    }

    public function clearAppointmentNoteField(int $patient, int $appointment, string $field): JsonResponse
    {
        return $this->adminWebJson(
            app(PatientsController::class)->clearAppointmentNoteField($patient, $appointment, $field)
        );
    }

    public function destroyAppointmentNote(int $patient, int $appointment): JsonResponse
    {
        return $this->adminWebJson(
            app(PatientsController::class)->destroyAppointmentNote($patient, $appointment)
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function patientDetailPayload(Patient $patient): array
    {
        return array_merge($this->patientSummaryPayload($patient), [
            'birthdate' => $patient->birthdate?->toDateString(),
            'gender' => $patient->gender,
            'address' => $patient->address,
            'emergency_contact' => $patient->emergency_contact,
            'history_summary' => $patient->history_summary,
            'created_at' => $patient->created_at?->toIso8601String(),
        ]);
    }

    private function canManageStatus(): bool
    {
        $admin = auth('admin')->user();

        return $admin && in_array(strtolower((string) $admin->role), ['super admin', 'superadmin', 'admin'], true);
    }
}
