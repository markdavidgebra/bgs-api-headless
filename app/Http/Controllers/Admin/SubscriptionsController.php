<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MembershipPlan;
use App\Models\PatientSubscription;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SubscriptionsController extends Controller
{
    public function index(Request $request): View
    {
        $query = MembershipPlan::query()
            ->with('services')
            ->withCount([
                'patientSubscriptions as active_subscribers_count' => function ($q) {
                    $q->where('status', 'active');
                },
            ])
            ->orderBy('name');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('billing')) {
            $query->where('billing_cycle', $request->string('billing')->toString());
        }

        $plans = $query->paginate(15)->withQueryString();

        $stats = [
            'total_plans' => MembershipPlan::query()->count(),
            'active_plans' => MembershipPlan::query()->where('status', 'active')->count(),
            'active_subscribers' => PatientSubscription::query()->where('status', 'active')->count(),
        ];

        return view('admin.subscriptions.index', compact('plans', 'stats'));
    }

    public function create(): View
    {
        $services = Service::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.subscriptions.create', compact('services'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateMembershipPlanRequest($request);
        $this->assertDurationPair($validated);
        $this->assertServiceSessionsMatch($request);

        $slug = $this->resolveSlug($validated['name'], $validated['slug'] ?? null, null);

        $plan = MembershipPlan::query()->create([
            'name' => $validated['name'],
            'slug' => $slug,
            'type' => $validated['type'] ?? null,
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'status' => $validated['status'] ?? 'active',
            'billing_cycle' => $validated['billing_cycle'],
            'duration_value' => $validated['duration_value'] ?? null,
            'duration_type' => $validated['duration_type'] ?? null,
            'max_usage_per_month' => $validated['max_usage_per_month'] ?? null,
            'rollover_unused_sessions' => $request->boolean('rollover_unused_sessions'),
            'cancellation_allowed' => $request->boolean('cancellation_allowed'),
            'pause_allowed' => $request->boolean('pause_allowed'),
            'terms_and_conditions' => $validated['terms_and_conditions'] ?? null,
            'before_care' => $validated['before_care'] ?? null,
            'aftercare' => $validated['aftercare'] ?? null,
            'internal_notes' => $validated['internal_notes'] ?? null,
        ]);

        $this->syncServicePivotFromRequest($plan, $request);

        return redirect()
            ->route('admin.subscriptions.show', $plan)
            ->with('status', __('Membership plan created.'));
    }

    public function show(int $id): View
    {
        $plan = MembershipPlan::query()
            ->with([
                'services',
                'patientSubscriptions' => function ($q) {
                    $q->with('patient')->orderByDesc('start_date')->orderByDesc('id');
                },
            ])
            ->withCount([
                'patientSubscriptions',
                'patientSubscriptions as active_patient_subscriptions_count' => function ($q) {
                    $q->where('status', 'active');
                },
            ])
            ->findOrFail($id);

        return view('admin.subscriptions.show', compact('plan'));
    }

    public function edit(int $id): View
    {
        $plan = MembershipPlan::query()->with('services')->findOrFail($id);
        $services = Service::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.subscriptions.edit', compact('plan', 'services'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $plan = MembershipPlan::query()->findOrFail($id);
        $validated = $this->validateMembershipPlanRequest($request);
        $this->assertDurationPair($validated);
        $this->assertServiceSessionsMatch($request);

        $slug = $this->resolveSlug($validated['name'], $validated['slug'] ?? null, $plan->id);

        $plan->update([
            'name' => $validated['name'],
            'slug' => $slug,
            'type' => $validated['type'] ?? null,
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'status' => $validated['status'] ?? 'active',
            'billing_cycle' => $validated['billing_cycle'],
            'duration_value' => $validated['duration_value'] ?? null,
            'duration_type' => $validated['duration_type'] ?? null,
            'max_usage_per_month' => $validated['max_usage_per_month'] ?? null,
            'rollover_unused_sessions' => $request->boolean('rollover_unused_sessions'),
            'cancellation_allowed' => $request->boolean('cancellation_allowed'),
            'pause_allowed' => $request->boolean('pause_allowed'),
            'terms_and_conditions' => $validated['terms_and_conditions'] ?? null,
            'before_care' => $validated['before_care'] ?? null,
            'aftercare' => $validated['aftercare'] ?? null,
            'internal_notes' => $validated['internal_notes'] ?? null,
        ]);

        $this->syncServicePivotFromRequest($plan, $request);

        return redirect()
            ->route('admin.subscriptions.show', $plan)
            ->with('status', __('Membership plan updated.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validateMembershipPlanRequest(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'price' => ['required', 'numeric', 'min:0'],
            'billing_cycle' => ['required', 'string', 'in:monthly,quarterly,yearly'],
            'duration_value' => ['nullable', 'integer', 'min:1'],
            'duration_type' => ['nullable', 'string', 'in:month,year'],
            'max_usage_per_month' => ['nullable', 'integer', 'min:0'],
            'included_service_ids' => ['required', 'array', 'min:1'],
            'included_service_ids.*' => ['integer', 'exists:services,id'],
            'service_sessions' => ['required', 'array'],
            'terms_and_conditions' => ['nullable', 'string'],
            'before_care' => ['nullable', 'string'],
            'aftercare' => ['nullable', 'string'],
            'internal_notes' => ['nullable', 'string'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function assertDurationPair(array $validated): void
    {
        $hasValue = isset($validated['duration_value']) && $validated['duration_value'] !== null;
        $hasType = ! empty($validated['duration_type']);

        if ($hasValue xor $hasType) {
            throw ValidationException::withMessages([
                'duration_value' => __('Set both duration length and unit, or leave both empty.'),
            ]);
        }
    }

    private function assertServiceSessionsMatch(Request $request): void
    {
        $ids = $request->input('included_service_ids', []);
        $sessions = $request->input('service_sessions', []);

        foreach ($ids as $serviceId) {
            $keyInt = is_numeric($serviceId) ? (int) $serviceId : null;
            $raw = $sessions[$serviceId] ?? ($keyInt !== null ? ($sessions[$keyInt] ?? null) : null);
            $count = (int) $raw;
            if ($count < 1) {
                throw ValidationException::withMessages([
                    'service_sessions' => __('Each selected service needs at least one session.'),
                ]);
            }
        }
    }

    private function syncServicePivotFromRequest(MembershipPlan $plan, Request $request): void
    {
        $sync = [];
        foreach ($request->input('included_service_ids', []) as $serviceId) {
            $keyInt = (int) $serviceId;
            $sessions = $request->input('service_sessions', []);
            $count = (int) ($sessions[$serviceId] ?? $sessions[$keyInt] ?? 1);
            $sync[$keyInt] = ['sessions' => max(1, $count)];
        }

        $plan->services()->sync($sync);
    }

    private function resolveSlug(string $name, ?string $slugInput, ?int $ignoreId): string
    {
        $trimmed = $slugInput !== null ? trim($slugInput) : '';
        if ($trimmed !== '') {
            $base = Str::slug($trimmed);

            return $this->uniqueMembershipSlug($base !== '' ? $base : Str::slug($name), $ignoreId);
        }

        return $this->uniqueMembershipSlugFromName($name, $ignoreId);
    }

    private function uniqueMembershipSlugFromName(string $name, ?int $ignoreId): string
    {
        $slug = Str::slug($name);
        if ($slug === '') {
            $slug = 'plan-'.Str::lower(Str::random(8));
        }

        return $this->uniqueMembershipSlug($slug, $ignoreId);
    }

    private function uniqueMembershipSlug(string $slug, ?int $ignoreId): string
    {
        $base = $slug;
        $i = 1;
        while (MembershipPlan::query()
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
