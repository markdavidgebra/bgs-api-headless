<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Admin\PaymentsController;
use App\Http\Controllers\Api\Concerns\AdminPortalResponses;
use App\Http\Controllers\Api\Concerns\ConvertsAdminWebResponses;
use App\Http\Controllers\Controller;
use App\Models\PatientSubscription;
use App\Models\Payment;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\TreatmentPatientPackage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\DB;

class AdminPaymentsController extends Controller
{
    use AdminPortalResponses;
    use ConvertsAdminWebResponses;

    /**
     * One row per POS/checkout transaction — every payment line item sharing
     * the same `transaction_no` is grouped and summarized together, with the
     * full item breakdown available via `show()`.
     */
    public function index(Request $request): JsonResponse
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

        $query = $this->filteredPaymentsQuery($request)
            ->orderByDesc('payment_date')
            ->orderByDesc('id');

        $rows = $query->get();

        $transactions = $rows
            ->groupBy(fn (Payment $p) => $p->transaction_no ?: $p->payment_id)
            ->sortByDesc(fn (Collection|BaseCollection $items) => $this->groupSortKey($items))
            ->map(fn (Collection|BaseCollection $items) => $this->transactionSummary($items))
            ->values();

        $perPage = max(1, min((int) $request->integer('limit', 15), 100));
        $page = max(1, (int) $request->integer('page', 1));

