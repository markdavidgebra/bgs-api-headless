<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AffiliateCode;
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
            ->withCount(['services', 'treatmentPackages', 'products'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15);

        return view('admin.affiliate-codes.index', compact('affiliateCodes'));
    }

    public function create(): View
    {
        $services = Service::query()->orderBy('name')->get(['id', 'name']);
        $treatmentPackages = TreatmentPackage::query()->orderBy('name')->get(['id', 'name']);
        $products = Product::query()->orderBy('name')->get(['id', 'name', 'sku']);

        return view('admin.affiliate-codes.create', compact(
            'services',
            'treatmentPackages',
            'products',
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9_-]+$/', Rule::unique('affiliate_codes', 'code')],
            'label' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'effective_from' => ['nullable', 'date', 'after_or_equal:today', new BookableAppointmentDate],
            'effective_to' => ['nullable', 'date', 'after_or_equal:today', 'after_or_equal:effective_from', new BookableAppointmentDate],
            'discount_method' => ['required', 'string', 'in:percentage,fixed'],
            'discount_value' => [
                'required',
                'numeric',
                'min:0.01',
                Rule::when($request->string('discount_method')->toString() === 'percentage', ['max:100']),
            ],
            'notes' => ['nullable', 'string', 'max:5000'],
            'service_ids' => ['nullable', 'array'],
            'service_ids.*' => ['integer', 'exists:services,id'],
            'treatment_package_ids' => ['nullable', 'array'],
            'treatment_package_ids.*' => ['integer', 'exists:treatment_packages,id'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
        ]);

        $serviceIds = array_values(array_unique(array_map('intval', $validated['service_ids'] ?? [])));
        $packageIds = array_values(array_unique(array_map('intval', $validated['treatment_package_ids'] ?? [])));
        $productIds = array_values(array_unique(array_map('intval', $validated['product_ids'] ?? [])));

        if ($serviceIds === [] && $packageIds === [] && $productIds === []) {
            return back()
                ->withInput()
                ->withErrors([
                    'service_ids' => 'Select at least one service, treatment package, or product.',
                ]);
        }

        $affiliateCode = DB::transaction(function () use ($validated, $serviceIds, $packageIds, $productIds) {
            $affiliateCode = AffiliateCode::query()->create([
                'code' => strtoupper(trim($validated['code'])),
                'label' => $validated['label'] ?? null,
                'status' => $validated['status'],
                'effective_from' => $validated['effective_from'] ?? null,
                'effective_to' => $validated['effective_to'] ?? null,
                'discount_method' => $validated['discount_method'],
                'discount_value' => $validated['discount_value'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $affiliateCode->services()->sync($serviceIds);
            $affiliateCode->treatmentPackages()->sync($packageIds);
            $affiliateCode->products()->sync($productIds);

            return $affiliateCode;
        });

        return redirect()
            ->route('admin.affiliate-codes')
            ->with('status', __('Affiliate code :code created.', ['code' => $affiliateCode->code]));
    }
}
