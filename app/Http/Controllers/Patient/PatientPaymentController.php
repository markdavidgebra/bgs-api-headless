<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\AppointmentPayment;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PatientPaymentController extends Controller
{
    public function index(): View
    {
        $patientId = Auth::id();

        $paymentScope = AppointmentPayment::query()
            ->whereHas('appointment', fn ($q) => $q->where('patient_id', $patientId));

        $totals = [
            'record_count' => (clone $paymentScope)->count(),
            'paid_sum' => (clone $paymentScope)->where('is_paid', true)->sum('amount'),
            'outstanding_sum' => (clone $paymentScope)->where('is_paid', false)->sum('amount'),
            'pending_sum' => (clone $paymentScope)->where('payment_status', 'pending')->sum('amount'),
            'latest_paid_at' => (clone $paymentScope)->whereNotNull('paid_at')->max('paid_at'),
        ];

        $payments = AppointmentPayment::query()
            ->whereHas('appointment', fn ($q) => $q->where('patient_id', $patientId))
            ->with(['appointment.clinicalStaff', 'appointment.service'])
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('patient.payments.index', [
            'totals' => $totals,
            'payments' => $payments,
        ]);
    }

    public function show(Request $request, int $payment): View
    {
        $patientId = Auth::id();

        $paymentRecord = AppointmentPayment::query()
            ->whereKey($payment)
            ->whereHas('appointment', fn ($q) => $q->where('patient_id', $patientId))
            ->with(['appointment.clinicalStaff', 'appointment.service'])
            ->firstOrFail();

        return view('patient.payments.show', [
            'payment' => $paymentRecord,
        ]);
    }
}
