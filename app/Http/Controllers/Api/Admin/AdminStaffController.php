<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Admin\StaffsController;
use App\Http\Controllers\Api\Concerns\AdminPortalResponses;
use App\Http\Controllers\Api\Concerns\ConvertsAdminWebResponses;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminStaffController extends Controller
{
    use AdminPortalResponses;
    use ConvertsAdminWebResponses;

    public function index(Request $request): JsonResponse
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

        $perPage = max(1, min((int) $request->integer('limit', 20), 100));
        $paginator = $query->paginate($perPage)->withQueryString();

        return response()->json([
            'data' => $paginator->getCollection()
                ->map(fn (Admin $s) => $this->staffPayload($s))
                ->values(),
            'meta' => $this->paginationMeta($paginator),
        ]);
    }

    public function create(): JsonResponse
    {
        return response()->json([
            'role_options' => $this->roleOptions(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        return $this->adminWebJson(
            app(StaffsController::class)->store($request),
            201
        );
    }

    public function show(int $id): JsonResponse
    {
        $staff = Admin::query()->findOrFail($id);

        return response()->json([
            'staff' => $this->staffPayload($staff),
        ]);
    }

    public function edit(int $id): JsonResponse
    {
        $staff = Admin::query()->findOrFail($id);

        return response()->json([
            'staff' => $this->staffPayload($staff),
            'role_options' => $this->roleOptions(),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        return $this->adminWebJson(
            app(StaffsController::class)->update($request, $id)
        );
    }

    public function destroy(int $id): JsonResponse
    {
        return $this->adminWebJson(
            app(StaffsController::class)->destroy($id)
        );
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        return $this->adminWebJson(
            app(StaffsController::class)->updateStatus($request, $id)
        );
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
