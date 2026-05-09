<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Support\SiteFooterConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FooterSettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.footer', [
            'footer' => SiteFooterConfig::get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'newsletter_title' => ['nullable', 'string', 'max:1000'],
            'newsletter_email_placeholder' => ['nullable', 'string', 'max:255'],
            'newsletter_blurb' => ['nullable', 'string', 'max:5000'],
            'department_title' => ['nullable', 'string', 'max:255'],
            'contact_title' => ['nullable', 'string', 'max:255'],
            'contact_address_label' => ['nullable', 'string', 'max:255'],
            'contact_address' => ['nullable', 'string', 'max:500'],
            'contact_phone_label' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:100'],
            'contact_email_label' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'string', 'max:255'],
            'page_links_title' => ['nullable', 'string', 'max:255'],
            'copyright_brand' => ['nullable', 'string', 'max:255'],
            'copyright_brand_url' => ['nullable', 'string', 'max:500'],
            'copyright_year' => ['nullable', 'string', 'max:20'],
            'copyright_suffix' => ['nullable', 'string', 'max:255'],
            'social_links' => ['nullable', 'array'],
            'social_links.*.icon' => ['nullable', 'string', 'max:50'],
            'social_links.*.url' => ['nullable', 'string', 'max:500'],
            'department_links' => ['nullable', 'array'],
            'department_links.*.label' => ['nullable', 'string', 'max:255'],
            'department_links.*.url' => ['nullable', 'string', 'max:500'],
            'page_links' => ['nullable', 'array'],
            'page_links.*.label' => ['nullable', 'string', 'max:255'],
            'page_links.*.url' => ['nullable', 'string', 'max:500'],
            'bottom_links' => ['nullable', 'array'],
            'bottom_links.*.label' => ['nullable', 'string', 'max:255'],
            'bottom_links.*.url' => ['nullable', 'string', 'max:500'],
        ]);

        $socialLinks = collect($request->input('social_links', []))
            ->map(function ($row) {
                return [
                    'icon' => trim((string) data_get($row, 'icon', '')),
                    'url' => trim((string) data_get($row, 'url', '')),
                ];
            })
            ->filter(fn ($row) => ($row['icon'] ?? '') !== '' || ($row['url'] ?? '') !== '')
            ->values()
            ->all();

        $departmentLinks = collect($request->input('department_links', []))
            ->map(function ($row) {
                return [
                    'label' => trim((string) data_get($row, 'label', '')),
                    'url' => trim((string) data_get($row, 'url', '')),
                ];
            })
            ->filter(fn ($row) => ($row['label'] ?? '') !== '' || ($row['url'] ?? '') !== '')
            ->values()
            ->all();

        $pageLinks = collect($request->input('page_links', []))
            ->map(function ($row) {
                return [
                    'label' => trim((string) data_get($row, 'label', '')),
                    'url' => trim((string) data_get($row, 'url', '')),
                ];
            })
            ->filter(fn ($row) => ($row['label'] ?? '') !== '' || ($row['url'] ?? '') !== '')
            ->values()
            ->all();

        $bottomLinks = collect($request->input('bottom_links', []))
            ->map(function ($row) {
                return [
                    'label' => trim((string) data_get($row, 'label', '')),
                    'url' => trim((string) data_get($row, 'url', '')),
                ];
            })
            ->filter(fn ($row) => ($row['label'] ?? '') !== '' || ($row['url'] ?? '') !== '')
            ->values()
            ->all();

        $payload = [
            'newsletter_title' => trim((string) ($validated['newsletter_title'] ?? '')),
            'newsletter_email_placeholder' => trim((string) ($validated['newsletter_email_placeholder'] ?? '')),
            'newsletter_blurb' => trim((string) ($validated['newsletter_blurb'] ?? '')),
            'department_title' => trim((string) ($validated['department_title'] ?? '')),
            'department_links' => $departmentLinks,
            'contact_title' => trim((string) ($validated['contact_title'] ?? '')),
            'contact_address_label' => trim((string) ($validated['contact_address_label'] ?? '')),
            'contact_address' => trim((string) ($validated['contact_address'] ?? '')),
            'contact_phone_label' => trim((string) ($validated['contact_phone_label'] ?? '')),
            'contact_phone' => trim((string) ($validated['contact_phone'] ?? '')),
            'contact_email_label' => trim((string) ($validated['contact_email_label'] ?? '')),
            'contact_email' => trim((string) ($validated['contact_email'] ?? '')),
            'page_links_title' => trim((string) ($validated['page_links_title'] ?? '')),
            'page_links' => $pageLinks,
            'social_links' => $socialLinks,
            'copyright_brand' => trim((string) ($validated['copyright_brand'] ?? '')),
            'copyright_brand_url' => trim((string) ($validated['copyright_brand_url'] ?? '')),
            'copyright_year' => trim((string) ($validated['copyright_year'] ?? '')),
            'copyright_suffix' => trim((string) ($validated['copyright_suffix'] ?? '')),
            'bottom_links' => $bottomLinks,
        ];

        AppSetting::setValue(SiteFooterConfig::SETTING_KEY, json_encode($payload));

        return redirect()->route('admin.settings.footer')->with('status', __('Footer settings updated.'));
    }
}
