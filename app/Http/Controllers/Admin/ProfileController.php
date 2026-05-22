<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $admin = $request->user('admin');

        return view('admin.profile.edit', [
            'admin' => $admin,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $admin = $request->user('admin');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('admins', 'email')->ignore($admin->id),
            ],
            'photo' => ['nullable', 'image', 'max:499'],
            'remove_photo' => ['nullable', 'boolean'],
        ]);

        $data = collect($validated)->except(['photo', 'remove_photo'])->all();

        if ($request->hasFile('photo')) {
            $this->removeStoredAdminImage($admin->image_path);

            $dir = public_path('uploads/admins');
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $file = $request->file('photo');
            $ext = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'jpg';
            $filename = $admin->id.'_'.uniqid('', true).'.'.$ext;
            $file->move($dir, $filename);

            $data['image_path'] = 'uploads/admins/'.$filename;
        } elseif ($request->boolean('remove_photo') && $admin->image_path) {
            $this->removeStoredAdminImage($admin->image_path);
            $data['image_path'] = null;
        }

        $admin->fill($data)->save();

        return back()->with('status', 'profile-updated');
    }

    private function removeStoredAdminImage(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        $normalized = ltrim($path, '/');

        if (str_starts_with($normalized, 'uploads/admins/')) {
            $fullPath = public_path($normalized);
            if (is_file($fullPath)) {
                unlink($fullPath);
            }

            return;
        }

        Storage::disk('public')->delete($normalized);
    }
}
