<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminRole;
use App\Support\AdminPermissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminRolesController extends Controller
{
    public function index(Request $request): View
    {
        $query = AdminRole::query()->orderBy('name');

        if ($request->filled('search')) {
            $term = $request->string('search')->toString();
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('role_value', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        }

        $roles = $query->paginate(20)->withQueryString();

        return view('admin.roles.index', compact('roles'));
    }

    public function create(): View
    {
        return view('admin.roles.create', [
            'permissionGroups' => AdminPermissions::groups(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('admin_roles', 'name')],
            'role_value' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(AdminPermissions::allKeys())],
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

        if (AdminRole::query()->where('role_value', $roleValue)->exists()) {
            return back()
                ->withInput()
                ->withErrors(['role_value' => __('Role value already exists.')]);
        }

        AdminRole::query()->create([
            'name' => trim($validated['name']),
            'role_value' => $roleValue,
            'description' => trim((string) ($validated['description'] ?? '')) ?: null,
            'permissions' => array_values(array_unique($validated['permissions'] ?? [])),
        ]);

        return redirect()
            ->route('admin.roles.index')
            ->with('status', __('Role created.'));
    }

    public function edit(int $id): View
    {
        $role = AdminRole::query()->findOrFail($id);

        return view('admin.roles.edit', [
            'role' => $role,
            'permissionGroups' => AdminPermissions::groups(),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $role = AdminRole::query()->findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('admin_roles', 'name')->ignore($role->id)],
            'role_value' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(AdminPermissions::allKeys())],
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

        if (AdminRole::query()->where('role_value', $roleValue)->where('id', '!=', $role->id)->exists()) {
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
            ->route('admin.roles.index')
            ->with('status', __('Role updated.'));
    }

    public function destroy(int $id): RedirectResponse
    {
        $role = AdminRole::query()->findOrFail($id);

        $reserved = ['super admin', 'superadmin', 'admin', 'manager', 'ceo', 'developer'];
        if (in_array(strtolower((string) $role->role_value), $reserved, true)) {
            return back()->with('error', __('This role is reserved and cannot be deleted.'));
        }

        $inUse = Admin::query()->where('role', $role->role_value)->exists();
        if ($inUse) {
            return back()->with('error', __('This role is currently assigned to one or more admins and cannot be deleted.'));
        }

        $role->delete();

        return redirect()
            ->route('admin.roles.index')
            ->with('status', __('Role deleted.'));
    }

    private function normalizeRoleValue(string $value): string
    {
        return AdminPermissions::normalizeRole($value);
    }
}
