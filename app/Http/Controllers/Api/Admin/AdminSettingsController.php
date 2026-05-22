<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Api\Concerns\ConvertsAdminWebResponses;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSettingsController extends Controller
{
    use ConvertsAdminWebResponses;

    public function index(Request $request): JsonResponse
    {
        return $this->viewDataJson(app(SettingsController::class)->index($request));
    }

    public function updateLogo(Request $request): JsonResponse
    {
        return $this->adminWebJson(app(SettingsController::class)->updateLogo($request));
    }

    public function updateFavicon(Request $request): JsonResponse
    {
        return $this->adminWebJson(app(SettingsController::class)->updateFavicon($request));
    }
}
