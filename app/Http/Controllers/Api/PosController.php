<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MembershipPlan;
use App\Models\Patient;
use App\Models\PatientSubscription;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Service;
use App\Models\StockMovement;
use App\Models\TreatmentPackage;
use App\Models\TreatmentPatientPackage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PosController extends Controller
{
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

        if (! Auth::guard('admin')->attempt($credentials, true)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        $request->session()->regenerate();

        $admin = Auth::guard('admin')->user();
        if (! $admin || ! in_array((string) $admin->role, ['admin', 'cashier'], true)) {
            Auth::guard('admin')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json([
                'message' => 'Forbidden.',
            ], 403);
        }

        return response()->json([
            'message' => 'POS login successful.',
            'csrf_token' => csrf_token(),
            'admin' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => $admin->role,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logged out.',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $admin = $request->user('admin');

        return response()->json([
            'csrf_token' => csrf_token(),
            'admin' => [
                'id' => $admin?->id,
                'name' => $admin?->name,
                'email' => $admin?->email,
                'role' => $admin?->role,
            ],
        ]);
    }

    public function catalog(Request $request): JsonResponse
    {
        $type = $request->string('type')->toString();
        $search = trim($request->string('search')->toString());
        $limit = max(1, min((int) $request->integer('limit', 50), 200));

        $payload = [];

        if ($type === '' || $type === 'product') {
            $payload['products'] = Product::query()
                ->where('status', 'active')
                ->where('is_available_for_sale', true)
                ->when($search !== '', function ($q) use ($search) {
                    $q->where(function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%")
                            ->orWhere('brand', 'like', "%{$search}%");
                    });
                })
                ->orderBy('name')
                ->limit($limit)
                ->get()
                ->map(fn (Product $p) => [
                    'type' => 'product',
                    'id' => $p->id,
                    'name' => $p->name,
                    'sku' => $p->sku,
                    'price' => (float) $p->final_price,
                    'stock_quantity' => (int) $p->stock_quantity,
                    'unit' => $p->unit,
                    'stock_status' => $p->stock_status,
                ])
                ->values();
        }

        if ($type === '' || $type === 'service') {
            $payload['services'] = Service::query()
                ->where('status', 'active')
                ->where('is_bookable', true)
                ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                ->orderBy('name')
                ->limit($limit)
                ->get()
                ->map(fn (Service $s) => [
                    'type' => 'service',
                    'id' => $s->id,
                    'name' => $s->name,
                    'price' => (float) ($s->promo_price ?? $s->price ?? 0),
                    'duration_minutes' => $s->duration_minutes,
                ])
                ->values();
        }

        if ($type === '' || $type === 'package') {
            $payload['packages'] = TreatmentPackage::query()
                ->where('status', 'active')
                ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                ->orderBy('name')
                ->limit($limit)
                ->get()
                ->map(fn (TreatmentPackage $p) => [
                    'type' => 'package',
                    'id' => $p->id,
                    'name' => $p->name,
                    'price' => (float) ($p->price ?? 0),
                    'validity_label' => $p->validity_label,
                ])
                ->values();
        }

        if ($type === '' || $type === 'membership') {
            $payload['memberships'] = MembershipPlan::query()
                ->where('status', 'active')
                ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                ->orderBy('name')
                ->limit($limit)
                ->get()
                ->map(fn (MembershipPlan $m) => [
                    'type' => 'membership',
                    'id' => $m->id,
                    'name' => $m->name,
                    'price' => (float) ($m->price ?? 0),
                    'duration_label' => $m->duration_label,
                ])
                ->values();
        }

        return response()->json($payload);
    }

    public function patients(Request $request): JsonResponse
    {
        $search = trim($request->string('search')->toString());
        $limit = max(1, min((int) $request->integer('limit', 30), 100));

        $patients = Patient::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'name', 'email', 'phone', 'status']);

        return response()->json([
            'patients' => $patients,
        ]);
    }

    public function promotions(): JsonResponse
    {
        $promotions = Promotion::query()
            ->where('status', 'active')
            ->where(function ($q) {
                $today = now()->toDateString();
                $q->where(function ($q2) use ($today) {
                    $q2->whereNull('start_date')->orWhereDate('start_date', '<=', $today);
                })->where(function ($q2) use ($today) {
                    $q2->whereNull('end_date')->orWhereDate('end_date', '>=', $today);
                });
            })
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Promotion $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'code' => $p->code,
                'discount_label' => $p->discount_label,
                'applies_to' => $p->applies_to,
                'start_date' => optional($p->start_date)->toDateString(),
                'end_date' => optional($p->end_date)->toDateString(),
            ])
            ->values();

        return response()->json([
            'promotions' => $promotions,
        ]);
    }

    public function checkout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'integer', 'exists:users,id'],
            'payment_method' => ['required', 'string', 'in:cash,gcash,maya,card,bank_transfer'],
            'payment_status' => ['nullable', 'string', 'in:paid,unpaid,partial,refunded,cancelled'],
            'payment_date' => ['nullable', 'date'],
            'transaction_reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.type' => ['required', 'string', 'in:product,service,package,membership'],
            'items.*.id' => ['required', 'integer'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $paymentDate = $validated['payment_date'] ?? now()->toDateString();
        $paymentStatus = $validated['payment_status'] ?? 'paid';
        $cashierTxnRef = $validated['transaction_reference'] ?? null;
        $notes = $validated['notes'] ?? null;
        $items = $validated['items'];

        $this->validatePackageAndMembershipBeforeCheckout(
            (int) $validated['patient_id'],
            $items
        );

        $created = DB::transaction(function () use ($validated, $items, $paymentDate, $paymentStatus, $cashierTxnRef, $notes) {
            $payments = [];
            $totals = [
                'subtotal' => 0.0,
                'total_items' => 0,
            ];

            foreach ($items as $row) {
                $type = $row['type'];
                $recordId = (int) $row['id'];
                $quantity = (int) ($row['quantity'] ?? 1);

                [$model, $referenceType, $defaultUnitPrice] = $this->resolveReferenceForPos($type, $recordId);

                if ($type === 'product') {
                    /** @var Product $model */
                    if ($model->stock_quantity < $quantity) {
                        throw ValidationException::withMessages([
                            'items' => ["Insufficient stock for {$model->name}. Requested {$quantity}, available {$model->stock_quantity}."],
                        ]);
                    }
                }

                $unitPrice = array_key_exists('unit_price', $row)
                    ? (float) $row['unit_price']
                    : $defaultUnitPrice;

                $lineAmount = round($unitPrice * $quantity, 2);

                $payment = Payment::query()->create([
                    'payment_id' => Payment::generatePaymentId(),
                    'patient_id' => $validated['patient_id'],
                    'reference_type' => $referenceType,
                    'reference_id' => $model->id,
                    'amount' => $lineAmount,
                    'payment_method' => $validated['payment_method'],
                    'payment_status' => $paymentStatus,
                    'payment_date' => $paymentDate,
                    'transaction_reference' => $cashierTxnRef,
                    'notes' => $notes,
                ]);

                if ($type === 'product') {
                    /** @var Product $model */
                    $model->decrement('stock_quantity', $quantity);

                    StockMovement::query()->create([
                        'product_id' => $model->id,
                        'type' => 'out',
                        'quantity' => $quantity,
                        'reference' => $payment->payment_id,
                        'notes' => 'POS sale checkout',
                    ]);
                }

                if ($type === 'membership') {
                    /** @var MembershipPlan $model */
                    $this->createMembershipSubscriptionFromPos(
                        (int) $validated['patient_id'],
                        $model,
                        (string) $paymentDate,
                        $payment->payment_id,
                        $notes
                    );
                }

                if ($type === 'package') {
                    /** @var TreatmentPackage $model */
                    $this->createPatientPackageFromPos(
                        (int) $validated['patient_id'],
                        $model,
                        (string) $paymentDate,
                        $payment->payment_id,
                        $notes
                    );
                }

                $payments[] = [
                    'payment_id' => $payment->payment_id,
                    'reference_type' => $payment->reference_type,
                    'reference_id' => $payment->reference_id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'amount' => $lineAmount,
                ];

                $totals['subtotal'] += $lineAmount;
                $totals['total_items'] += $quantity;
            }

            $totals['subtotal'] = round($totals['subtotal'], 2);

            return [$payments, $totals];
        });

        return response()->json([
            'message' => 'POS checkout completed.',
            'payments' => $created[0],
            'totals' => $created[1],
        ], 201);
    }

    /**
     * @return array{0:Model, 1:string, 2:float}
     */
    private function resolveReferenceForPos(string $type, int $id): array
    {
        return match ($type) {
            'product' => $this->resolveProductReference($id),
            'service' => $this->resolveServiceReference($id),
            'package' => $this->resolvePackageReference($id),
            'membership' => $this->resolveMembershipReference($id),
            default => throw ValidationException::withMessages([
                'items' => ['Unsupported POS item type.'],
            ]),
        };
    }

    /**
     * @return array{0:Product, 1:string, 2:float}
     */
    private function resolveProductReference(int $id): array
    {
        $product = Product::query()
            ->whereKey($id)
            ->where('status', 'active')
            ->where('is_available_for_sale', true)
            ->first();

        if (! $product) {
            throw ValidationException::withMessages([
                'items' => ["Product #{$id} is not available for POS sale."],
            ]);
        }

        return [$product, 'product', (float) $product->final_price];
    }

    /**
     * @return array{0:Service, 1:string, 2:float}
     */
    private function resolveServiceReference(int $id): array
    {
        $service = Service::query()
            ->whereKey($id)
            ->where('status', 'active')
            ->where('is_bookable', true)
            ->first();

        if (! $service) {
            throw ValidationException::withMessages([
                'items' => ["Service #{$id} is not available for POS sale."],
            ]);
        }

        return [$service, 'appointment', (float) ($service->promo_price ?? $service->price ?? 0)];
    }

    /**
     * @return array{0:TreatmentPackage, 1:string, 2:float}
     */
    private function resolvePackageReference(int $id): array
    {
        $package = TreatmentPackage::query()
            ->whereKey($id)
            ->where('status', 'active')
            ->first();

        if (! $package) {
            throw ValidationException::withMessages([
                'items' => ["Package #{$id} is not available for POS sale."],
            ]);
        }

        return [$package, 'package', (float) ($package->price ?? 0)];
    }

    /**
     * @return array{0:MembershipPlan, 1:string, 2:float}
     */
    private function resolveMembershipReference(int $id): array
    {
        $membership = MembershipPlan::query()
            ->whereKey($id)
            ->where('status', 'active')
            ->first();

        if (! $membership) {
            throw ValidationException::withMessages([
                'items' => ["Membership #{$id} is not available for POS sale."],
            ]);
        }

        return [$membership, 'membership', (float) ($membership->price ?? 0)];
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function validatePackageAndMembershipBeforeCheckout(int $patientId, array $items): void
    {
        $membershipRows = array_values(array_filter($items, static fn (array $row): bool => ($row['type'] ?? null) === 'membership'));
        if (count($membershipRows) > 1) {
            throw ValidationException::withMessages([
                'items' => ['Only one membership item is allowed per checkout.'],
            ]);
        }

        if (count($membershipRows) === 1) {
            $targetMembershipId = (int) ($membershipRows[0]['id'] ?? 0);
            $currentActive = PatientSubscription::query()
                ->where('patient_id', $patientId)
                ->where('status', 'active')
                ->latest('id')
                ->first();

            if ($currentActive && (int) $currentActive->membership_plan_id === $targetMembershipId) {
                throw ValidationException::withMessages([
                    'items' => ['Patient already has this membership as active. Choose a different plan for upgrade/downgrade.'],
                ]);
            }
        }

        $packageRows = array_values(array_filter($items, static fn (array $row): bool => ($row['type'] ?? null) === 'package'));
        if (count($packageRows) > 1) {
            throw ValidationException::withMessages([
                'items' => ['Only one package item is allowed per checkout.'],
            ]);
        }

        if (count($packageRows) === 1) {
            $targetPackageId = (int) ($packageRows[0]['id'] ?? 0);
            $hasActivePackage = TreatmentPatientPackage::query()
                ->where('patient_id', $patientId)
                ->where('status', 'active')
                ->where('treatment_package_id', $targetPackageId)
                ->exists();

            if ($hasActivePackage) {
                throw ValidationException::withMessages([
                    'items' => ['Patient already has this package as active.'],
                ]);
            }
        }
    }

    private function createMembershipSubscriptionFromPos(
        int $patientId,
        MembershipPlan $plan,
        string $startDate,
        string $paymentId,
        ?string $notes
    ): void {
        $existingActive = PatientSubscription::query()
            ->where('patient_id', $patientId)
            ->where('status', 'active')
            ->with('membershipPlan:id,name,price')
            ->latest('id')
            ->first();

        if ($existingActive) {
            $currentPrice = (float) ($existingActive->membershipPlan?->price ?? 0);
            $newPrice = (float) ($plan->price ?? 0);
            $changeType = $newPrice > $currentPrice
                ? 'upgrade'
                : ($newPrice < $currentPrice ? 'downgrade' : 'plan change');

            $existingNotes = trim((string) ($existingActive->notes ?? ''));
            $transitionNote = "Closed by POS {$changeType} to {$plan->name} ({$paymentId}).";

            $existingActive->update([
                'status' => 'cancelled',
                'end_date' => $startDate,
                'notes' => $existingNotes !== ''
                    ? $existingNotes.' | '.$transitionNote
                    : $transitionNote,
            ]);
        }

        $startsAt = now()->parse($startDate)->startOfDay();

        $renewalDate = match ($plan->billing_cycle) {
            'quarterly' => $startsAt->copy()->addMonths(3),
            'yearly' => $startsAt->copy()->addYear(),
            default => $startsAt->copy()->addMonth(),
        };

        $endDate = $renewalDate->copy();
        if (! empty($plan->duration_value) && ! empty($plan->duration_type)) {
            $endDate = $plan->duration_type === 'year'
                ? $startsAt->copy()->addYears((int) $plan->duration_value)
                : $startsAt->copy()->addMonths((int) $plan->duration_value);
        }

        $includedSessions = (int) DB::table('membership_plan_service')
            ->where('membership_plan_id', $plan->id)
            ->sum('sessions');

        // Fallback to monthly cap when pivot sessions are not configured.
        if ($includedSessions <= 0 && $plan->max_usage_per_month !== null) {
            $includedSessions = (int) $plan->max_usage_per_month;
        }

        $noteParts = [
            'POS membership checkout',
            'payment '.$paymentId,
        ];
        if ($notes) {
            $noteParts[] = trim($notes);
        }

        PatientSubscription::query()->create([
            'patient_id' => $patientId,
            'membership_plan_id' => $plan->id,
            'start_date' => $startsAt->toDateString(),
            'renewal_date' => $renewalDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'status' => 'active',
            'sessions_used' => 0,
            'sessions_remaining' => max(0, $includedSessions),
            'notes' => implode(' | ', $noteParts),
        ]);
    }

    private function createPatientPackageFromPos(
        int $patientId,
        TreatmentPackage $package,
        string $purchaseDate,
        string $paymentId,
        ?string $notes
    ): void {
        $purchasedAt = now()->parse($purchaseDate)->startOfDay();

        $endDate = null;
        if (! empty($package->validity_value) && ! empty($package->validity_type)) {
            $endDateCarbon = $package->validity_type === 'year'
                ? $purchasedAt->copy()->addYears((int) $package->validity_value)
                : $purchasedAt->copy()->addMonths((int) $package->validity_value);
            $endDate = $endDateCarbon->toDateString();
        }

        $totalSessions = (int) DB::table('treatment_service_package')
            ->where('treatment_package_id', $package->id)
            ->sum('sessions');

        $noteParts = [
            'POS package checkout',
            'payment '.$paymentId,
        ];
        if ($notes) {
            $noteParts[] = trim($notes);
        }

        TreatmentPatientPackage::query()->create([
            'patient_id' => $patientId,
            'treatment_package_id' => $package->id,
            'purchased_at' => $purchasedAt->toDateString(),
            'start_date' => $purchasedAt->toDateString(),
            'end_date' => $endDate,
            'status' => 'active',
            'total_sessions' => max(0, $totalSessions),
            'used_sessions' => 0,
            'remaining_sessions' => max(0, $totalSessions),
            'notes' => implode(' | ', $noteParts),
        ]);
    }
}
