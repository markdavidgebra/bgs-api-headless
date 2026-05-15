<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\MembershipPlan;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Service;
use App\Models\TreatmentPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentsController extends Controller
{
    public function index(Request $request): View
    {
        $base = Payment::query();

        $totalRevenue = (float) (clone $base)->whereIn('payment_status', ['paid', 'partial'])->sum('amount');
        $todaysRevenue = (float) (clone $base)
            ->whereIn('payment_status', ['paid', 'partial'])
            ->whereDate('payment_date', now()->toDateString())
            ->sum('amount');
        $pendingPayments = (clone $base)->whereIn('payment_status', ['unpaid', 'cancelled'])->count();
        $partialPayments = (clone $base)->where('payment_status', 'partial')->count();
        $refundedPayments = (clone $base)->where('payment_status', 'refunded')->count();

        $membershipRevenue = (float) (clone $base)
            ->where('reference_type', 'membership')
            ->whereIn('payment_status', ['paid', 'partial'])
            ->sum('amount');
        $packageRevenue = (float) (clone $base)
            ->where('reference_type', 'package')
            ->whereIn('payment_status', ['paid', 'partial'])
            ->sum('amount');

        $query = Payment::query()
            ->with(['patient:id,name,email'])
            ->orderByDesc('payment_date')
            ->orderByDesc('id');

        if ($request->filled('search')) {
            $term = $request->string('search')->toString();
            $query->where(function ($q) use ($term) {
                $q->where('payment_id', 'like', "%{$term}%")
                    ->orWhere('transaction_reference', 'like', "%{$term}%")
                    ->orWhereHas('patient', function ($q2) use ($term) {
                        $q2->where('name', 'like', "%{$term}%")
                            ->orWhere('email', 'like', "%{$term}%");
                    });
            });
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->string('payment_status')->toString());
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->string('payment_method')->toString());
        }

        if ($request->filled('date')) {
            $query->whereDate('payment_date', $request->date('date'));
        }

        if ($request->filled('reference_type')) {
            $query->where('reference_type', $request->string('reference_type')->toString());
        }

        $payments = $query->paginate(15)->withQueryString();

        return view('admin.payments.index', compact(
            'payments',
            'totalRevenue',
            'todaysRevenue',
            'pendingPayments',
            'partialPayments',
            'refundedPayments',
            'membershipRevenue',
            'packageRevenue',
        ));
    }

    public function show(string $id): View
    {
        $payment = Payment::query()
            ->with(['patient:id,name,email,phone'])
            ->where(function ($q) use ($id) {
                $q->where('id', $id)->orWhere('payment_id', $id);
            })
            ->firstOrFail();

        match ($payment->reference_type) {
            'appointment' => $payment->load([
                'referenceAppointment' => static fn ($q) => $q->with([
                    'service:id,name',
                    'doctor:id,name',
                ]),
            ]),
            'package' => $payment->load('referencePackage:id,name'),
            'membership' => $payment->load('referenceMembership:id,name'),
            'product' => $payment->load('referenceProduct:id,name'),
            'service' => $payment->load('referenceService:id,name'),
            default => null,
        };

        return view('admin.payments.show', compact('payment'));
    }

    public function create(): View
    {
        $patients = Patient::query()
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $appointments = Appointment::query()
            ->with(['service:id,name'])
            ->orderByDesc('appointment_date')
            ->orderByDesc('id')
            ->limit(100)
            ->get(['id', 'appointment_no', 'service_id', 'patient_id', 'appointment_date']);

        $packages = TreatmentPackage::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $memberships = MembershipPlan::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $products = Product::query()
            ->orderBy('name')
            ->get(['id', 'name', 'sku']);

        return view('admin.payments.create', compact(
            'patients',
            'appointments',
            'packages',
            'memberships',
            'products',
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $ref = $request->input('reference_id');
        $request->merge([
            'reference_id' => ($ref === '' || $ref === null) ? null : (int) $ref,
        ]);

        $validated = $request->validate([
            'patient_id' => ['required', 'integer', 'exists:users,id'],
            'reference_type' => ['required', 'string', 'in:appointment,service,package,membership,product'],
            'reference_id' => ['nullable', 'integer'],
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', 'string', 'in:cash,gcash,maya,card,bank_transfer'],
            'payment_status' => ['required', 'string', 'in:paid,unpaid,partial,refunded,cancelled'],
            'payment_date' => ['nullable', 'date'],
            'transaction_reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $referenceId = $validated['reference_id'] ?? null;
        if ($referenceId !== null) {
            $exists = match ($validated['reference_type']) {
                'appointment' => Appointment::query()->whereKey($referenceId)->exists(),
                'service' => Service::query()->whereKey($referenceId)->exists(),
                'package' => TreatmentPackage::query()->whereKey($referenceId)->exists(),
                'membership' => MembershipPlan::query()->whereKey($referenceId)->exists(),
                'product' => Product::query()->whereKey($referenceId)->exists(),
                default => false,
            };
            if (! $exists) {
                return back()
                    ->withErrors(['reference_id' => __('The selected reference does not exist for this type.')])
                    ->withInput();
            }
        }

        $payment = Payment::query()->create([
            'payment_id' => Payment::generatePaymentId(),
            'patient_id' => $validated['patient_id'],
            'reference_type' => $validated['reference_type'],
            'reference_id' => $referenceId,
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'payment_status' => $validated['payment_status'],
            'payment_date' => $validated['payment_date'] ?? null,
            'transaction_reference' => $validated['transaction_reference'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('admin.payments.show', $payment->id)
            ->with('status', __('Payment recorded.'));
    }
}
