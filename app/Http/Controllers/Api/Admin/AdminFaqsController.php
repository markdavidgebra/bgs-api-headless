<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Admin\FaqsController;
use App\Http\Controllers\Api\Concerns\ConvertsAdminWebResponses;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminFaqsController extends Controller
{
    use ConvertsAdminWebResponses;

    public function index(): JsonResponse
    {
        return $this->viewDataJson(app(FaqsController::class)->index());
    }

    public function create(): JsonResponse
    {
        return $this->viewDataJson(app(FaqsController::class)->create());
    }

    public function store(Request $request): JsonResponse
    {
        return $this->adminWebJson(app(FaqsController::class)->store($request), 201);
    }

    public function edit(int|string $id): JsonResponse
    {
        return $this->viewDataJson(app(FaqsController::class)->edit($id));
    }

    public function update(Request $request, int|string $id): JsonResponse
    {
        return $this->adminWebJson(app(FaqsController::class)->update($request, $id));
    }

    public function destroy(int|string $id): JsonResponse
    {
        return $this->adminWebJson(app(FaqsController::class)->destroy($id));
    }
}
