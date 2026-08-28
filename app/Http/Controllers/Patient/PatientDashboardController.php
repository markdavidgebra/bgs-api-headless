<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentPayment;
use App\Models\PatientSubscription;
use App\Models\Payment;
use App\Models\TreatmentPatientPackage;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class PatientDashboardController extends Controller
{
    public function index(): View
    {
        $patient = Auth::user();
        $patientId = $patient->id;

        $stats = [
            'total' => Appointment::where('patient_id', $patientId)->count(),
            'upcoming' => Appointment::where('patient_id', $patientId)
                ->whereIn('status', ['pending', 'confirmed', 'rescheduled'])
                ->whereDate('appointment_date', '>=', now()->toDateString())
                ->count(),
            'completed' => Appointment::where('patient_id', $patientId)->where('status', 'completed')->count(),
            'cancelled' => Appointment::where('patient_id', $patientId)->where('status', 'cancelled')->count(),
        ];

        $upcomingAppointment = Appointment::query()
            ->where('patient_id', $patientId)
            ->whereIn('status', ['pending', 'confirmed', 'rescheduled'])
            ->whereDate('appointment_date', '>=', now()->toDateString())
            ->with(['clinicalStaff', 'service'])
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->first();

        $activePackage = TreatmentPatientPackage::query()
            ->where('patient_id', $patientId)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', now()->toDateString());
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
            ->with('membershipPlan')
            ->orderByDesc('start_date')
            ->first();

        $tablePayment = Payment::query()
            ->where('patient_id', $patientId)
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->first();

        $appointmentInvoice = AppointmentPayment::query()
            ->whereHas('appointment', fn ($q) => $q->where('patient_id', $patientId))
            ->with('appointment')
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->first();

        $latestPaymentPair = $this->resolveLatestPaymentRecord($tablePayment, $appointmentInvoice);

        $recentAppointments = Appointment::query()
            ->where('patient_id', $patientId)
            ->with(['clinicalStaff', 'service'])
            ->orderByDesc('appointment_date')
            ->orderByDesc('appointment_time')
            ->limit(15)
            ->get();

        return view('patient.dashboard.index', [
            'patient' => $patient,
            'stats' => $stats,
            'recentAppointments' => $recentAppointments,
            'upcomingAppointment' => $upcomingAppointment,
            'activePackage' => $activePackage,
            'activeMembership' => $activeMembership,
            'latestPaymentRecord' => $latestPaymentPair[0] ?? null,
            'latestPaymentKind' => $latestPaymentPair[1] ?? null,
        ]);
    }

    /**
     * @return array{0: Payment|AppointmentPayment, 1: 'payment'|'appointment_payment'}|null
     */
    private function resolveLatestPaymentRecord(?Payment $payment, ?AppointmentPayment $invoice): ?array
    {
        if (! $payment && ! $invoice) {
            return null;
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
}
