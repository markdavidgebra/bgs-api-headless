<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Admin\SubscriptionsController;
use App\Http\Controllers\Api\Concerns\ConvertsAdminWebResponses;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSubscriptionsController extends Controller
{
    use ConvertsAdminWebResponses;

    public function index(Request $request): JsonResponse
    {
        return $this->viewDataJson(app(SubscriptionsController::class)->index($request));
    }

    public function create(): JsonResponse
    {
        return $this->viewDataJson(app(SubscriptionsController::class)->create());
    }

    public function store(Request $request): JsonResponse
    {
        return $this->adminWebJson(app(SubscriptionsController::class)->store($request), 201);
    }

    public function show(int $id): JsonResponse
    {
        return $this->viewDataJson(app(SubscriptionsController::class)->show($id));
    }

    public function edit(int $id): JsonResponse
    {
        return $this->viewDataJson(app(SubscriptionsController::class)->edit($id));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        return $this->adminWebJson(app(SubscriptionsController::class)->update($request, $id));
    }

    public function destroy(int $id): JsonResponse
    {
        return $this->adminWebJson(app(SubscriptionsController::class)->destroy($id));
    }
}
