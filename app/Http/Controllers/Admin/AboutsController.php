<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\About;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AboutsController extends Controller
{
    public function index(): View
    {
        $about = About::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        return view('admin.pages.about.index', compact('about'));
    }

    public function create(): View
    {
        return view('admin.pages.about.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:draft,published'],
            'image' => ['nullable', 'image', 'max:8192'],
            'secondary_image' => ['nullable', 'image', 'max:8192'],
            'meta.story_points' => ['nullable', 'array'],
            'meta.story_points.*' => ['nullable', 'string', 'max:255'],
            'meta.features' => ['nullable', 'array'],
            'meta.features.*.icon' => ['nullable', 'string', 'max:100'],
            'meta.features.*.title' => ['nullable', 'string', 'max:255'],
            'meta.features.*.text' => ['nullable', 'string', 'max:1000'],
            'meta.list_points' => ['nullable', 'array'],
            'meta.list_points.*' => ['nullable', 'string', 'max:255'],
            'meta.home_bottom_text' => ['nullable', 'string', 'max:1000'],
            'meta.about_highlight_quote' => ['nullable', 'string', 'max:1000'],
            'meta.about_after_quote_paragraphs' => ['nullable', 'array'],
            'meta.about_after_quote_paragraphs.*' => ['nullable', 'string', 'max:2000'],
            'meta.vision_title' => ['nullable', 'string', 'max:255'],
            'meta.vision_text' => ['nullable', 'string', 'max:1000'],
            'meta.mission_title' => ['nullable', 'string', 'max:255'],
            'meta.mission_text' => ['nullable', 'string', 'max:1000'],
            'meta.about_footer_title' => ['nullable', 'string', 'max:255'],
            'meta.about_footer_subtitle' => ['nullable', 'string', 'max:255'],
            'meta.clinic_hours_title' => ['nullable', 'string', 'max:255'],
            'meta.clinic_hours' => ['nullable', 'array'],
            'meta.clinic_hours.*.day' => ['nullable', 'string', 'max:255'],
            'meta.clinic_hours.*.time' => ['nullable', 'string', 'max:255'],
            'meta.why_tagline' => ['nullable', 'string', 'max:255'],
            'meta.why_title' => ['nullable', 'string', 'max:255'],
            'meta.why_text' => ['nullable', 'string', 'max:1000'],
            'meta.progress_1_label' => ['nullable', 'string', 'max:255'],
            'meta.progress_1_value' => ['nullable', 'integer', 'min:0', 'max:100'],
            'meta.progress_2_label' => ['nullable', 'string', 'max:255'],
            'meta.progress_2_value' => ['nullable', 'integer', 'min:0', 'max:100'],
            'meta.help_label' => ['nullable', 'string', 'max:255'],
            'meta.help_phone' => ['nullable', 'string', 'max:50'],
            'meta.stats_1_value' => ['nullable', 'integer', 'min:0'],
            'meta.stats_1_label' => ['nullable', 'string', 'max:255'],
            'meta.stats_2_value' => ['nullable', 'integer', 'min:0'],
            'meta.stats_2_label' => ['nullable', 'string', 'max:255'],
            'meta.stats_3_value' => ['nullable', 'integer', 'min:0'],
            'meta.stats_3_label' => ['nullable', 'string', 'max:255'],
            'meta.stats_4_value' => ['nullable', 'integer', 'min:0'],
            'meta.stats_4_label' => ['nullable', 'string', 'max:255'],
            'meta.cta_tagline' => ['nullable', 'string', 'max:255'],
            'meta.cta_title' => ['nullable', 'string', 'max:255'],
            'meta.cta_primary_text' => ['nullable', 'string', 'max:255'],
            'meta.cta_primary_url' => ['nullable', 'string', 'max:255'],
            'meta.cta_secondary_text' => ['nullable', 'string', 'max:255'],
            'meta.cta_secondary_url' => ['nullable', 'string', 'max:255'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $this->storePublicImage($request->file('image'));
        }

        $metaPayload = $this->buildMetaPayload($validated['meta'] ?? []);
        if ($request->hasFile('secondary_image')) {
            $metaPayload['secondary_image'] = $this->storePublicImage($request->file('secondary_image'));
        }

        About::query()->create([
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'] ?? null,
            'content' => $validated['content'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'status' => $validated['status'],
            'image' => $imagePath,
            'meta' => $metaPayload,
        ]);

        return redirect()->route('admin.abouts')->with('status', __('About content created.'));
    }

    public function show(int|string $id): RedirectResponse
    {
        About::query()->findOrFail($id);

        return redirect()->route('admin.abouts.edit', $id);
    }

    public function edit(int|string $id): View
    {
        $about = About::query()->findOrFail($id);

        return view('admin.pages.about.edit', compact('about'));
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        $about = About::query()->findOrFail($id);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:draft,published'],
            'image' => ['nullable', 'image', 'max:8192'],
            'secondary_image' => ['nullable', 'image', 'max:8192'],
            'meta.story_points' => ['nullable', 'array'],
            'meta.story_points.*' => ['nullable', 'string', 'max:255'],
            'meta.features' => ['nullable', 'array'],
            'meta.features.*.icon' => ['nullable', 'string', 'max:100'],
            'meta.features.*.title' => ['nullable', 'string', 'max:255'],
            'meta.features.*.text' => ['nullable', 'string', 'max:1000'],
            'meta.list_points' => ['nullable', 'array'],
            'meta.list_points.*' => ['nullable', 'string', 'max:255'],
            'meta.home_bottom_text' => ['nullable', 'string', 'max:1000'],
            'meta.about_highlight_quote' => ['nullable', 'string', 'max:1000'],
            'meta.about_after_quote_paragraphs' => ['nullable', 'array'],
            'meta.about_after_quote_paragraphs.*' => ['nullable', 'string', 'max:2000'],
            'meta.vision_title' => ['nullable', 'string', 'max:255'],
            'meta.vision_text' => ['nullable', 'string', 'max:1000'],
            'meta.mission_title' => ['nullable', 'string', 'max:255'],
            'meta.mission_text' => ['nullable', 'string', 'max:1000'],
            'meta.about_footer_title' => ['nullable', 'string', 'max:255'],
            'meta.about_footer_subtitle' => ['nullable', 'string', 'max:255'],
            'meta.clinic_hours_title' => ['nullable', 'string', 'max:255'],
            'meta.clinic_hours' => ['nullable', 'array'],
            'meta.clinic_hours.*.day' => ['nullable', 'string', 'max:255'],
            'meta.clinic_hours.*.time' => ['nullable', 'string', 'max:255'],
            'meta.why_tagline' => ['nullable', 'string', 'max:255'],
            'meta.why_title' => ['nullable', 'string', 'max:255'],
            'meta.why_text' => ['nullable', 'string', 'max:1000'],
            'meta.progress_1_label' => ['nullable', 'string', 'max:255'],
            'meta.progress_1_value' => ['nullable', 'integer', 'min:0', 'max:100'],
            'meta.progress_2_label' => ['nullable', 'string', 'max:255'],
            'meta.progress_2_value' => ['nullable', 'integer', 'min:0', 'max:100'],
            'meta.help_label' => ['nullable', 'string', 'max:255'],
            'meta.help_phone' => ['nullable', 'string', 'max:50'],
            'meta.stats_1_value' => ['nullable', 'integer', 'min:0'],
            'meta.stats_1_label' => ['nullable', 'string', 'max:255'],
            'meta.stats_2_value' => ['nullable', 'integer', 'min:0'],
            'meta.stats_2_label' => ['nullable', 'string', 'max:255'],
            'meta.stats_3_value' => ['nullable', 'integer', 'min:0'],
            'meta.stats_3_label' => ['nullable', 'string', 'max:255'],
            'meta.stats_4_value' => ['nullable', 'integer', 'min:0'],
            'meta.stats_4_label' => ['nullable', 'string', 'max:255'],
            'meta.cta_tagline' => ['nullable', 'string', 'max:255'],
            'meta.cta_title' => ['nullable', 'string', 'max:255'],
            'meta.cta_primary_text' => ['nullable', 'string', 'max:255'],
            'meta.cta_primary_url' => ['nullable', 'string', 'max:255'],
            'meta.cta_secondary_text' => ['nullable', 'string', 'max:255'],
            'meta.cta_secondary_url' => ['nullable', 'string', 'max:255'],
        ]);

        $metaPayload = $this->buildMetaPayload($validated['meta'] ?? []);
        $existingMeta = is_array($about->meta) ? $about->meta : [];
        if (! $request->hasFile('secondary_image') && filled(data_get($existingMeta, 'secondary_image'))) {
            $metaPayload['secondary_image'] = data_get($existingMeta, 'secondary_image');
        }

        if ($request->hasFile('secondary_image')) {
            $this->deleteStoredImage(data_get($existingMeta, 'secondary_image'));
            $metaPayload['secondary_image'] = $this->storePublicImage($request->file('secondary_image'));
        }

        $payload = [
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'] ?? null,
            'content' => $validated['content'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'status' => $validated['status'],
            'meta' => $metaPayload,
        ];

        if ($request->hasFile('image')) {
            $this->deleteStoredImage($about->image);
            $payload['image'] = $this->storePublicImage($request->file('image'));
        }

        $about->update($payload);

        return redirect()->route('admin.abouts.edit', $about->id)->with('status', __('About content updated.'));
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $about = About::query()->findOrFail($id);
        $this->deleteStoredImage($about->image);
        $about->delete();

        return redirect()->route('admin.abouts')->with('status', __('About content deleted.'));
    }

    private function storePublicImage(UploadedFile $file): string
    {
        $dir = public_path('about');
        File::ensureDirectoryExists($dir);
        $name = $file->hashName();
        $file->move($dir, $name);

        return 'about/'.$name;
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

    private function buildMetaPayload(array $meta): array
    {
        $storyPoints = collect($meta['story_points'] ?? [])
            ->map(fn ($point) => trim((string) $point))
            ->filter(fn ($point) => $point !== '')
            ->values()
            ->all();

        if ($storyPoints === []) {
            $storyPoints = collect([
                trim((string) ($meta['story_point_1'] ?? '')),
                trim((string) ($meta['story_point_2'] ?? '')),
                trim((string) ($meta['story_point_3'] ?? '')),
            ])->filter(fn ($point) => $point !== '')->values()->all();
        }

        $clinicHours = collect($meta['clinic_hours'] ?? [])
            ->map(function ($row) {
                $day = trim((string) data_get($row, 'day', ''));
                $time = trim((string) data_get($row, 'time', ''));

                if ($day === '' && $time === '') {
                    return null;
                }

                return [
                    'day' => $day,
                    'time' => $time,
                ];
            })
            ->filter()
            ->values()
            ->all();

        $features = collect($meta['features'] ?? [])
            ->map(function ($row) {
                $icon = trim((string) data_get($row, 'icon', ''));
                $title = trim((string) data_get($row, 'title', ''));
                $text = trim((string) data_get($row, 'text', ''));

                if ($icon === '' && $title === '' && $text === '') {
                    return null;
                }

                return [
                    'icon' => $icon,
                    'title' => $title,
                    'text' => $text,
                ];
            })
            ->filter()
            ->values()
            ->all();

        if ($features === []) {
            $legacyFeatures = [
                [
                    'title' => trim((string) ($meta['feature_1_title'] ?? '')),
                    'text' => trim((string) ($meta['feature_1_text'] ?? '')),
                ],
                [
                    'title' => trim((string) ($meta['feature_2_title'] ?? '')),
                    'text' => trim((string) ($meta['feature_2_text'] ?? '')),
                ],
            ];
            $features = collect($legacyFeatures)
                ->filter(fn ($row) => ($row['title'] ?? '') !== '' || ($row['text'] ?? '') !== '')
                ->values()
                ->all();
        }

        $listPoints = collect($meta['list_points'] ?? [])
            ->map(fn ($point) => trim((string) $point))
            ->filter(fn ($point) => $point !== '')
            ->values()
            ->all();

        if ($listPoints === []) {
            $listPoints = collect([
                trim((string) ($meta['list_point_1'] ?? '')),
                trim((string) ($meta['list_point_2'] ?? '')),
                trim((string) ($meta['list_point_3'] ?? '')),
                trim((string) ($meta['list_point_4'] ?? '')),
            ])->filter(fn ($point) => $point !== '')->values()->all();
        }

        $afterQuoteParagraphs = collect($meta['about_after_quote_paragraphs'] ?? [])
            ->map(fn ($paragraph) => trim((string) $paragraph))
            ->filter(fn ($paragraph) => $paragraph !== '')
            ->values()
            ->all();

        return [
            'story_points' => $storyPoints,
            'features' => $features,
            'list_points' => $listPoints,
            'home_bottom_text' => trim((string) ($meta['home_bottom_text'] ?? '')),
            'about_highlight_quote' => trim((string) ($meta['about_highlight_quote'] ?? '')),
            'about_after_quote_paragraphs' => $afterQuoteParagraphs,
            'vision_title' => trim((string) ($meta['vision_title'] ?? '')),
            'vision_text' => trim((string) ($meta['vision_text'] ?? '')),
            'mission_title' => trim((string) ($meta['mission_title'] ?? '')),
            'mission_text' => trim((string) ($meta['mission_text'] ?? '')),
            'about_footer_title' => trim((string) ($meta['about_footer_title'] ?? '')),
            'about_footer_subtitle' => trim((string) ($meta['about_footer_subtitle'] ?? '')),
            'clinic_hours_title' => trim((string) ($meta['clinic_hours_title'] ?? '')),
            'clinic_hours' => $clinicHours,
            'why_tagline' => trim((string) ($meta['why_tagline'] ?? '')),
            'why_title' => trim((string) ($meta['why_title'] ?? '')),
            'why_text' => trim((string) ($meta['why_text'] ?? '')),
            'progress_1_label' => trim((string) ($meta['progress_1_label'] ?? '')),
            'progress_1_value' => (int) ($meta['progress_1_value'] ?? 0),
            'progress_2_label' => trim((string) ($meta['progress_2_label'] ?? '')),
            'progress_2_value' => (int) ($meta['progress_2_value'] ?? 0),
            'help_label' => trim((string) ($meta['help_label'] ?? '')),
            'help_phone' => trim((string) ($meta['help_phone'] ?? '')),
            'stats_1_value' => (int) ($meta['stats_1_value'] ?? 0),
            'stats_1_label' => trim((string) ($meta['stats_1_label'] ?? '')),
            'stats_2_value' => (int) ($meta['stats_2_value'] ?? 0),
            'stats_2_label' => trim((string) ($meta['stats_2_label'] ?? '')),
            'stats_3_value' => (int) ($meta['stats_3_value'] ?? 0),
            'stats_3_label' => trim((string) ($meta['stats_3_label'] ?? '')),
            'stats_4_value' => (int) ($meta['stats_4_value'] ?? 0),
            'stats_4_label' => trim((string) ($meta['stats_4_label'] ?? '')),
            'cta_tagline' => trim((string) ($meta['cta_tagline'] ?? '')),
            'cta_title' => trim((string) ($meta['cta_title'] ?? '')),
            'cta_primary_text' => trim((string) ($meta['cta_primary_text'] ?? '')),
            'cta_primary_url' => trim((string) ($meta['cta_primary_url'] ?? '')),
            'cta_secondary_text' => trim((string) ($meta['cta_secondary_text'] ?? '')),
            'cta_secondary_url' => trim((string) ($meta['cta_secondary_url'] ?? '')),
        ];
    }
}
