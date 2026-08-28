<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ServicesController extends Controller
{
    public function index(Request $request): View
    {
        $query = Service::query()->orderBy('name');

        if ($request->filled('search')) {
            $term = $request->string('search')->toString();
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('slug', 'like', "%{$term}%")
                    ->orWhere('short_description', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('category')) {
            $cat = $request->string('category')->toString();
            $query->where(function ($q) use ($cat) {
                $q->where('short_description', 'like', "%{$cat}%")
                    ->orWhere('name', 'like', "%{$cat}%");
            });
        }

        $stats = [
            'total' => (clone $query)->count(),
            'active' => (clone $query)->where('status', 'active')->count(),
            'inactive' => (clone $query)->where('status', 'inactive')->count(),
            'avg_price' => (clone $query)->avg('price'),
        ];
        $avgPrice = (float) ($stats['avg_price'] ?? 0);

        $services = $query->paginate(15)->withQueryString();

        return view('admin.services.index', compact('services', 'stats', 'avgPrice'));
    }

    public function create(): View
    {
        return view('admin.services.create');
    }

    public function edit(int|string $id): View
    {
        $service = Service::query()->findOrFail($id);

        $draftName = old('name', $service->name);
        $oldSlug = old('slug', $service->slug ?? ($draftName !== '' ? \Illuminate\Support\Str::slug($draftName) : ''));
        $status = old('status', $service->status ?? 'active');
        $badge = $status === 'active' ? 'bg-green-lt' : 'bg-secondary-lt';
        $featuredChecked = old('is_featured') === '1' || (old('is_featured') === null && $service->is_featured && ! session()->has('errors'));
        $bookableChecked = old('is_bookable') === '1' || (old('is_bookable') === null && $service->is_bookable && ! session()->has('errors'));

        return view('admin.services.edit', compact('service', 'draftName', 'oldSlug', 'status', 'badge', 'featuredChecked', 'bookableChecked'));
    }

    public function show(int|string $id): View
    {
        $service = Service::query()->findOrFail($id);

        return view('admin.services.show', compact('service'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'short_description' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'promo_price' => ['nullable', 'numeric', 'min:0'],
            'duration_minutes' => ['nullable', 'integer', 'min:0'],
            'session_count' => ['nullable', 'integer', 'min:0'],
            'icon_class' => ['nullable', 'string', Rule::in(Service::allowedIconClassesForValidation())],
            'image' => ['required', 'image', 'max:5120'],
            'recovery_time' => ['nullable', 'string', 'max:100'],
            'max_appointments_per_day' => ['nullable', 'integer', 'min:0', 'max:255'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
            'before_care' => ['nullable', 'string'],
            'after_care' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $slug = Str::slug((string) ($validated['slug'] ?? '')) !== ''
            ? Str::slug($validated['slug'])
            : Str::slug($validated['name']);

        if ($slug === '') {
            $slug = 'service-'.Str::lower(Str::random(8));
        }

        $baseSlug = $slug;
        $i = 1;
        while (Service::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$i++;
        }

        $path = $this->storePublicServiceImage($request->file('image'));

        $service = Service::query()->create([
            'name' => $validated['name'],
            'slug' => $slug,
            'short_description' => $validated['short_description'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'promo_price' => $validated['promo_price'] ?? null,
            'duration_minutes' => $validated['duration_minutes'] ?? null,
            'session_count' => $validated['session_count'] ?? null,
            'icon_class' => filled($validated['icon_class'] ?? null) ? $validated['icon_class'] : null,
            'image' => $path,
            'recovery_time' => $validated['recovery_time'] ?? null,
            'max_appointments_per_day' => $validated['max_appointments_per_day'] ?? null,
            'status' => $validated['status'] ?? 'active',
            'is_featured' => $request->boolean('is_featured'),
            'is_bookable' => $request->boolean('is_bookable'),
            'before_care' => $validated['before_care'] ?? null,
            'after_care' => $validated['after_care'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('admin.services')->with('status', __('Service created.'));
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        $service = Service::findOrFail($id);

        $iconRuleIn = Service::allowedIconClassesForValidation();
        $currentIcon = trim((string) ($service->icon_class ?? ''));
        if ($currentIcon !== '' && ! in_array($currentIcon, $iconRuleIn, true)) {
            $iconRuleIn[] = $currentIcon;
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'short_description' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'promo_price' => ['nullable', 'numeric', 'min:0'],
            'duration_minutes' => ['nullable', 'integer', 'min:0'],
            'session_count' => ['nullable', 'integer', 'min:0'],
            'icon_class' => ['nullable', 'string', Rule::in($iconRuleIn)],
            'image' => ['nullable', 'image', 'max:5120'],
            'recovery_time' => ['nullable', 'string', 'max:100'],
            'max_appointments_per_day' => ['nullable', 'integer', 'min:0', 'max:255'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
            'before_care' => ['nullable', 'string'],
            'after_care' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $slug = Str::slug((string) ($validated['slug'] ?? '')) !== ''
            ? Str::slug($validated['slug'])
            : Str::slug($validated['name']);

        if ($slug === '') {
            $slug = 'service-'.Str::lower(Str::random(8));
        }

        $baseSlug = $slug;
        $i = 1;
        while (Service::query()->where('slug', $slug)->where('id', '!=', $service->id)->exists()) {
            $slug = $baseSlug.'-'.$i++;
        }

        $payload = [
            'name' => $validated['name'],
            'slug' => $slug,
            'short_description' => $validated['short_description'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'promo_price' => $validated['promo_price'] ?? null,
            'duration_minutes' => $validated['duration_minutes'] ?? null,
            'session_count' => $validated['session_count'] ?? null,
            'icon_class' => filled($validated['icon_class'] ?? null) ? $validated['icon_class'] : null,
            'recovery_time' => $validated['recovery_time'] ?? null,
            'max_appointments_per_day' => $validated['max_appointments_per_day'] ?? null,
            'status' => $validated['status'] ?? 'active',
            'is_featured' => $request->boolean('is_featured'),
            'is_bookable' => $request->boolean('is_bookable'),
            'before_care' => $validated['before_care'] ?? null,
            'after_care' => $validated['after_care'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ];

        if ($request->hasFile('image')) {
            $this->deleteStoredServiceImage($service->image);
            $payload['image'] = $this->storePublicServiceImage($request->file('image'));
        }

        $service->update($payload);

        return redirect()
            ->route('admin.services.show', $service)
            ->with('status', __('Service updated.'));
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $service = Service::query()->findOrFail($id);

        if (Appointment::query()->where('service_id', $service->id)->exists()) {
            return redirect()
                ->route('admin.services')
                ->with('error', __('Cannot delete this service because it is already linked to appointment records.'));
        }

        $this->deleteStoredServiceImage($service->image);
        $service->delete();

        return redirect()
            ->route('admin.services')
            ->with('status', __('Service deleted.'));
    }

    /**
     * Persist upload under public/services and return path relative to public/ (e.g. services/abc.jpg).
     */
    private function storePublicServiceImage(UploadedFile $file): string
    {
        $dir = public_path('services');
        File::ensureDirectoryExists($dir);

        $name = $file->hashName();
        $file->move($dir, $name);

        return 'services/'.$name;
    }

    private function deleteStoredServiceImage(?string $path): void
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
