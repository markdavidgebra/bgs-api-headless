<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Admin\SlidesController;
use App\Http\Controllers\Api\Concerns\ConvertsAdminWebResponses;
use App\Http\Controllers\Controller;
use App\Models\Slide;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSlidesController extends Controller
{
    use ConvertsAdminWebResponses;

    public function index(): JsonResponse
    {
        return $this->viewDataJson(app(SlidesController::class)->index());
    }

    public function create(): JsonResponse
    {
        return $this->viewDataJson(app(SlidesController::class)->create());
    }

    public function store(Request $request): JsonResponse
    {
        return $this->adminWebJson(app(SlidesController::class)->store($request), 201);
    }

    public function edit(int|string $id): JsonResponse
    {
        return $this->viewDataJson(app(SlidesController::class)->edit($id));
    }

    public function update(Request $request, int|string $id): JsonResponse
    {
        return $this->adminWebJson(app(SlidesController::class)->update($request, $id));
    }

    public function destroy(int|string $id): JsonResponse
    {
        return $this->adminWebJson(app(SlidesController::class)->destroy($id));
    }

    public function moveUp(Slide $slide): JsonResponse
    {
        return $this->adminWebJson(app(SlidesController::class)->moveUp($slide));
    }

    public function moveDown(Slide $slide): JsonResponse
    {
        return $this->adminWebJson(app(SlidesController::class)->moveDown($slide));
    }
}
