<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Concerns\ConvertsAdminWebResponses;
use App\Http\Controllers\Controller;
use App\Mail\PromotionBlastMail;
use App\Models\MembershipPlan;
use App\Models\Patient;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Service;
use App\Models\TreatmentPackage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminPromotionsController extends Controller
{
    use ConvertsAdminWebResponses;

    private const STATUSES = [
        ['value' => 'draft', 'label' => 'Draft'],
        ['value' => 'active', 'label' => 'Active'],
        ['value' => 'scheduled', 'label' => 'Scheduled'],
        ['value' => 'expired', 'label' => 'Expired'],
        ['value' => 'inactive', 'label' => 'Inactive'],
    ];

    private const DISCOUNT_METHODS = [
        ['value' => 'percentage', 'label' => 'Percentage (%)'],
        ['value' => 'fixed', 'label' => 'Fixed amount (₱)'],
        ['value' => 'free_service', 'label' => 'Free service'],
        ['value' => 'bundle', 'label' => 'Bundle promo'],
    ];

    private const APPLIES_TO = [
        ['value' => 'services', 'label' => 'Services'],
        ['value' => 'packages', 'label' => 'Treatment packages'],
        ['value' => 'memberships', 'label' => 'Membership plans'],
        ['value' => 'products', 'label' => 'Products'],
        ['value' => 'all', 'label' => 'All catalog items'],
    ];

    private const AVAILABLE_DAYS = [
        ['value' => 'mon', 'label' => 'Mon'],
        ['value' => 'tue', 'label' => 'Tue'],
        ['value' => 'wed', 'label' => 'Wed'],
        ['value' => 'thu', 'label' => 'Thu'],
        ['value' => 'fri', 'label' => 'Fri'],
        ['value' => 'sat', 'label' => 'Sat'],
        ['value' => 'sun', 'label' => 'Sun'],
    ];

    public function index(Request $request): JsonResponse
    {
        $perPage = max(1, min((int) $request->integer('limit', 15), 100));

        $base = Promotion::query();
        $stats = [
            'total' => (clone $base)->count(),
            'active' => (clone $base)->where('status', 'active')->count(),
            'expired' => (clone $base)->where('status', 'expired')->count(),
            'draft' => (clone $base)->where('status', 'draft')->count(),
            'scheduled' => (clone $base)->where('status', 'scheduled')->count(),
            'inactive' => (clone $base)->where('status', 'inactive')->count(),
            'services' => (clone $base)->where('applies_to', 'services')->count(),
            'packages' => (clone $base)->where('applies_to', 'packages')->count(),
            'memberships' => (clone $base)->where('applies_to', 'memberships')->count(),
            'products' => (clone $base)->where('applies_to', 'products')->count(),
        ];

        $query = Promotion::query()
            ->withCount(['services', 'treatmentPackages', 'membershipPlans', 'products'])
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if ($request->filled('search')) {
            $term = $request->string('search')->toString();
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('code', 'like', "%{$term}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('discount_method')) {
            $query->where('discount_method', $request->string('discount_method')->toString());
        }

        if ($request->filled('applies_to')) {
            $query->where('applies_to', $request->string('applies_to')->toString());
        }

        $paginator = $query->paginate($perPage)->withQueryString();

        return response()->json([
            'data' => $paginator
                ->getCollection()
                ->map(fn (Promotion $p) => $this->promotionRowPayload($p))
                ->values(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
            'stats' => $stats,
        ]);
    }

    public function create(): JsonResponse
    {
        return response()->json($this->formOptions());
    }

    public function store(Request $request): JsonResponse
    {
        $this->normalizeCode($request);

        $validated = $request->validate($this->promotionRules());
        $payload = $this->payloadFromValidated($validated, $request);

        $promotion = DB::transaction(function () use ($payload, $request, $validated) {
            $promotion = Promotion::query()->create($payload);
            $this->syncRelations($promotion, $request, $validated['applies_to']);

            return $promotion;
        });

        return response()->json([
            'message' => 'Promotion created.',
            'id' => $promotion->id,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $promotion = Promotion::query()
            ->withCount(['services', 'treatmentPackages', 'membershipPlans', 'products'])
            ->with([
                'services:id,name',
                'treatmentPackages:id,name',
                'membershipPlans:id,name',
                'products:id,name,sku',
            ])
            ->findOrFail($id);

        return response()->json([
            'promotion' => $this->promotionDetailPayload($promotion, includeNamed: true),
        ]);
    }

    public function edit(int $id): JsonResponse
    {
        $promotion = Promotion::query()
            ->withCount(['services', 'treatmentPackages', 'membershipPlans', 'products'])
            ->with([
                'services:id',
                'treatmentPackages:id',
                'membershipPlans:id',
                'products:id',
            ])
            ->findOrFail($id);

        return response()->json(array_merge(
            $this->formOptions(),
            ['promotion' => $this->promotionDetailPayload($promotion, includeNamed: false)],
        ));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $promotion = Promotion::query()->findOrFail($id);

        $this->normalizeCode($request);

        $validated = $request->validate($this->promotionRules($promotion->id));
        $payload = $this->payloadFromValidated($validated, $request);

        DB::transaction(function () use ($promotion, $payload, $request, $validated) {
            $promotion->update($payload);
            $this->syncRelations($promotion, $request, $validated['applies_to']);
        });

        return response()->json([
            'message' => 'Promotion updated.',
            'id' => $promotion->id,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $promotion = Promotion::query()->findOrFail($id);

        DB::transaction(function () use ($promotion) {
            $promotion->services()->sync([]);
            $promotion->treatmentPackages()->sync([]);
            $promotion->membershipPlans()->sync([]);
            $promotion->products()->sync([]);
            $promotion->delete();
        });

        return response()->json(['message' => 'Promotion deleted.']);
    }

    public function emailForm(): JsonResponse
    {
        $promotions = Promotion::query()
            ->where('status', 'active')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get(['id', 'name', 'code', 'start_date', 'end_date', 'discount_value', 'discount_method'])
            ->map(fn (Promotion $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'code' => $p->code,
                'start_date' => $p->start_date?->toDateString(),
                'end_date' => $p->end_date?->toDateString(),
                'discount_value' => $p->discount_value !== null ? (float) $p->discount_value : null,
                'discount_method' => $p->discount_method,
                'discount_label' => $p->discount_label,
            ])
            ->all();

        $recipientCount = Patient::query()
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', 'active');
            })
            ->count();

        return response()->json([
            'promotions' => $promotions,
            'recipient_count' => $recipientCount,
        ]);
    }

    public function sendEmail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'promotion_id' => ['required', 'integer', 'exists:promotions,id'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:5000'],
        ]);

        $promotion = Promotion::query()->findOrFail((int) $validated['promotion_id']);

        $patients = Patient::query()
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', 'active');
            })
            ->get(['name', 'email']);

        if ($patients->isEmpty()) {
            throw ValidationException::withMessages([
                'promotion_id' => 'No eligible patient emails found for blast.',
            ]);
        }

        $subjectLine = trim((string) ($validated['subject'] ?? '')) !== ''
            ? trim((string) $validated['subject'])
            : 'New promo available: '.$promotion->name;

        $customMessage = isset($validated['message']) && trim((string) $validated['message']) !== ''
            ? trim((string) $validated['message'])
            : null;

        foreach ($patients as $patient) {
            Mail::to((string) $patient->email)->send(
                new PromotionBlastMail($promotion, $subjectLine, $customMessage, $patient->name)
            );
        }

        return response()->json([
            'message' => 'Promotion email blast sent to '.$patients->count().' patient(s).',
            'recipient_count' => $patients->count(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Internals
    |--------------------------------------------------------------------------
    */

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'services' => $this->serviceOptions(),
            'treatment_packages' => $this->packageOptions(),
            'membership_plans' => $this->membershipPlanOptions(),
            'products' => $this->productOptions(),
            'statuses' => self::STATUSES,
            'discount_methods' => self::DISCOUNT_METHODS,
            'applies_to_options' => self::APPLIES_TO,
            'available_days' => self::AVAILABLE_DAYS,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function promotionRowPayload(Promotion $p): array
    {
        return [
            'id' => $p->id,
            'name' => $p->name,
            'code' => $p->code,
            'type' => $p->type,
            'status' => $p->status,
            'discount_value' => $p->discount_value !== null ? (float) $p->discount_value : null,
            'discount_method' => $p->discount_method,
            'discount_label' => $p->discount_label,
            'applies_to' => $p->applies_to,
            'scope_label' => $p->scope_label,
            'start_date' => $p->start_date?->toDateString(),
            'end_date' => $p->end_date?->toDateString(),
            'valid_until' => $p->end_date?->toDateString(),
            'validity_label' => $p->validity_label,
            'usage_limit' => $p->usage_limit,
            'limit_per_patient' => $p->limit_per_patient,
            'services_count' => (int) ($p->services_count ?? 0),
            'treatment_packages_count' => (int) ($p->treatment_packages_count ?? 0),
            'membership_plans_count' => (int) ($p->membership_plans_count ?? 0),
            'products_count' => (int) ($p->products_count ?? 0),
            'created_at' => $p->created_at?->toIso8601String(),
            'updated_at' => $p->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function promotionDetailPayload(Promotion $p, bool $includeNamed): array
    {
        $rawTime = $p->getRawOriginal('time_limit');

        $base = array_merge($this->promotionRowPayload($p), [
            'description' => $p->description,
            'image' => $p->image,
            'image_url' => $p->image_url,
            'minimum_spend' => $p->minimum_spend !== null ? (float) $p->minimum_spend : null,
            'maximum_discount' => $p->maximum_discount !== null ? (float) $p->maximum_discount : null,
            'time_limit' => $rawTime ? substr((string) $rawTime, 0, 5) : null,
            'available_days' => $p->available_days ?? [],
            'new_patients_only' => (bool) $p->new_patients_only,
            'can_combine_with_other_promos' => (bool) $p->can_combine_with_other_promos,
            'terms_and_conditions' => $p->terms_and_conditions,
            'internal_notes' => $p->internal_notes,
            'display_note' => $p->display_note,
            'service_ids' => $p->services->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
            'treatment_package_ids' => $p->treatmentPackages->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
            'membership_plan_ids' => $p->membershipPlans->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
            'product_ids' => $p->products->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
        ]);

        if ($includeNamed) {
            $base['services'] = $p->services
                ->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])
                ->values()
                ->all();
            $base['treatment_packages'] = $p->treatmentPackages
                ->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])
                ->values()
                ->all();
            $base['membership_plans'] = $p->membershipPlans
                ->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])
                ->values()
                ->all();
            $base['products'] = $p->products
                ->map(fn ($s) => ['id' => $s->id, 'name' => $s->name, 'sku' => $s->sku])
                ->values()
                ->all();
        }

        return $base;
    }

    private function normalizeCode(Request $request): void
    {
        if ($request->string('code')->toString() === '') {
            $request->merge(['code' => null]);
        }
    }

    private function syncRelations(Promotion $promotion, Request $request, string $appliesTo): void
    {
        $promotion->services()->sync([]);
        $promotion->treatmentPackages()->sync([]);
        $promotion->membershipPlans()->sync([]);
        $promotion->products()->sync([]);

        match ($appliesTo) {
            'services' => $promotion->services()->sync($request->input('service_ids', [])),
            'packages' => $promotion->treatmentPackages()->sync($request->input('treatment_package_ids', [])),
            'memberships' => $promotion->membershipPlans()->sync($request->input('membership_plan_ids', [])),
            'products' => $promotion->products()->sync($request->input('product_ids', [])),
            'all' => $this->syncAll($promotion, $request),
            default => null,
        };
    }

    private function syncAll(Promotion $promotion, Request $request): void
    {
        $promotion->services()->sync($request->input('service_ids', []));
        $promotion->treatmentPackages()->sync($request->input('treatment_package_ids', []));
        $promotion->membershipPlans()->sync($request->input('membership_plan_ids', []));
        $promotion->products()->sync($request->input('product_ids', []));
    }

    /**
     * @return array<string, mixed>
     */
    private function promotionRules(?int $ignorePromotionId = null): array
    {
        $codeRule = Rule::unique('promotions', 'code');
        if ($ignorePromotionId !== null) {
            $codeRule = $codeRule->ignore($ignorePromotionId);
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255', $codeRule],
            'type' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:draft,active,scheduled,expired,inactive'],
            'description' => ['nullable', 'string'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'discount_method' => ['nullable', 'string', 'in:percentage,fixed,free_service,bundle'],
            'minimum_spend' => ['nullable', 'numeric', 'min:0'],
            'maximum_discount' => ['nullable', 'numeric', 'min:0'],
            'applies_to' => ['required', 'string', 'in:services,packages,memberships,products,all'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'time_limit' => ['nullable', 'date_format:H:i'],
            'available_days' => ['nullable', 'array'],
            'available_days.*' => ['string', 'in:mon,tue,wed,thu,fri,sat,sun'],
            'usage_limit' => ['nullable', 'integer', 'min:0'],
            'limit_per_patient' => ['nullable', 'integer', 'min:0'],
            'new_patients_only' => ['sometimes', 'boolean'],
            'can_combine_with_other_promos' => ['sometimes', 'boolean'],
            'terms_and_conditions' => ['nullable', 'string'],
            'internal_notes' => ['nullable', 'string'],
            'display_note' => ['nullable', 'string'],
            'service_ids' => ['nullable', 'array'],
            'service_ids.*' => ['integer', 'exists:services,id'],
            'treatment_package_ids' => ['nullable', 'array'],
            'treatment_package_ids.*' => ['integer', 'exists:treatment_packages,id'],
            'membership_plan_ids' => ['nullable', 'array'],
            'membership_plan_ids.*' => ['integer', 'exists:membership_plans,id'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function payloadFromValidated(array $validated, Request $request): array
    {
        $code = isset($validated['code']) && $validated['code'] !== ''
            ? $validated['code']
            : null;

        return [
            'name' => $validated['name'],
            'code' => $code,
            'type' => $validated['type'] ?? null,
            'status' => $validated['status'],
            'description' => $validated['description'] ?? null,
            'discount_value' => $validated['discount_value'] ?? 0,
            'discount_method' => $validated['discount_method'] ?? null,
            'minimum_spend' => $validated['minimum_spend'] ?? null,
            'maximum_discount' => $validated['maximum_discount'] ?? null,
            'applies_to' => $validated['applies_to'],
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'time_limit' => ! empty($validated['time_limit'])
                ? $validated['time_limit'].':00'
                : null,
            'available_days' => ! empty($validated['available_days'])
                ? array_values($validated['available_days'])
                : null,
            'usage_limit' => $validated['usage_limit'] ?? null,
            'limit_per_patient' => $validated['limit_per_patient'] ?? null,
            'new_patients_only' => $request->boolean('new_patients_only'),
            'can_combine_with_other_promos' => $request->boolean('can_combine_with_other_promos'),
            'terms_and_conditions' => $validated['terms_and_conditions'] ?? null,
            'internal_notes' => $validated['internal_notes'] ?? null,
            'display_note' => $validated['display_note'] ?? null,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function serviceOptions(): array
    {
        return Service::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Service $s) => ['id' => $s->id, 'name' => $s->name])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function packageOptions(): array
    {
        return TreatmentPackage::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (TreatmentPackage $p) => ['id' => $p->id, 'name' => $p->name])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function membershipPlanOptions(): array
    {
        return MembershipPlan::query()
            ->orderBy('name')
            ->get(['id', 'name', 'duration_value', 'duration_type'])
            ->map(fn (MembershipPlan $plan) => [
                'id' => $plan->id,
                'name' => $plan->name,
                'duration_label' => $plan->duration_label,
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function productOptions(): array
    {
        return Product::query()
            ->orderBy('name')
            ->get(['id', 'name', 'sku'])
            ->map(fn (Product $p) => ['id' => $p->id, 'name' => $p->name, 'sku' => $p->sku])
            ->all();
    }
}
