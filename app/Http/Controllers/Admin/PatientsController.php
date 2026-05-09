<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentNote;
use App\Models\AppointmentPayment;
use App\Models\Patient;
use App\Models\PatientSubscription;
use App\Models\Payment;
use App\Models\TreatmentPackageUsageHistory;
use App\Models\TreatmentPatientPackage;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientsController extends Controller
{
    public function index(Request $request): View
    {
        $query = Patient::query()->orderBy('name');

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

        return view('admin.patients.index', compact('patients'));
    }

    public function show(int $id): View
    {
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
            ->with(['service:id,name', 'doctor:id,name'])
            ->where('patient_id', $patient->id)
            ->orderByDesc('appointment_date')
            ->orderByDesc('id')
            ->get();

        $appointmentNotes = AppointmentNote::query()
            ->whereHas('appointment', fn ($q) => $q->where('patient_id', $patient->id))
            ->with(['appointment:id,appointment_no,appointment_date,appointment_time,service_id', 'appointment.service:id,name'])
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

        return view('admin.patients.show', compact(
            'patient',
            'legacySubscription',
            'legacyAppointmentHistory',
            'legacyNotes',
            'appointments',
            'appointmentNotes',
            'appointmentPayments',
            'payments',
            'productOrders',
            'subscriptions',
            'patientPackages',
            'packageUsageHistory',
        ));
    }
}
