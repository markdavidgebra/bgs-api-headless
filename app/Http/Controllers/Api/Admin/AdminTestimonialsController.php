<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Admin\TestimonialsController;
use App\Http\Controllers\Api\Concerns\ConvertsAdminWebResponses;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminTestimonialsController extends Controller
{
    use ConvertsAdminWebResponses;

    public function index(): JsonResponse
    {
        return $this->viewDataJson(app(TestimonialsController::class)->index());
    }

    public function create(): JsonResponse
    {
        return $this->viewDataJson(app(TestimonialsController::class)->create());
    }

    public function store(Request $request): JsonResponse
    {
        return $this->adminWebJson(app(TestimonialsController::class)->store($request), 201);
    }

    public function show(int|string $id): JsonResponse
    {
        return $this->viewDataJson(app(TestimonialsController::class)->show($id));
    }

    public function edit(int|string $id): JsonResponse
    {
        return $this->viewDataJson(app(TestimonialsController::class)->edit($id));
    }

    public function update(Request $request, int|string $id): JsonResponse
    {
        return $this->adminWebJson(app(TestimonialsController::class)->update($request, $id));
    }

    public function destroy(int|string $id): JsonResponse
    {
        return $this->adminWebJson(app(TestimonialsController::class)->destroy($id));
    }
}
