<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Admin\ClinicalStaffController;
use App\Http\Controllers\Api\Concerns\AdminPortalResponses;
use App\Http\Controllers\Api\Concerns\ConvertsAdminWebResponses;
use App\Http\Controllers\Controller;
use App\Models\ClinicalStaff;
use App\Models\ClinicalStaffRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminClinicalStaffController extends Controller
{
    use AdminPortalResponses;
    use ConvertsAdminWebResponses;

    public function index(Request $request): JsonResponse
    {
        $query = ClinicalStaff::query()->notManagerAlias()->with('role')->orderBy('name');

        if ($request->filled('search')) {
            $term = $request->string('search')->toString();
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%")
                    ->orWhere('specialty', 'like', "%{$term}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('specialty')) {
            $query->where('specialty', 'like', '%'.$request->string('specialty').'%');
        }

        $perPage = max(1, min((int) $request->integer('limit', 15), 100));
        $paginator = $query->paginate($perPage)->withQueryString();

        return response()->json([
            'data' => $paginator->getCollection()
                ->map(fn (ClinicalStaff $d) => $this->clinicalStaffPayload($d))
                ->values(),
            'meta' => $this->paginationMeta($paginator),
        ]);
    }

    public function create(): JsonResponse
    {
        return response()->json([
            'clinical_staff_roles' => ClinicalStaffRole::query()->orderBy('name')->get()->map(fn (ClinicalStaffRole $r) => [
                'id' => $r->id,
                'name' => $r->name,
                'role_value' => $r->role_value,
            ])->values(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        return $this->adminWebJson(
            app(ClinicalStaffController::class)->store($request),
            201
        );
    }

    public function show(int $id): JsonResponse
    {
        $doctor = ClinicalStaff::query()
            ->with(['weeklySchedules', 'services', 'role'])
            ->findOrFail($id);

        return response()->json([
            'clinical_staff' => $this->clinicalStaffShowPayload($doctor),
            'clinical_staff_roles' => ClinicalStaffRole::query()->orderBy('name')->get()->map(fn (ClinicalStaffRole $r) => [
                'id' => $r->id,
                'name' => $r->name,
                'role_value' => $r->role_value,
            ])->values(),
        ]);
    }

    public function edit(int $id): JsonResponse
    {
        $doctor = ClinicalStaff::query()->with('role')->findOrFail($id);

        return response()->json([
            'clinical_staff' => $this->clinicalStaffPayload($doctor),
            'clinical_staff_roles' => ClinicalStaffRole::query()->orderBy('name')->get()->map(fn (ClinicalStaffRole $r) => [
                'id' => $r->id,
                'name' => $r->name,
                'role_value' => $r->role_value,
            ])->values(),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        return $this->adminWebJson(
            app(ClinicalStaffController::class)->update($request, $id)
        );
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        return $this->adminWebJson(
            app(ClinicalStaffController::class)->updateStatus($request, $id)
        );
    }

    public function updateRole(Request $request, int $id): JsonResponse
    {
        return $this->adminWebJson(
            app(ClinicalStaffController::class)->updateRole($request, $id)
        );
    }

    public function destroy(int $id): JsonResponse
    {
        return $this->adminWebJson(
            app(ClinicalStaffController::class)->destroy($id)
        );
    }
}