        $paginator = new LengthAwarePaginator(
            $transactions->forPage($page, $perPage)->values(),
            $transactions->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()],
        );

        return response()->json([
            'data' => $paginator->items(),
            'meta' => $this->paginationMeta($paginator),
            'totalRevenue' => $totalRevenue,
            'todaysRevenue' => $todaysRevenue,
            'pendingPayments' => $pendingPayments,
            'partialPayments' => $partialPayments,
            'refundedPayments' => $refundedPayments,
            'membershipRevenue' => $membershipRevenue,
            'packageRevenue' => $packageRevenue,
        ]);
    }

    /**
     * `$id` is a transaction number (e.g. `TXN-0031`) from the grouped list.
     * Falls back to matching a single `payment_id`/numeric id for older links,
     * then resolves to that item's full transaction group.
     */
    public function show(string $id): JsonResponse
    {
        $items = $this->resolveTransactionRows($id, $this->paymentDetailQuery());

        return response()->json([
            'transaction' => $this->transactionSummary($items, detailed: true),
        ]);
    }

    public function create(): JsonResponse
    {
        return $this->viewDataJson(app(PaymentsController::class)->create());
    }

    public function store(Request $request): JsonResponse
    {
        return $this->adminWebJson(app(PaymentsController::class)->store($request), 201);
    }

    /**
     * Same payload as `show()` — used to prefill the edit form with the
     * transaction's current shared values and read-only item breakdown.
     */
    public function edit(string $id): JsonResponse
    {
        return $this->show($id);
    }

    /**
     * Only the fields shared across every line item in a checkout (method,
     * status, date, gateway/bank reference) are editable here. Per-item
     * amounts/references are left untouched to avoid desyncing POS-generated
     * stock movements, subscriptions, and treatment packages.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $items = $this->resolveTransactionRows($id, Payment::query());

        $validated = $request->validate([
            'payment_method' => ['required', 'string', 'in:cash,gcash,maya,card,bank_transfer'],
            'payment_status' => ['required', 'string', 'in:paid,unpaid,partial,refunded,cancelled'],
            'payment_date' => ['nullable', 'date'],
            'transaction_reference' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($items, $validated) {
            foreach ($items as $item) {
                $item->update([
                    'payment_method' => $validated['payment_method'],
                    'payment_status' => $validated['payment_status'],
                    'payment_date' => $validated['payment_date'] ?? $item->payment_date,
                    'transaction_reference' => $validated['transaction_reference'] ?? null,
                ]);
            }
        });

        $fresh = $items->fresh([
            'patient:id,name,email,phone',
            'referenceAppointment.service',
            'referenceAppointment.clinicalStaff',
            'referencePackage',
            'referenceMembership',
            'referenceProduct',
            'referenceService',
        ]);

        return response()->json([
            'message' => __('Transaction updated.'),
            'transaction' => $this->transactionSummary($fresh, detailed: true),
        ]);
    }

    /**
     * Deletes every line item in the transaction and reverses the POS side
     * effects tied to those specific payments (restocking products, removing
     * memberships/packages the checkout created). It cannot restore a
     * membership that the checkout replaced/cancelled.
     */
    public function destroy(string $id): JsonResponse
    {
        $items = $this->resolveTransactionRows($id, Payment::query());

        DB::transaction(function () use ($items) {
            foreach ($items as $item) {
                $this->reversePosSideEffects($item);
                $item->delete();
            }
        });

        return response()->json([
            'message' => __('Transaction deleted.'),
        ]);
    }

    /**
     * Resolves `$id` (a `transaction_no` like `TXN-0031`, or — for older
     * links — a `payment_id`/numeric id) to every Payment row belonging to
     * that same transaction.
     *
     * @return Collection<int, Payment>
     */
    private function resolveTransactionRows(string $id, Builder $query): Collection
    {
        $anchor = Payment::query()
            ->where('transaction_no', $id)
            ->orWhere('payment_id', $id)
            ->orWhere('id', $id)
            ->first();

        if (! $anchor) {
            abort(404);
        }

        $groupKey = $anchor->transaction_no ?: $anchor->payment_id;

        return $query
            ->where(function (Builder $q) use ($groupKey, $anchor) {
                $q->where('transaction_no', $groupKey);
                if (! $anchor->transaction_no) {
                    $q->orWhere('id', $anchor->id);
                }
            })
            ->orderBy('id')
            ->get();
    }

    /**
     * Undoes the POS checkout side effects created for this specific payment
     * line: restocks products via their matching `stock_movements` row, and
     * removes memberships/packages the checkout created (matched by the
     * `payment {payment_id}` marker `PosController` writes into their notes).
     */
    private function reversePosSideEffects(Payment $item): void
    {
        if ($item->reference_type === 'product' && $item->reference_id) {
            $movement = StockMovement::query()
                ->where('reference', $item->payment_id)
                ->where('type', 'out')
                ->first();

            if ($movement) {
                Product::query()->whereKey($item->reference_id)->increment('stock_quantity', $movement->quantity);
                $movement->delete();
            }
        }

        if ($item->reference_type === 'membership') {
            PatientSubscription::query()
                ->where('notes', 'like', '%payment '.$item->payment_id.'%')
                ->delete();
        }

        if ($item->reference_type === 'package') {
            TreatmentPatientPackage::query()
                ->where('notes', 'like', '%payment '.$item->payment_id.'%')
                ->delete();
        }
    }

    private function filteredPaymentsQuery(Request $request): Builder
    {
        $query = Payment::query()->with([
            'patient:id,name,email',
            'referenceAppointment.service',
            'referencePackage',
            'referenceMembership',
            'referenceProduct',
            'referenceService',
        ]);

        if ($request->filled('search')) {
            $term = $request->string('search')->toString();
            $query->where(function (Builder $q) use ($term) {
                $q->where('payment_id', 'like', "%{$term}%")
                    ->orWhere('transaction_no', 'like', "%{$term}%")
                    ->orWhere('transaction_reference', 'like', "%{$term}%")
                    ->orWhereHas('patient', function (Builder $q2) use ($term) {
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
            $type = $request->string('reference_type')->toString();
            // Filtering is per line item, but we still want the *whole*
            // transaction (all its items) once any item in it matches.
            $matchingKeys = (clone $query)
                ->where('reference_type', $type)
                ->pluck('transaction_no', 'payment_id');
            $keys = $matchingKeys->keys()->merge($matchingKeys->values())->unique()->filter()->values();
            $query->where(function (Builder $q) use ($keys) {
                $q->whereIn('transaction_no', $keys)->orWhereIn('payment_id', $keys);
            });
        }

        return $query;
    }

    private function paymentDetailQuery(): Builder
    {
        return Payment::query()->with([
            'patient:id,name,email,phone',
            'referenceAppointment.service',
            'referenceAppointment.clinicalStaff',
            'referencePackage',
            'referenceMembership',
            'referenceProduct',
            'referenceService',
        ]);
    }

    /**
     * Sortable key (payment date, then most recent row id as a tiebreak) used
     * to order transaction groups newest-first — mirrors the row-level
     * `orderByDesc('payment_date')->orderByDesc('id')` ordering used before
     * grouping existed.
     *
     * @param  Collection<int, Payment>|BaseCollection<int, Payment>  $items
     */
    private function groupSortKey(Collection|BaseCollection $items): string
    {
        $first = $items->first();
        $latestId = (int) $items->max('id');

        return ($first->payment_date?->format('Y-m-d') ?? '0000-00-00').'-'.str_pad((string) $latestId, 10, '0', STR_PAD_LEFT);
    }

    /**
     * @param  Collection<int, Payment>|BaseCollection<int, Payment>  $items
     * @return array<string, mixed>
     */
    private function transactionSummary(Collection|BaseCollection $items, bool $detailed = false): array
    {
        $first = $items->first();
        $methods = $items->pluck('payment_method')->filter()->unique();
        $statuses = $items->pluck('payment_status')->filter()->unique();

        $payload = [
            'transaction_no' => $first->transaction_no ?: $first->payment_id,
            'payment_ids' => $items->pluck('payment_id')->values(),
            'item_count' => $items->count(),
            'total_amount' => (float) $items->sum(fn (Payment $p) => (float) $p->amount),
            'patient' => $first->relationLoaded('patient') && $first->patient
                ? [
                    'id' => $first->patient->id,
                    'name' => $first->patient->name,
                    'email' => $first->patient->email,
                    'phone' => $first->patient->phone ?? null,
                ]
                : null,
            'payment_method' => $methods->count() === 1 ? $methods->first() : 'mixed',
            'payment_status' => $statuses->count() === 1 ? $statuses->first() : 'mixed',
            'payment_date' => $first->payment_date?->toDateString(),
            'created_at' => $items->min(fn (Payment $p) => $p->created_at)?->toIso8601String(),
            'affiliate_code' => $this->extractAffiliateCode($items),
            'items' => $items->map(fn (Payment $p) => [
                'id' => $p->id,
                'payment_id' => $p->payment_id,
                'reference_type' => $p->reference_type,
                'reference_type_label' => $p->reference_type_label,
                'reference_id' => $p->reference_id,
                'reference_name' => $p->reference_name,
                'amount' => (float) $p->amount,
                'payment_status' => $p->payment_status,
            ])->values(),
        ];

        if ($detailed) {
            $payload['transaction_reference'] = $items->pluck('transaction_reference')->filter()->first();
            $payload['notes'] = $items->pluck('notes')->filter()->unique()->values();
            $payload['assigned_clinical_staff'] = $items->pluck('assigned_clinical_staff_name')->filter()->first();
        }

        return $payload;
    }

    /**
     * POS checkout has no dedicated affiliate-code column — the code is
     * embedded as free text into a line item's `notes` (e.g.
     * "Affiliate code: WELCOME10" or "Cashier note | Affiliate code: WELCOME10 | POS service checkout").
     * Scan every item's notes for that segment and return the code, if any.
     *
     * @param  Collection<int, Payment>|BaseCollection<int, Payment>  $items
     */
    private function extractAffiliateCode(Collection|BaseCollection $items): ?string
    {
        foreach ($items as $item) {
            $notes = (string) ($item->notes ?? '');
            if ($notes === '') {
                continue;
            }

            if (preg_match('/Affiliate code:\s*([^|]+)/i', $notes, $matches)) {
                $code = trim($matches[1]);
                if ($code !== '') {
                    return $code;
                }
            }
        }

        return null;
    }
}
