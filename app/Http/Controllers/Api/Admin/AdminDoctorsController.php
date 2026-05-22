<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Admin\DoctorsController;
use App\Http\Controllers\Api\Concerns\AdminPortalResponses;
use App\Http\Controllers\Api\Concerns\ConvertsAdminWebResponses;
use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\DoctorRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminDoctorsController extends Controller
{
    use AdminPortalResponses;
    use ConvertsAdminWebResponses;

    public function index(Request $request): JsonResponse
    {
        $query = Doctor::query()->with('doctorRole')->orderBy('name');

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
                ->map(fn (Doctor $d) => $this->doctorPayload($d))
                ->values(),
            'meta' => $this->paginationMeta($paginator),
        ]);
    }

    public function create(): JsonResponse
    {
        return response()->json([
            'doctor_roles' => DoctorRole::query()->orderBy('name')->get()->map(fn (DoctorRole $r) => [
                'id' => $r->id,
                'name' => $r->name,
                'role_value' => $r->role_value,
            ])->values(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        return $this->adminWebJson(
            app(DoctorsController::class)->store($request),
            201
        );
    }

    public function show(int $id): JsonResponse
    {
        $doctor = Doctor::query()
            ->with(['weeklySchedules', 'services', 'doctorRole'])
            ->findOrFail($id);

        return response()->json([
            'doctor' => $this->doctorShowPayload($doctor),
            'doctor_roles' => DoctorRole::query()->orderBy('name')->get()->map(fn (DoctorRole $r) => [
                'id' => $r->id,
                'name' => $r->name,
                'role_value' => $r->role_value,
            ])->values(),
        ]);
    }

    public function edit(int $id): JsonResponse
    {
        $doctor = Doctor::query()->with('doctorRole')->findOrFail($id);

        return response()->json([
            'doctor' => $this->doctorPayload($doctor),
            'doctor_roles' => DoctorRole::query()->orderBy('name')->get()->map(fn (DoctorRole $r) => [
                'id' => $r->id,
                'name' => $r->name,
                'role_value' => $r->role_value,
            ])->values(),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        return $this->adminWebJson(
            app(DoctorsController::class)->update($request, $id)
        );
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        return $this->adminWebJson(
            app(DoctorsController::class)->updateStatus($request, $id)
        );
    }

    public function updateRole(Request $request, int $id): JsonResponse
    {
        return $this->adminWebJson(
            app(DoctorsController::class)->updateRole($request, $id)
        );
    }

    public function destroy(int $id): JsonResponse
    {
        return $this->adminWebJson(
            app(DoctorsController::class)->destroy($id)
        );
    }
}
