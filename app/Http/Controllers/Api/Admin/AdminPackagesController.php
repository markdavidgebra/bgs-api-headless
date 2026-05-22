<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Admin\PackagesController;
use App\Http\Controllers\Api\Concerns\ConvertsAdminWebResponses;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminPackagesController extends Controller
{
    use ConvertsAdminWebResponses;

    public function index(Request $request): JsonResponse
    {
        return $this->viewDataJson(app(PackagesController::class)->index($request));
    }

    public function create(): JsonResponse
    {
        return $this->viewDataJson(app(PackagesController::class)->create());
    }

    public function store(Request $request): JsonResponse
    {
        return $this->adminWebJson(app(PackagesController::class)->store($request), 201);
    }

    public function show(string $id): JsonResponse
    {
        return $this->viewDataJson(app(PackagesController::class)->show($id));
    }

    public function edit(string $id): JsonResponse
    {
        return $this->viewDataJson(app(PackagesController::class)->edit($id));
    }

    public function update(Request $request, string $id): JsonResponse
    {
        return $this->adminWebJson(app(PackagesController::class)->update($request, $id));
    }

    public function destroy(string $id): JsonResponse
    {
        return $this->adminWebJson(app(PackagesController::class)->destroy($id));
    }
}
