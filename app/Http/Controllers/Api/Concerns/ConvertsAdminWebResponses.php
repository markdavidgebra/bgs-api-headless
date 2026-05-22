<?php

namespace App\Http\Controllers\Api\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

trait ConvertsAdminWebResponses
{
    protected function viewDataJson(\Illuminate\View\View $view): JsonResponse
    {
        return response()->json($view->getData());
    }

    protected function adminWebJson(mixed $response, int $successStatus = 200): JsonResponse
    {
        if ($response instanceof RedirectResponse) {
            $message = session('status') ?? session('error') ?? __('OK');
            $status = session()->has('error') ? 422 : $successStatus;

            return response()->json([
                'message' => is_string($message) ? $message : (string) $message,
                'warning' => session('warning'),
            ], $status);
        }

        if ($response instanceof View) {
            return response()->json([
                'message' => __('This action is not available via the API.'),
            ], 501);
        }

        if ($response instanceof JsonResponse) {
            return $response;
        }

        return response()->json([
            'message' => __('OK'),
        ], $successStatus);
    }
}
