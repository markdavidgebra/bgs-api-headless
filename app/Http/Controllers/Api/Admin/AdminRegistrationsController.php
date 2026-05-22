<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Admin\PatientRegistrationsController;
use App\Http\Controllers\Api\Concerns\AdminPortalResponses;
use App\Http\Controllers\Api\Concerns\ConvertsAdminWebResponses;
use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminRegistrationsController extends Controller
{
    use AdminPortalResponses;
    use ConvertsAdminWebResponses;

    public function index(Request $request): JsonResponse
    {
        $query = Patient::query()
            ->where('status', 'pending')
            ->orderByDesc('created_at');

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
                ->map(fn (Patient $p) => array_merge(
                    $this->patientSummaryPayload($p),
                    ['created_at' => $p->created_at?->toIso8601String()],
                ))
                ->values(),
            'meta' => $this->paginationMeta($paginator),
        ]);
    }

    public function approve(int $id): JsonResponse
    {
        return $this->adminWebJson(
            app(PatientRegistrationsController::class)->approve($id)
        );
    }

    public function disapprove(int $id): JsonResponse
    {
        return $this->adminWebJson(
            app(PatientRegistrationsController::class)->disapprove($id)
        );
    }
}
