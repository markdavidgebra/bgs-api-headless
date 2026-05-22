<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Api\Concerns\ConvertsAdminWebResponses;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminReportsController extends Controller
{
    use ConvertsAdminWebResponses;

    public function index(): JsonResponse
    {
        return $this->viewDataJson(app(ReportsController::class)->index());
    }

    public function revenue(Request $request): JsonResponse
    {
        return $this->viewDataJson(app(ReportsController::class)->revenue($request));
    }

    public function appointments(Request $request): JsonResponse
    {
        return $this->viewDataJson(app(ReportsController::class)->appointments($request));
    }

    public function services(Request $request): JsonResponse
    {
        return $this->viewDataJson(app(ReportsController::class)->services($request));
    }

    public function patients(Request $request): JsonResponse
    {
        return $this->viewDataJson(app(ReportsController::class)->patients($request));
    }

    public function subscriptions(Request $request): JsonResponse
    {
        return $this->viewDataJson(app(ReportsController::class)->subscriptions($request));
    }
}
