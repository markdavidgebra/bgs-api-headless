<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Admin\BlogsController;
use App\Http\Controllers\Api\Concerns\ConvertsAdminWebResponses;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminBlogsController extends Controller
{
    use ConvertsAdminWebResponses;

    public function index(): JsonResponse
    {
        return $this->viewDataJson(app(BlogsController::class)->index());
    }

    public function create(): JsonResponse
    {
        return $this->viewDataJson(app(BlogsController::class)->create());
    }

    public function store(Request $request): JsonResponse
    {
        return $this->adminWebJson(app(BlogsController::class)->store($request), 201);
    }

    public function show(int|string $id): JsonResponse
    {
        return $this->viewDataJson(app(BlogsController::class)->show($id));
    }

    public function edit(int|string $id): JsonResponse
    {
        return $this->viewDataJson(app(BlogsController::class)->edit($id));
    }

    public function update(Request $request, int|string $id): JsonResponse
    {
        return $this->adminWebJson(app(BlogsController::class)->update($request, $id));
    }

    public function destroy(int|string $id): JsonResponse
    {
        return $this->adminWebJson(app(BlogsController::class)->destroy($id));
    }
}
