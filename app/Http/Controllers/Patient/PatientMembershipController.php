<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\PatientSubscription;
use App\Models\Payment;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class PatientMembershipController extends Controller
{
    public function index(): View
    {
        $patientId = Auth::id();

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

        return view('patient.membership.index', [
            'patient' => Auth::user(),
            'subscription' => $subscription,
            'previousMemberships' => $previousMemberships,
            'latestMembershipPayment' => $latestMembershipPayment,
        ]);
    }
}
