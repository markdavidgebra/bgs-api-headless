<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Concerns\AdminPortalResponses;
use App\Http\Controllers\Controller;
use App\Models\ClinicalStaffRole;
use App\Support\ClinicalStaffPermissions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminClinicalStaffRolesController extends Controller
{
    use AdminPortalResponses;

    public function index(Request $request): JsonResponse
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

        $perPage = max(1, min((int) $request->integer('limit', 20), 100));
        $paginator = $query->paginate($perPage)->withQueryString();

        return response()->json([
            'data' => $paginator->getCollection()->map(fn (ClinicalStaffRole $r) => $this->rolePayload($r))->values(),
            'meta' => $this->paginationMeta($paginator),
        ]);
    }

    public function create(): JsonResponse
    {
        return response()->json([
            'permission_groups' => ClinicalStaffPermissions::groups(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('doctor_roles', 'name')],
            'role_value' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(ClinicalStaffPermissions::allKeys())],
        ]);

        $roleValue = $this->resolveRoleValue(
            trim((string) ($validated['role_value'] ?? '')),
            $validated['name']
        );

        if ($roleValue === '') {
            throw ValidationException::withMessages([
                'role_value' => [__('Role value is invalid.')],
            ]);
        }

        if (ClinicalStaffRole::query()->where('role_value', $roleValue)->exists()) {
            throw ValidationException::withMessages([
                'role_value' => [__('Role value already exists.')],
            ]);
        }

        $role = ClinicalStaffRole::query()->create([
            'name' => trim($validated['name']),
            'role_value' => $roleValue,
            'description' => trim((string) ($validated['description'] ?? '')) ?: null,
            'permissions' => array_values(array_unique($validated['permissions'] ?? [])),
        ]);

        return response()->json([
            'message' => __('Clinical role created.'),
            'role' => $this->rolePayload($role),
        ], 201);
    }

    public function edit(int $id): JsonResponse
    {
        $role = ClinicalStaffRole::query()->findOrFail($id);

        return response()->json([
            'role' => $this->rolePayload($role),
            'permission_groups' => ClinicalStaffPermissions::groups(),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $role = ClinicalStaffRole::query()->findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('doctor_roles', 'name')->ignore($role->id)],
            'role_value' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(ClinicalStaffPermissions::allKeys())],
        ]);

        $roleValue = $this->resolveRoleValue(
            trim((string) ($validated['role_value'] ?? '')),
            $validated['name']
        );

        if ($roleValue === '') {
            throw ValidationException::withMessages([
                'role_value' => [__('Role value is invalid.')],
            ]);
        }

        if (ClinicalStaffRole::query()->where('role_value', $roleValue)->where('id', '!=', $role->id)->exists()) {
            throw ValidationException::withMessages([
                'role_value' => [__('Role value already exists.')],
            ]);
        }

        $role->update([
            'name' => trim($validated['name']),
            'role_value' => $roleValue,
            'description' => trim((string) ($validated['description'] ?? '')) ?: null,
            'permissions' => array_values(array_unique($validated['permissions'] ?? [])),
        ]);

        return response()->json([
            'message' => __('Clinical role updated.'),
            'role' => $this->rolePayload($role->fresh()),
        ]);
    }

    private function resolveRoleValue(string $roleValueInput, string $name): string
    {
        $source = $roleValueInput !== '' ? $roleValueInput : $name;

        return ClinicalStaffPermissions::normalizeRole($source);
    }

    /**
     * @return array<string, mixed>
     */
    private function rolePayload(ClinicalStaffRole $role): array
    {
        $permissions = is_array($role->permissions) ? $role->permissions : [];

        return [
            'id' => $role->id,
            'name' => $role->name,
            'role_value' => $role->role_value,
            'description' => $role->description,
            'permissions' => $permissions,
            'permissions_count' => count($permissions),
        ];
    }
}
