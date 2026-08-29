<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClinicalStaff;
use App\Models\Service;
use App\Models\TreatmentPackage;
use App\Models\TreatmentPackageUsageHistory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PackagesController extends Controller
{
    public function index(Request $request): View
    {
        $query = TreatmentPackage::query()
            ->with('services')
            ->withCount(['services', 'clinicalStaff'])
            ->orderByDesc('updated_at');

        if ($request->filled('search')) {
            $term = $request->string('search')->toString();
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('slug', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        $packages = $query->paginate(15)->withQueryString();

        return view('admin.packages.index', compact('packages'));
    }

    public function create(): View
    {
        $services = Service::query()->orderBy('name')->get(['id', 'name']);
        $clinicalStaff = ClinicalStaff::query()->notManagerAlias()->orderBy('name')->get(['id', 'name']);

        return view('admin.packages.create', compact('services', 'clinicalStaff'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePackageRequest($request);
        $this->assertServiceSessionsMatch($request);

        $slug = $this->uniqueSlug($validated['name'], null);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $this->storePublicPackageImage($request->file('image'));
        }

        $package = TreatmentPackage::query()->create([
            'name' => $validated['name'],
            'slug' => $slug,
            'category' => $validated['category'] ?? null,
            'description' => $validated['description'] ?? null,
            'image' => $imagePath,
            'status' => $validated['status'] ?? 'active',
            'price' => $validated['total_price'],
            'original_price' => $validated['original_price'] ?? null,
            'discount_percent' => $validated['discount_percent'] ?? null,
            'validity_value' => $validated['validity_duration'] ?? null,
            'validity_type' => $validated['validity_unit'] ?? null,
            'expiry_rule' => $validated['expiry_rule'] ?? null,
            'max_usage_per_day' => $validated['max_usage_per_day'] ?? null,
            'allow_sharing' => $request->boolean('allow_sharing'),
            'is_refundable' => $request->boolean('refundable'),
            'before_care' => $validated['before_care'] ?? null,
            'aftercare' => $validated['aftercare'] ?? null,
            'internal_notes' => $validated['notes'] ?? null,
        ]);

        $this->syncServicePivotFromRequest($package, $request);
        $package->clinicalStaff()->sync($request->input('assigned_clinical_staff', []));

        return redirect()
            ->route('admin.packages.show', $package)
            ->with('status', __('Package created.'));
    }

    public function edit(string $id): View
    {
        $package = TreatmentPackage::query()
            ->with(['services', 'clinicalStaff'])
            ->findOrFail($id);

        $services = Service::query()->orderBy('name')->get(['id', 'name']);
        $clinicalStaff = ClinicalStaff::query()->notManagerAlias()->orderBy('name')->get(['id', 'name']);

        return view('admin.packages.edit', compact('package', 'services', 'clinicalStaff'));
    }

    public function show(string $id): View
    {
        $package = TreatmentPackage::query()
            ->with([
                'services',
                'clinicalStaff',
                'patientPackages' => fn ($q) => $q->orderByDesc('purchased_at')->orderByDesc('id'),
                'patientPackages.patient',
            ])
            ->findOrFail($id);

        $usageHistories = TreatmentPackageUsageHistory::query()
            ->whereHas('patientPackage', fn ($q) => $q->where('treatment_package_id', $package->id))
            ->with(['patient', 'service'])
            ->orderByDesc('used_on')
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        return view('admin.packages.show', compact('package', 'usageHistories'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $package = TreatmentPackage::query()->findOrFail($id);
        $validated = $this->validatePackageRequest($request);
        $this->assertServiceSessionsMatch($request);

        $slug = $this->uniqueSlug($validated['name'], $package->id);

        $payload = [
            'name' => $validated['name'],
            'slug' => $slug,
            'category' => $validated['category'] ?? null,
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'] ?? 'active',
            'price' => $validated['total_price'],
            'original_price' => $validated['original_price'] ?? null,
            'discount_percent' => $validated['discount_percent'] ?? null,
            'validity_value' => $validated['validity_duration'] ?? null,
            'validity_type' => $validated['validity_unit'] ?? null,
            'expiry_rule' => $validated['expiry_rule'] ?? null,
            'max_usage_per_day' => $validated['max_usage_per_day'] ?? null,
            'allow_sharing' => $request->boolean('allow_sharing'),
            'is_refundable' => $request->boolean('refundable'),
            'before_care' => $validated['before_care'] ?? null,
            'aftercare' => $validated['aftercare'] ?? null,
            'internal_notes' => $validated['notes'] ?? null,
        ];

        if ($request->hasFile('image')) {
            $this->deleteStoredPackageImage($package->image);
            $payload['image'] = $this->storePublicPackageImage($request->file('image'));
        }

        $package->update($payload);

        $this->syncServicePivotFromRequest($package, $request);
        $package->clinicalStaff()->sync($request->input('assigned_clinical_staff', []));

        return redirect()
            ->route('admin.packages.show', $package)
            ->with('status', __('Package updated.'));
    }

    public function destroy(string $id): RedirectResponse
    {
        $package = TreatmentPackage::query()->findOrFail($id);

        if ($package->patientPackages()->exists()) {
            return redirect()
                ->route('admin.packages')
                ->with('error', __('Cannot delete this package because it has already been purchased by patients.'));
        }

        $this->deleteStoredPackageImage($package->image);

        $package->services()->detach();
        $package->clinicalStaff()->detach();
        $package->delete();

        return redirect()
            ->route('admin.packages')
            ->with('status', __('Package deleted.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePackageRequest(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'in:active,inactive,pending,archived'],
            'image' => ['nullable', 'image', 'max:5120'],
            'included_service_ids' => ['required', 'array', 'min:1'],
            'included_service_ids.*' => ['integer', 'exists:services,id'],
            'service_sessions' => ['required', 'array'],
            'total_price' => ['required', 'numeric', 'min:0'],
            'original_price' => ['nullable', 'numeric', 'min:0'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'validity_duration' => ['nullable', 'integer', 'min:1'],
            'validity_unit' => ['nullable', 'string', 'in:days,months,years'],
            'expiry_rule' => ['nullable', 'string', 'in:after_purchase,after_first_use'],
            'max_usage_per_day' => ['nullable', 'integer', 'min:1'],
            'assigned_clinical_staff' => ['nullable', 'array'],
            'assigned_clinical_staff.*' => ['integer', 'exists:clinical_staff,id'],
            'before_care' => ['nullable', 'string'],
            'aftercare' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    private function assertServiceSessionsMatch(Request $request): void
    {
        $ids = $request->input('included_service_ids', []);
        $sessions = $request->input('service_sessions', []);

        $errors = [];
        foreach ($ids as $serviceId) {
            $keyInt = is_numeric($serviceId) ? (int) $serviceId : null;
            $raw = $sessions[$serviceId] ?? ($keyInt !== null ? ($sessions[$keyInt] ?? null) : null);
            $count = (int) $raw;
            if ($count < 1) {
                $errors['service_sessions'] = __('Each selected service needs at least one session.');
                break;
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function syncServicePivotFromRequest(TreatmentPackage $package, Request $request): void
    {
        $sync = [];
        foreach ($request->input('included_service_ids', []) as $serviceId) {
            $keyInt = (int) $serviceId;
            $sessions = $request->input('service_sessions', []);
            $count = (int) ($sessions[$serviceId] ?? $sessions[$keyInt] ?? 1);
            $sync[$keyInt] = ['sessions' => max(1, $count)];
        }

        $package->services()->sync($sync);
    }

    private function uniqueSlug(string $name, ?int $ignoreId): string
    {
        $slug = Str::slug($name);
        if ($slug === '') {
            $slug = 'package-'.Str::lower(Str::random(8));
        }

        $base = $slug;
        $i = 1;
        while (TreatmentPackage::query()
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    private function storePublicPackageImage(UploadedFile $file): string
    {
        $dir = public_path('packages');
        File::ensureDirectoryExists($dir);

        $name = $file->hashName();
        $file->move($dir, $name);

        return 'packages/'.$name;
    }

    private function deleteStoredPackageImage(?string $path): void
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
