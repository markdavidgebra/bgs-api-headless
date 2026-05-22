<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Admin\FooterSettingsController;
use App\Http\Controllers\Api\Concerns\ConvertsAdminWebResponses;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminFooterSettingsController extends Controller
{
    use ConvertsAdminWebResponses;

    public function edit(): JsonResponse
    {
        return $this->viewDataJson(app(FooterSettingsController::class)->edit());
    }

    public function update(Request $request): JsonResponse
    {
        return $this->adminWebJson(app(FooterSettingsController::class)->update($request));
    }
}
