<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Admin\StaffsController;
use App\Http\Controllers\Api\Concerns\AdminPortalResponses;
use App\Http\Controllers\Api\Concerns\ConvertsAdminWebResponses;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Support\AdminPermissions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminManagersController extends Controller
{
    use AdminPortalResponses;
    use ConvertsAdminWebResponses;

    public function index(Request $request): JsonResponse
    {
        $query = Admin::query()
            ->notInventoryOfficers()
            ->whereRaw('LOWER(role) = ?', ['manager'])
            ->orderBy('name');

        if ($request->filled('search')) {
            $term = $request->string('search')->toString();
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
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
            'role_options' => ['manager'],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->merge(['role' => 'manager']);

        return $this->adminWebJson(
            app(StaffsController::class)->store($request),
            201
        );
    }

    public function show(int $id): JsonResponse
    {
        $manager = $this->managerAccount($id);

        return response()->json([
            'staff' => $this->staffPayload($manager),
        ]);
    }

    public function edit(int $id): JsonResponse
    {
        $manager = $this->managerAccount($id);

        return response()->json([
            'staff' => $this->staffPayload($manager),
            'role_options' => ['manager'],
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $this->managerAccount($id);
        $request->merge(['role' => 'manager']);

        return $this->adminWebJson(
            app(StaffsController::class)->update($request, $id)
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $this->managerAccount($id);

        return $this->adminWebJson(
            app(StaffsController::class)->destroy($id)
        );
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $this->managerAccount($id);

        return $this->adminWebJson(
            app(StaffsController::class)->updateStatus($request, $id)
        );
    }

    private function managerAccount(int $id): Admin
    {
        $admin = Admin::query()->notInventoryOfficers()->findOrFail($id);
        if (! AdminPermissions::isManager($admin)) {
            abort(404);
        }

        return $admin;
    }
}
