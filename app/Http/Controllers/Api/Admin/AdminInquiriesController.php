<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Admin\InquiriesController;
use App\Http\Controllers\Api\Concerns\AdminPortalResponses;
use App\Http\Controllers\Api\Concerns\ConvertsAdminWebResponses;
use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminInquiriesController extends Controller
{
    use AdminPortalResponses;
    use ConvertsAdminWebResponses;

    public function index(Request $request): JsonResponse
    {
        $perPage = max(1, min((int) $request->integer('limit', 25), 100));
        $paginator = Inquiry::query()
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'data' => $paginator->getCollection()
                ->map(fn (Inquiry $i) => $this->inquiryPayload($i))
                ->values(),
            'meta' => $this->paginationMeta($paginator),
        ]);
    }

    public function show(int|string $id): JsonResponse
    {
        $inquiry = Inquiry::query()->findOrFail($id);

        return response()->json([
            'inquiry' => $this->inquiryPayload($inquiry),
        ]);
    }

    public function destroy(int|string $id): JsonResponse
    {
        return $this->adminWebJson(
            app(InquiriesController::class)->destroy($id)
        );
    }
}
