<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Admin\PaymentsController;
use App\Http\Controllers\Api\Concerns\ConvertsAdminWebResponses;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminPaymentsController extends Controller
{
    use ConvertsAdminWebResponses;

    public function index(Request $request): JsonResponse
    {
        return $this->viewDataJson(app(PaymentsController::class)->index($request));
    }

    public function create(): JsonResponse
    {
        return $this->viewDataJson(app(PaymentsController::class)->create());
    }

    public function store(Request $request): JsonResponse
    {
        return $this->adminWebJson(app(PaymentsController::class)->store($request), 201);
    }

    public function show(string $id): JsonResponse
    {
        return $this->viewDataJson(app(PaymentsController::class)->show($id));
    }
}
