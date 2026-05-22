<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminDashboardService;
use Illuminate\Http\JsonResponse;

class AdminDashboardController extends Controller
{
    public function __construct(
        private readonly AdminDashboardService $dashboard,
    ) {}

    public function index(): JsonResponse
    {
        return response()->json($this->dashboard->payload());
    }
}
