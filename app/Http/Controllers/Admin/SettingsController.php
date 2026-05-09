<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.settings.index', [
            'admin' => $request->user('admin'),
            'siteLogo' => AppSetting::getValue('site_logo'),
            'siteFavicon' => AppSetting::getValue('site_favicon'),
        ]);
    }

    public function updateLogo(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'site_logo' => ['required', 'image', 'max:1024'],
        ]);

        $existingLogo = AppSetting::getValue('site_logo');
        if ($existingLogo) {
            $this->removeStoredSettingsFile($existingLogo);
        }

        $dir = public_path('uploads/settings');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $file = $validated['site_logo'];
        $ext = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'png';
        $filename = 'site-logo-'.uniqid('', true).'.'.$ext;
        $file->move($dir, $filename);

        AppSetting::setValue('site_logo', 'uploads/settings/'.$filename);

        return back()->with('status', 'site-logo-updated');
    }

    public function updateFavicon(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'site_favicon' => ['required', 'file', 'max:512'],
        ]);

        $file = $validated['site_favicon'];
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: '');
        $allowed = ['ico', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'webp'];
        if (! in_array($ext, $allowed, true)) {
            return back()->withErrors([
                'site_favicon' => 'The favicon must be ICO, PNG, JPG, GIF, SVG, or WebP.',
            ]);
        }

        $existing = AppSetting::getValue('site_favicon');
        if ($existing) {
            $this->removeStoredSettingsFile($existing);
        }

        $dir = public_path('uploads/settings');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = 'site-favicon-'.uniqid('', true).'.'.$ext;
        $file->move($dir, $filename);

        AppSetting::setValue('site_favicon', 'uploads/settings/'.$filename);

        return back()->with('status', 'site-favicon-updated');
    }

    private function removeStoredSettingsFile(string $path): void
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        $normalized = ltrim($path, '/');

        if (str_starts_with($normalized, 'uploads/settings/')) {
            $fullPath = public_path($normalized);
            if (is_file($fullPath)) {
                unlink($fullPath);
            }

            return;
        }

        Storage::disk('public')->delete($normalized);
    }
}
