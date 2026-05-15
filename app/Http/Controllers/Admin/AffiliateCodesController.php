<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AffiliateCode;
use App\Models\MembershipPlan;
use App\Models\Product;
use App\Models\Service;
use App\Models\TreatmentPackage;
use App\Rules\BookableAppointmentDate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AffiliateCodesController extends Controller
{
    public function index(): View
    {
        $affiliateCodes = AffiliateCode::query()
            ->withCount(['services', 'treatmentPackages', 'membershipPlans', 'products'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15);

        return view('admin.affiliate-codes.index', compact('affiliateCodes'));
    }

    public function create(): View
    {
        $services = Service::query()->orderBy('name')->get(['id', 'name']);
        $treatmentPackages = TreatmentPackage::query()->orderBy('name')->get(['id', 'name']);
        $membershipPlans = MembershipPlan::query()->orderBy('name')->get(['id', 'name', 'duration_value', 'duration_type']);
        $products = Product::query()->orderBy('name')->get(['id', 'name', 'sku']);

        return view('admin.affiliate-codes.create', compact(
            'services',
            'treatmentPackages',
            'membershipPlans',
            'products',
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->affiliateCodeRules());

        return $this->persistAffiliateCode($validated, null, 'created');
    }

    public function edit(AffiliateCode $affiliateCode): View
    {
        $affiliateCode->load(['services:id', 'treatmentPackages:id', 'membershipPlans:id', 'products:id']);

        $services = Service::query()->orderBy('name')->get(['id', 'name']);
        $treatmentPackages = TreatmentPackage::query()->orderBy('name')->get(['id', 'name']);
        $membershipPlans = MembershipPlan::query()->orderBy('name')->get(['id', 'name', 'duration_value', 'duration_type']);
        $products = Product::query()->orderBy('name')->get(['id', 'name', 'sku']);

        return view('admin.affiliate-codes.edit', compact(
            'affiliateCode',
            'services',
            'treatmentPackages',
            'membershipPlans',
            'products',
        ));
    }

    public function update(Request $request, AffiliateCode $affiliateCode): RedirectResponse
    {
        $validated = $request->validate($this->affiliateCodeRules($affiliateCode));

        return $this->persistAffiliateCode($validated, $affiliateCode, 'updated');
    }

    public function destroy(AffiliateCode $affiliateCode): RedirectResponse
    {
        $code = $affiliateCode->code;

        DB::transaction(fn () => $affiliateCode->delete());

        return redirect()
            ->route('admin.affiliate-codes')
            ->with('status', __('Affiliate code :code deleted.', ['code' => $code]));
    }

    /**
     * @return array<string, mixed>
     */
    private function affiliateCodeRules(?AffiliateCode $affiliateCode = null): array
    {
        $codeRules = ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9_-]+$/'];
        $codeRules[] = $affiliateCode
            ? Rule::unique('affiliate_codes', 'code')->ignore($affiliateCode->id)
            : Rule::unique('affiliate_codes', 'code');

        $fromRules = ['nullable', 'date', new BookableAppointmentDate];
        $toRules = ['nullable', 'date', 'after_or_equal:effective_from', new BookableAppointmentDate];

        if (! $affiliateCode) {
            $fromRules[] = 'after_or_equal:today';
            $toRules[] = 'after_or_equal:today';
        }

        return [
            'code' => $codeRules,
            'label' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'effective_from' => $fromRules,
            'effective_to' => $toRules,
            'discount_method' => ['required', 'string', 'in:percentage,fixed'],
            'discount_value' => [
                'required',
                'numeric',
                'min:0.01',
                Rule::when(request()->string('discount_method')->toString() === 'percentage', ['max:100']),
            ],
            'notes' => ['nullable', 'string', 'max:5000'],
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
     */
    private function persistAffiliateCode(array $validated, ?AffiliateCode $affiliateCode, string $action): RedirectResponse
    {
        $serviceIds = array_values(array_unique(array_map('intval', $validated['service_ids'] ?? [])));
        $packageIds = array_values(array_unique(array_map('intval', $validated['treatment_package_ids'] ?? [])));
        $membershipPlanIds = array_values(array_unique(array_map('intval', $validated['membership_plan_ids'] ?? [])));
        $productIds = array_values(array_unique(array_map('intval', $validated['product_ids'] ?? [])));

        if ($serviceIds === [] && $packageIds === [] && $membershipPlanIds === [] && $productIds === []) {
            return back()
                ->withInput()
                ->withErrors([
                    'service_ids' => 'Select at least one service, treatment package, membership plan, or product.',
                ]);
        }

        $payload = [
            'code' => strtoupper(trim($validated['code'])),
            'label' => $validated['label'] ?? null,
            'status' => $validated['status'],
            'effective_from' => $validated['effective_from'] ?? null,
            'effective_to' => $validated['effective_to'] ?? null,
            'discount_method' => $validated['discount_method'],
            'discount_value' => $validated['discount_value'],
            'notes' => $validated['notes'] ?? null,
        ];

        $affiliateCode = DB::transaction(function () use ($affiliateCode, $payload, $serviceIds, $packageIds, $membershipPlanIds, $productIds) {
            if ($affiliateCode) {
                $affiliateCode->update($payload);
            } else {
                $affiliateCode = AffiliateCode::query()->create($payload);
            }

            $affiliateCode->services()->sync($serviceIds);
            $affiliateCode->treatmentPackages()->sync($packageIds);
            $affiliateCode->membershipPlans()->sync($membershipPlanIds);
            $affiliateCode->products()->sync($productIds);

            return $affiliateCode;
        });

        $message = $action === 'created'
            ? __('Affiliate code :code created.', ['code' => $affiliateCode->code])
            : __('Affiliate code :code updated.', ['code' => $affiliateCode->code]);

        return redirect()
            ->route('admin.affiliate-codes')
            ->with('status', $message);
    }
}
