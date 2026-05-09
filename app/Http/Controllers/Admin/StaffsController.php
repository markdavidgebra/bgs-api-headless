<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StaffsController extends Controller
{
    public function index(Request $request): View
    {
        $query = Admin::query()->orderBy('name');

        if ($request->filled('search')) {
            $term = $request->string('search')->toString();
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('role', 'like', "%{$term}%");
            });
        }

        $staffs = $query->paginate(20)->withQueryString();

        return view('admin.staff.index', compact('staffs'));
    }

    public function create(): View
    {
        return view('admin.staff.create', [
            'roleOptions' => $this->roleOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $roleOptions = $this->roleOptions();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('admins', 'email')],
            'role' => ['required', 'string', 'max:50', Rule::in($roleOptions)],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $staff = Admin::query()->create([
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'role' => strtolower(trim($validated['role'])),
            'password' => $validated['password'],
        ]);

        return redirect()
            ->route('admin.staffs.show', $staff->id)
            ->with('status', __('Staff account created.'));
    }

    public function show(int $id): View
    {
        $staff = Admin::query()->findOrFail($id);

        return view('admin.staff.show', compact('staff'));
    }

    public function edit(int $id): View
    {
        $staff = Admin::query()->findOrFail($id);

        return view('admin.staff.edit', [
            'staff' => $staff,
            'roleOptions' => $this->roleOptions(),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $staff = Admin::query()->findOrFail($id);
        $roleOptions = $this->roleOptions();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('admins', 'email')->ignore($staff->id)],
            'role' => ['required', 'string', 'max:50', Rule::in($roleOptions)],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $payload = [
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'role' => strtolower(trim($validated['role'])),
        ];

        if (! empty($validated['password'])) {
            $payload['password'] = $validated['password'];
        }

        $staff->update($payload);

        return redirect()
            ->route('admin.staffs.show', $staff->id)
            ->with('status', __('Staff account updated.'));
    }

    /**
     * @return list<string>
     */
    private function roleOptions(): array
    {
        $roles = AdminRole::query()
            ->orderBy('name')
            ->pluck('role_value')
            ->map(static fn (string $role): string => strtolower(trim($role)))
            ->filter(static fn (string $role): bool => $role !== '')
            ->values()
            ->all();

        foreach (['super admin', 'admin'] as $defaultRole) {
            if (! in_array($defaultRole, $roles, true)) {
                $roles[] = $defaultRole;
            }
        }

        sort($roles);

        return $roles;
    }
}
