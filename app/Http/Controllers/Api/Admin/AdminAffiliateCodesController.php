<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Admin\AffiliateCodesController;
use App\Http\Controllers\Api\Concerns\ConvertsAdminWebResponses;
use App\Http\Controllers\Controller;
use App\Models\AffiliateCode;
use App\Models\MembershipPlan;
use App\Models\Product;
use App\Models\Service;
use App\Models\TreatmentPackage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AdminAffiliateCodesController extends Controller
{
    use ConvertsAdminWebResponses;

    public function index(Request $request): JsonResponse
    {
        $perPage = max(1, min((int) $request->integer('limit', 15), 100));

        $query = AffiliateCode::query()
            ->withCount(['services', 'treatmentPackages', 'membershipPlans', 'products']);

        if ($request->filled('search')) {
            $term = $request->string('search')->toString();
            $query->where(function ($q) use ($term) {
                $q->where('code', 'like', "%{$term}%")
                    ->orWhere('label', 'like', "%{$term}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        $paginator = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'data' => $paginator
                ->getCollection()
                ->map(fn (AffiliateCode $code) => $this->affiliateCodeRowPayload($code))
                ->values(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ]);
    }

    public function create(): JsonResponse
    {
        return response()->json([
            'services' => $this->serviceOptions(),
            'treatment_packages' => $this->packageOptions(),
            'membership_plans' => $this->membershipPlanOptions(),
            'products' => $this->productOptions(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->guardAtLeastOneSelection($request);

        return $this->adminWebJson(app(AffiliateCodesController::class)->store($request), 201);
    }

    public function edit(AffiliateCode $affiliateCode): JsonResponse
    {
        $affiliateCode->load(['services:id', 'treatmentPackages:id', 'membershipPlans:id', 'products:id']);

        return response()->json([
            'affiliate_code' => $this->affiliateCodeDetailPayload($affiliateCode),
            'services' => $this->serviceOptions(),
            'treatment_packages' => $this->packageOptions(),
            'membership_plans' => $this->membershipPlanOptions(),
            'products' => $this->productOptions(),
        ]);
    }

    public function update(Request $request, AffiliateCode $affiliateCode): JsonResponse
    {
        $this->guardAtLeastOneSelection($request);

        return $this->adminWebJson(app(AffiliateCodesController::class)->update($request, $affiliateCode));
    }

    public function destroy(AffiliateCode $affiliateCode): JsonResponse
    {
        return $this->adminWebJson(app(AffiliateCodesController::class)->destroy($affiliateCode));
    }

    /**
     * @return array<string, mixed>
     */
    private function affiliateCodeRowPayload(AffiliateCode $code): array
    {
        return [
            'id' => $code->id,
            'code' => $code->code,
            'label' => $code->label,
            'status' => $code->status,
            'discount_method' => $code->discount_method,
            'discount_value' => $code->discount_value,
            'discount_label' => $code->discount_label,
            'effective_from' => $code->effective_from?->toDateString(),
            'effective_to' => $code->effective_to?->toDateString(),
            'effectivity_label' => $code->effectivity_label,
            'times_used' => (int) $code->times_used,
            'services_count' => (int) ($code->services_count ?? 0),
            'treatment_packages_count' => (int) ($code->treatment_packages_count ?? 0),
            'membership_plans_count' => (int) ($code->membership_plans_count ?? 0),
            'products_count' => (int) ($code->products_count ?? 0),
            'created_at' => $code->created_at?->toIso8601String(),
            'updated_at' => $code->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function affiliateCodeDetailPayload(AffiliateCode $code): array
    {
        return array_merge($this->affiliateCodeRowPayload($code), [
            'notes' => $code->notes,
            'service_ids' => $code->services->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
            'treatment_package_ids' => $code->treatmentPackages->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
            'membership_plan_ids' => $code->membershipPlans->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
            'product_ids' => $code->products->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
        ]);
    }

    private function guardAtLeastOneSelection(Request $request): void
    {
        $services = $request->input('service_ids', []);
        $packages = $request->input('treatment_package_ids', []);
        $memberships = $request->input('membership_plan_ids', []);
        $products = $request->input('product_ids', []);

        $total = (is_array($services) ? count($services) : 0)
            + (is_array($packages) ? count($packages) : 0)
            + (is_array($memberships) ? count($memberships) : 0)
            + (is_array($products) ? count($products) : 0);

        if ($total === 0) {
            throw ValidationException::withMessages([
                'service_ids' => __('Select at least one service, treatment package, membership plan, or product.'),
            ]);
        }
    }

    private function serviceOptions(): array
    {
        return Service::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Service $s) => ['id' => $s->id, 'name' => $s->name])
            ->all();
    }

    private function packageOptions(): array
    {
        return TreatmentPackage::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (TreatmentPackage $p) => ['id' => $p->id, 'name' => $p->name])
            ->all();
    }

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

    private function productOptions(): array
    {
        return Product::query()
            ->orderBy('name')
            ->get(['id', 'name', 'sku'])
            ->map(fn (Product $p) => ['id' => $p->id, 'name' => $p->name, 'sku' => $p->sku])
            ->all();
    }
}
