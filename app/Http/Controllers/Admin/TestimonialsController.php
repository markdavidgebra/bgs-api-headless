<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TestimonialsController extends Controller
{
    public function index(): View
    {
        $testimonials = Testimonial::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(20);

        return view('admin.testimonials.index', compact('testimonials'));
    }

    public function create(): View
    {
        return view('admin.testimonials.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'designation' => ['nullable', 'string', 'max:150'],
            'quote' => ['required', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:draft,published'],
            'image' => ['nullable', 'image', 'max:8192'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $this->storePublicImage($request->file('image'));
        }

        Testimonial::query()->create([
            'name' => $validated['name'],
            'designation' => $validated['designation'] ?? null,
            'quote' => $validated['quote'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'status' => $validated['status'],
            'image' => $imagePath,
        ]);

        return redirect()->route('admin.testimonials')->with('status', __('Testimonial created.'));
    }

    public function show(int|string $id): View
    {
        $testimonial = Testimonial::query()->findOrFail($id);

        return view('admin.testimonials.show', compact('testimonial'));
    }

    public function edit(int|string $id): View
    {
        $testimonial = Testimonial::query()->findOrFail($id);

        return view('admin.testimonials.edit', compact('testimonial'));
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        $testimonial = Testimonial::query()->findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'designation' => ['nullable', 'string', 'max:150'],
            'quote' => ['required', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:draft,published'],
            'image' => ['nullable', 'image', 'max:8192'],
        ]);

        $payload = [
            'name' => $validated['name'],
            'designation' => $validated['designation'] ?? null,
            'quote' => $validated['quote'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'status' => $validated['status'],
        ];

        if ($request->hasFile('image')) {
            $this->deleteStoredImage($testimonial->image);
            $payload['image'] = $this->storePublicImage($request->file('image'));
        }

        $testimonial->update($payload);

        return redirect()->route('admin.testimonials')->with('status', __('Testimonial updated.'));
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $testimonial = Testimonial::query()->findOrFail($id);
        $this->deleteStoredImage($testimonial->image);
        $testimonial->delete();

        return redirect()->route('admin.testimonials')->with('status', __('Testimonial deleted.'));
    }

    private function storePublicImage(UploadedFile $file): string
    {
        $dir = public_path('testimonials');
        File::ensureDirectoryExists($dir);

        $name = $file->hashName();
        $file->move($dir, $name);

        return 'testimonials/'.$name;
    }

    private function deleteStoredImage(?string $path): void
    {
        if (! $path || Str::startsWith($path, ['http://', 'https://'])) {
            return;
        }

        if (Str::startsWith($path, 'frontend/')) {
            return;
        }

        $file = public_path($path);
        if (is_file($file)) {
            @unlink($file);
        }
    }
}
