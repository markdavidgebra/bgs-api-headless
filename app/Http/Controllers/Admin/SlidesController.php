<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Slide;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SlidesController extends Controller
{
    public function index(): View
    {
        $slides = Slide::query()->ordered()->get();

        return view('admin.slides.index', compact('slides'));
    }

    public function create(): View
    {
        return view('admin.slides.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'is_active' => ['nullable', 'boolean'],
            'subtitle' => ['nullable', 'string', 'max:190'],
            'title' => ['required', 'string', 'max:2000'],
            'title_span' => ['nullable', 'string', 'max:2000'],
            'description' => ['nullable', 'string', 'max:5000'],
            'button_text' => ['nullable', 'string', 'max:120'],
            'button_url' => ['nullable', 'string', 'max:500'],
            'show_video' => ['nullable', 'boolean'],
            'video_url' => ['nullable', 'string', 'max:500'],
            'video_label' => ['nullable', 'string', 'max:120'],
            'image' => ['required', 'image', 'max:8192'],
            'image_alt' => ['nullable', 'string', 'max:255'],
        ]);

        $showVideo = $request->boolean('show_video');
        $path = $this->storePublicSlideImage($request->file('image'));
        $title = $validated['title'];

        Slide::query()->create([
            'sort_order' => $this->nextSortOrder(),
            'is_active' => $request->boolean('is_active'),
            'subtitle' => $validated['subtitle'] ?? null,
            'title' => $title,
            'title_span' => $validated['title_span'] ?? null,
            'description' => $validated['description'] ?? null,
            'button_text' => $validated['button_text'] ?? null,
            'button_url' => $this->normalizeButtonUrl($validated['button_url'] ?? null),
            'show_video' => $showVideo,
            'video_url' => $showVideo ? $this->trimNullable($validated['video_url'] ?? null) : null,
            'video_label' => $showVideo ? $this->trimNullable($validated['video_label'] ?? null) : null,
            'image' => $path,
            'image_alt' => $this->resolvedImageAlt($validated['image_alt'] ?? null, $title),
        ]);

        return redirect()->route('admin.slides')->with('status', __('Slide created.'));
    }

    public function edit(int|string $id): View
    {
        $slide = Slide::findOrFail($id);

        return view('admin.slides.edit', compact('slide'));
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        $slide = Slide::findOrFail($id);

        $validated = $request->validate([
            'is_active' => ['nullable', 'boolean'],
            'subtitle' => ['nullable', 'string', 'max:190'],
            'title' => ['required', 'string', 'max:2000'],
            'title_span' => ['nullable', 'string', 'max:2000'],
            'description' => ['nullable', 'string', 'max:5000'],
            'button_text' => ['nullable', 'string', 'max:120'],
            'button_url' => ['nullable', 'string', 'max:500'],
            'show_video' => ['nullable', 'boolean'],
            'video_url' => ['nullable', 'string', 'max:500'],
            'video_label' => ['nullable', 'string', 'max:120'],
            'image' => ['nullable', 'image', 'max:8192'],
            'image_alt' => ['nullable', 'string', 'max:255'],
        ]);

        $showVideo = $request->boolean('show_video');
        $title = $validated['title'];

        $payload = [
            'is_active' => $request->boolean('is_active'),
            'subtitle' => $validated['subtitle'] ?? null,
            'title' => $title,
            'title_span' => $validated['title_span'] ?? null,
            'description' => $validated['description'] ?? null,
            'button_text' => $validated['button_text'] ?? null,
            'button_url' => $this->normalizeButtonUrl($validated['button_url'] ?? null),
            'show_video' => $showVideo,
            'video_url' => $showVideo ? $this->trimNullable($validated['video_url'] ?? null) : null,
            'video_label' => $showVideo ? $this->trimNullable($validated['video_label'] ?? null) : null,
            'image_alt' => $this->resolvedImageAlt($validated['image_alt'] ?? null, $title),
        ];

        if ($request->hasFile('image')) {
            $this->deleteStoredSlideImage($slide->image);
            $payload['image'] = $this->storePublicSlideImage($request->file('image'));
        }

        $slide->update($payload);

        return redirect()->route('admin.slides')->with('status', __('Slide updated.'));
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $slide = Slide::findOrFail($id);
        $this->deleteStoredSlideImage($slide->image);
        $slide->delete();
        $this->renumberSlides();

        return redirect()->route('admin.slides')->with('status', __('Slide removed.'));
    }

    public function moveUp(Slide $slide): RedirectResponse
    {
        $slides = Slide::query()->orderBy('sort_order')->orderBy('id')->get();
        $idx = $slides->search(fn (Slide $s) => $s->is($slide));
        if ($idx === false || $idx < 1) {
            return redirect()->route('admin.slides');
        }
        $this->swapSortOrder($slide, $slides[$idx - 1]);
        $this->renumberSlides();

        return redirect()->route('admin.slides')->with('status', __('Slide order updated.'));
    }

    public function moveDown(Slide $slide): RedirectResponse
    {
        $slides = Slide::query()->orderBy('sort_order')->orderBy('id')->get();
        $idx = $slides->search(fn (Slide $s) => $s->is($slide));
        if ($idx === false || $idx >= $slides->count() - 1) {
            return redirect()->route('admin.slides');
        }
        $this->swapSortOrder($slide, $slides[$idx + 1]);
        $this->renumberSlides();

        return redirect()->route('admin.slides')->with('status', __('Slide order updated.'));
    }

    private function nextSortOrder(): int
    {
        return (int) (Slide::query()->max('sort_order') ?? 0) + 1;
    }

    private function swapSortOrder(Slide $a, Slide $b): void
    {
        $tmp = $a->sort_order;
        $a->update(['sort_order' => $b->sort_order]);
        $b->update(['sort_order' => $tmp]);
    }

    private function renumberSlides(): void
    {
        Slide::query()->orderBy('sort_order')->orderBy('id')->get()->values()->each(function (Slide $slide, int $index) {
            if ($slide->sort_order !== $index) {
                $slide->update(['sort_order' => $index]);
            }
        });
    }

    private function normalizeButtonUrl(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }
        $url = trim($url);
        if ($url === '') {
            return null;
        }
        if (preg_match('#^https?://#i', $url)) {
            return $url;
        }
        if (! str_starts_with($url, '/')) {
            return '/'.ltrim($url, '/');
        }

        return $url;
    }

    private function trimNullable(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function resolvedImageAlt(?string $imageAlt, string $title): ?string
    {
        $t = $imageAlt !== null ? trim($imageAlt) : '';
        if ($t !== '') {
            return Str::limit($t, 255, '');
        }
        $first = Str::of($title)->explode("\n")->first();

        return filled(trim((string) $first)) ? Str::limit(trim((string) $first), 255, '') : null;
    }

    private function storePublicSlideImage(UploadedFile $file): string
    {
        $dir = public_path('slides');
        File::ensureDirectoryExists($dir);
        $name = $file->hashName();
        $file->move($dir, $name);

        return 'slides/'.$name;
    }

    private function deleteStoredSlideImage(?string $path): void
    {
        if (! $path || Str::startsWith($path, ['http://', 'https://'])) {
            return;
        }

        if (Str::startsWith($path, 'frontend/')) {
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
