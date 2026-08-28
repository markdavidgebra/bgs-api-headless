<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClinicalStaffRole;
use App\Support\ClinicalStaffPermissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClinicalStaffRolesController extends Controller
{
    public function index(Request $request): View
    {
        $query = ClinicalStaffRole::query()->orderBy('name');

        if ($request->filled('search')) {
            $term = $request->string('search')->toString();
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('role_value', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        }

        $roles = $query->paginate(20)->withQueryString();

        return view('admin.clinical-staff-roles.index', compact('roles'));
    }

    public function create(): View
    {
        return view('admin.clinical-staff-roles.create', [
            'permissionGroups' => ClinicalStaffPermissions::groups(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('clinical_staff_roles', 'name')],
            'role_value' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(ClinicalStaffPermissions::allKeys())],
        ]);

        $roleValueInput = trim((string) ($validated['role_value'] ?? ''));
        $roleValue = $this->normalizeRoleValue(
            $roleValueInput !== '' ? $roleValueInput : $validated['name']
        );

        if ($roleValue === '') {
            return back()
                ->withInput()
                ->withErrors(['role_value' => __('Role value is invalid.')]);
        }

        if (ClinicalStaffRole::query()->where('role_value', $roleValue)->exists()) {
            return back()
                ->withInput()
                ->withErrors(['role_value' => __('Role value already exists.')]);
        }

        ClinicalStaffRole::query()->create([
            'name' => trim($validated['name']),
            'role_value' => $roleValue,
            'description' => trim((string) ($validated['description'] ?? '')) ?: null,
            'permissions' => array_values(array_unique($validated['permissions'] ?? [])),
        ]);

        return redirect()
            ->route('admin.clinical-staff-roles.index')
            ->with('status', __('Clinical role created.'));
    }

    public function edit(int $id): View
    {
        $role = ClinicalStaffRole::query()->findOrFail($id);

        return view('admin.clinical-staff-roles.edit', [
            'role' => $role,
            'permissionGroups' => ClinicalStaffPermissions::groups(),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $role = ClinicalStaffRole::query()->findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('clinical_staff_roles', 'name')->ignore($role->id)],
            'role_value' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(ClinicalStaffPermissions::allKeys())],
        ]);

        $roleValueInput = trim((string) ($validated['role_value'] ?? ''));
        $roleValue = $this->normalizeRoleValue(
            $roleValueInput !== '' ? $roleValueInput : $validated['name']
        );

        if ($roleValue === '') {
            return back()
                ->withInput()
                ->withErrors(['role_value' => __('Role value is invalid.')]);
        }

        if (ClinicalStaffRole::query()->where('role_value', $roleValue)->where('id', '!=', $role->id)->exists()) {
            return back()
                ->withInput()
                ->withErrors(['role_value' => __('Role value already exists.')]);
        }

        $role->update([
            'name' => trim($validated['name']),
            'role_value' => $roleValue,
            'description' => trim((string) ($validated['description'] ?? '')) ?: null,
            'permissions' => array_values(array_unique($validated['permissions'] ?? [])),
        ]);

        return redirect()
            ->route('admin.clinical-staff-roles.index')
            ->with('status', __('Clinical role updated.'));
    }

    private function normalizeRoleValue(string $value): string
    {
        return ClinicalStaffPermissions::normalizeRole($value);
    }
}
