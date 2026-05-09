<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class DoctorProfileController extends Controller
{
    public function edit(): View
    {
        return view('doctor.profile.edit', [
            'doctor' => auth('doctor')->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $doctor = $request->user('doctor');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('doctors', 'email')->ignore($doctor->id)],
            'phone' => ['nullable', 'string', 'max:32'],
            'specialty' => ['nullable', 'string', 'max:255'],
            'license_no' => ['nullable', 'string', 'max:255'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:80'],
            'bio' => ['nullable', 'string', 'max:5000'],
            'social_existing' => ['nullable', 'array'],
            'social_existing.*' => ['nullable', 'url', 'max:255'],
            'social_links' => ['nullable', 'array'],
            'social_links.*.platform' => ['nullable', 'string', 'max:50'],
            'social_links.*.url' => ['nullable', 'url', 'max:255'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);

        $data = collect($validated)->except(['photo', 'social_links'])->all();

        $normalizedSocialLinks = [];
        foreach ((array) ($validated['social_existing'] ?? []) as $platform => $url) {
            $platform = trim((string) $platform);
            $url = trim((string) $url);
            if ($platform === '' || $url === '') {
                continue;
            }
            $normalizedSocialLinks[$platform] = $url;
        }

        foreach ((array) ($validated['social_links'] ?? []) as $row) {
            $platform = trim((string) ($row['platform'] ?? ''));
            $url = trim((string) ($row['url'] ?? ''));
            if ($platform === '' || $url === '') {
                continue;
            }
            $normalizedSocialLinks[$platform] = $url;
        }

        $data['social_links'] = $normalizedSocialLinks === [] ? null : $normalizedSocialLinks;

        if ($request->hasFile('photo')) {
            $this->removeStoredDoctorImage($doctor->image_path);

            // Use uploads/doctors — NOT public/doctor/profile, which shadows the /doctor/profile route on many servers.
            $dir = public_path('uploads/doctors');
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $file = $request->file('photo');
            $ext = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'jpg';
            $filename = $doctor->id.'_'.uniqid('', true).'.'.$ext;
            $file->move($dir, $filename);

            $data['image_path'] = 'uploads/doctors/'.$filename;
        }

        $doctor->fill($data);
        $doctor->save();

        return redirect()->route('doctor.profile')->with('success', __('Profile updated successfully.'));
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $doctor = $request->user('doctor');

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if (! Hash::check($validated['current_password'], $doctor->password)) {
            return back()
                ->withErrors(['current_password' => __('The current password is incorrect.')])
                ->withInput($request->only('current_password'));
        }

        $doctor->forceFill([
            'password' => $validated['password'],
        ])->save();

        return redirect()->route('doctor.profile')->with('success', __('Password updated successfully.'));
    }

    private function removeStoredDoctorImage(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        $normalized = ltrim($path, '/');

        if (str_starts_with($normalized, 'uploads/doctors/')) {
            $fullPath = public_path($normalized);
            if (is_file($fullPath)) {
                unlink($fullPath);
            }

            return;
        }

        if (str_starts_with($normalized, 'doctor/profile/')) {
            $fullPath = public_path($normalized);
            if (is_file($fullPath)) {
                unlink($fullPath);
            }

            return;
        }

        Storage::disk('public')->delete($normalized);
    }
}
