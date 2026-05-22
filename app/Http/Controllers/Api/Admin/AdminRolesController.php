<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Admin\AdminRolesController as WebAdminRolesController;
use App\Http\Controllers\Api\Concerns\ConvertsAdminWebResponses;
use App\Http\Controllers\Controller;
use App\Models\AdminRole;
use App\Support\AdminPermissions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminRolesController extends Controller
{
    use ConvertsAdminWebResponses;

    public function index(Request $request): JsonResponse
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

        $perPage = max(1, min((int) $request->integer('limit', 20), 100));
        $paginator = $query->paginate($perPage)->withQueryString();

        return response()->json([
            'data' => $paginator->getCollection()->map(fn (AdminRole $r) => $this->rolePayload($r))->values(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function create(): JsonResponse
    {
        return response()->json([
            'permission_groups' => AdminPermissions::groups(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        return $this->adminWebJson(
            app(WebAdminRolesController::class)->store($request),
            201
        );
    }

    public function edit(int $id): JsonResponse
    {
        $role = AdminRole::query()->findOrFail($id);

        return response()->json([
            'role' => $this->rolePayload($role),
            'permission_groups' => AdminPermissions::groups(),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        return $this->adminWebJson(
            app(WebAdminRolesController::class)->update($request, $id)
        );
    }

    public function destroy(int $id): JsonResponse
    {
        return $this->adminWebJson(
            app(WebAdminRolesController::class)->destroy($id)
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function rolePayload(AdminRole $role): array
    {
        return [
            'id' => $role->id,
            'name' => $role->name,
            'role_value' => $role->role_value,
            'description' => $role->description,
            'permissions' => $role->permissions ?? [],
        ];
    }
}
