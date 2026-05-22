<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Admin\ProductsController;
use App\Http\Controllers\Api\Concerns\ConvertsAdminWebResponses;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminProductsController extends Controller
{
    use ConvertsAdminWebResponses;

    public function index(Request $request): JsonResponse
    {
        return $this->viewDataJson(app(ProductsController::class)->index($request));
    }

    public function create(): JsonResponse
    {
        return $this->viewDataJson(app(ProductsController::class)->create());
    }

    public function store(Request $request): JsonResponse
    {
        return $this->adminWebJson(app(ProductsController::class)->store($request), 201);
    }

    public function show(int $id): JsonResponse
    {
        return $this->viewDataJson(app(ProductsController::class)->show($id));
    }

    public function edit(int $id): JsonResponse
    {
        return $this->viewDataJson(app(ProductsController::class)->edit($id));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        return $this->adminWebJson(app(ProductsController::class)->update($request, $id));
    }

    public function destroy(int $id): JsonResponse
    {
        return $this->adminWebJson(app(ProductsController::class)->destroy($id));
    }

    public function categories(Request $request): JsonResponse
    {
        return $this->viewDataJson(app(ProductsController::class)->categories($request));
    }

    public function categoriesCreate(): JsonResponse
    {
        return $this->viewDataJson(app(ProductsController::class)->categoriesCreate());
    }

    public function categoriesStore(Request $request): JsonResponse
    {
        return $this->adminWebJson(app(ProductsController::class)->categoriesStore($request), 201);
    }

    public function inventory(Request $request): JsonResponse
    {
        return $this->viewDataJson(app(ProductsController::class)->inventory($request));
    }

    public function stockMovements(Request $request): JsonResponse
    {
        return $this->viewDataJson(app(ProductsController::class)->stockMovements($request));
    }

    public function pages(): JsonResponse
    {
        return $this->viewDataJson(app(ProductsController::class)->editCatalogPage());
    }

    public function pagesUpdate(Request $request): JsonResponse
    {
        return $this->adminWebJson(app(ProductsController::class)->updateCatalogPage($request));
    }
}
