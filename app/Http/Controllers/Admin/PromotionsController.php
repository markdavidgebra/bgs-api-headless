<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\PromotionBlastMail;
use App\Models\MembershipPlan;
use App\Models\Patient;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Service;
use App\Models\TreatmentPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PromotionsController extends Controller
{
    public function index(Request $request): View
    {
        $base = Promotion::query();

        $totalPromos = (clone $base)->count();
        $activePromos = (clone $base)->where('status', 'active')->count();
        $expiredPromos = (clone $base)->where('status', 'expired')->count();
        $draftPromos = (clone $base)->where('status', 'draft')->count();

        $servicePromos = (clone $base)->where('applies_to', 'services')->count();
        $packagePromos = (clone $base)->where('applies_to', 'packages')->count();
        $membershipPromos = (clone $base)->where('applies_to', 'memberships')->count();
        $productPromos = (clone $base)->where('applies_to', 'products')->count();

        $query = Promotion::query()->orderByDesc('created_at')->orderByDesc('id');

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
            $map = [
                'service' => 'services',
                'package' => 'packages',
                'membership' => 'memberships',
                'product' => 'products',
                'all' => 'all',
            ];
            $key = $request->string('applies_to')->toString();
            if (isset($map[$key])) {
                $query->where('applies_to', $map[$key]);
            }
        }

        if ($request->filled('date')) {
            $d = $request->date('date');
            $query->where(function ($q) use ($d) {
                $q->where(function ($q2) use ($d) {
                    $q2->whereNull('start_date')
                        ->orWhereDate('start_date', '<=', $d);
                })->where(function ($q2) use ($d) {
                    $q2->whereNull('end_date')
                        ->orWhereDate('end_date', '>=', $d);
                });
            });
        }

        $promotions = $query->paginate(15)->withQueryString();

        return view('admin.promotions.index', compact(
            'promotions',
            'totalPromos',
            'activePromos',
            'expiredPromos',
            'draftPromos',
            'servicePromos',
            'packagePromos',
            'membershipPromos',
            'productPromos',
        ));
    }

    public function create(): View
    {
        $services = Service::query()->orderBy('name')->get(['id', 'name']);
        $treatmentPackages = TreatmentPackage::query()->orderBy('name')->get(['id', 'name']);
        $membershipPlans = MembershipPlan::query()->orderBy('name')->get(['id', 'name']);
        $products = Product::query()->orderBy('name')->get(['id', 'name', 'sku']);

        return view('admin.promotions.create', compact(
            'services',
            'treatmentPackages',
            'membershipPlans',
            'products',
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        if ($request->string('code')->toString() === '') {
            $request->merge(['code' => null]);
        }

        $validated = $request->validate($this->promotionRules());

        $payload = $this->promotionPayloadFromValidated($validated, $request);

        if ($request->hasFile('image')) {
            $payload['image'] = $this->storePromotionImage($request->file('image'));
        }

        $appliesTo = $validated['applies_to'];

        $promotion = DB::transaction(function () use ($payload, $request, $appliesTo) {
            $promotion = Promotion::query()->create($payload);
            $this->syncPromotionRelations($promotion, $request, $appliesTo);

            return $promotion;
        });

        return redirect()
            ->route('admin.promotions.show', $promotion->id)
            ->with('status', __('Promotion created.'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $promotion = Promotion::query()->findOrFail($id);

        if ($request->string('code')->toString() === '') {
            $request->merge(['code' => null]);
        }

        $validated = $request->validate($this->promotionRules($promotion->id));

        $payload = $this->promotionPayloadFromValidated($validated, $request);

        if ($request->hasFile('image')) {
            $this->deleteStoredPromotionImage($promotion->image);
            $payload['image'] = $this->storePromotionImage($request->file('image'));
        }

        $appliesTo = $validated['applies_to'];

        DB::transaction(function () use ($promotion, $payload, $request, $appliesTo) {
            $promotion->update($payload);
            $this->syncPromotionRelations($promotion, $request, $appliesTo);
        });

        return redirect()
            ->route('admin.promotions.show', $promotion->id)
            ->with('status', __('Promotion updated.'));
    }

    public function show(int $id): View
    {
        $promotion = Promotion::query()
            ->with([
                'services:id,name',
                'treatmentPackages:id,name',
                'membershipPlans:id,name',
                'products:id,name,sku',
            ])
            ->findOrFail($id);

        return view('admin.promotions.show', compact('promotion'));
    }

    public function edit(int $id): View
    {
        $promotion = Promotion::query()
            ->with([
                'services:id',
                'treatmentPackages:id',
                'membershipPlans:id',
                'products:id',
            ])
            ->findOrFail($id);

        $services = Service::query()->orderBy('name')->get(['id', 'name']);
        $treatmentPackages = TreatmentPackage::query()->orderBy('name')->get(['id', 'name']);
        $membershipPlans = MembershipPlan::query()->orderBy('name')->get(['id', 'name']);
        $products = Product::query()->orderBy('name')->get(['id', 'name', 'sku']);

        return view('admin.promotions.edit', compact(
            'promotion',
            'services',
            'treatmentPackages',
            'membershipPlans',
            'products',
        ));
    }

    public function emailForm(): View
    {
        $promotions = Promotion::query()
            ->where('status', 'active')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get(['id', 'name', 'code', 'start_date', 'end_date', 'discount_value', 'discount_method']);

        $recipientCount = Patient::query()
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', 'active');
            })
            ->count();

        return view('admin.promotions.email', compact('promotions', 'recipientCount'));
    }

    public function sendEmailBlast(Request $request): RedirectResponse
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
            return back()->withErrors(['subject' => 'No eligible patient emails found for blast.'])->withInput();
        }

        $subjectLine = trim((string) ($validated['subject'] ?? '')) !== ''
            ? trim((string) $validated['subject'])
            : 'New promo available: '.$promotion->name;

        $customMessage = isset($validated['message']) && trim((string) $validated['message']) !== ''
            ? trim((string) $validated['message'])
            : null;

        foreach ($patients as $patient) {
            Mail::to((string) $patient->email)->send(
                new PromotionBlastMail(
                    $promotion,
                    $subjectLine,
                    $customMessage,
                    $patient->name
                )
            );
        }

        return redirect()
            ->route('admin.promotions.email')
            ->with('status', 'Promotion email blast sent to '.$patients->count().' patient(s).');
    }

    private function syncPromotionRelations(Promotion $promotion, Request $request, string $appliesTo): void
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
            'all' => $this->syncAllPromotionRelations($promotion, $request),
            default => null,
        };
    }

    private function syncAllPromotionRelations(Promotion $promotion, Request $request): void
    {
        $promotion->services()->sync($request->input('service_ids', []));
        $promotion->treatmentPackages()->sync($request->input('treatment_package_ids', []));
        $promotion->membershipPlans()->sync($request->input('membership_plan_ids', []));
        $promotion->products()->sync($request->input('product_ids', []));
    }

    private function storePromotionImage(UploadedFile $file): string
    {
        $dir = public_path('promotions');
        File::ensureDirectoryExists($dir);
        $name = $file->hashName();
        $file->move($dir, $name);

        return 'promotions/'.$name;
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
            'image' => ['nullable', 'image', 'max:5120'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'discount_method' => ['nullable', 'string', 'in:percentage,fixed,free_service,bundle'],
            'minimum_spend' => ['nullable', 'numeric', 'min:0'],
            'maximum_discount' => ['nullable', 'numeric', 'min:0'],
            'applies_to' => ['required', 'string', 'in:services,packages,memberships,products,all'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
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
    private function promotionPayloadFromValidated(array $validated, Request $request): array
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

    private function deleteStoredPromotionImage(?string $path): void
    {
        if (! $path || Str::startsWith($path, ['http://', 'https://'])) {
            return;
        }

        $publicFile = public_path($path);
        if (is_file($publicFile)) {
            @unlink($publicFile);

            return;
        }

        Storage::disk('public')->delete($path);
    }
}
