<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Admin\ServicesController;
use App\Http\Controllers\Api\Concerns\ConvertsAdminWebResponses;
use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminServicesController extends Controller
{
    use ConvertsAdminWebResponses;

    public function index(Request $request): JsonResponse
    {
        return $this->viewDataJson(app(ServicesController::class)->index($request));
    }

    public function create(): JsonResponse
    {
        $view = app(ServicesController::class)->create();

        return response()->json(array_merge($view->getData(), [
            'icon_options' => $this->iconOptionsList(Service::iconClassSelectOptions()),
            'service_statuses' => ['active', 'inactive'],
        ]));
    }

    public function store(Request $request): JsonResponse
    {
        return $this->adminWebJson(app(ServicesController::class)->store($request), 201);
    }

    public function show(int|string $id): JsonResponse
    {
        return $this->viewDataJson(app(ServicesController::class)->show($id));
    }

    public function edit(int|string $id): JsonResponse
    {
        $view = app(ServicesController::class)->edit($id);
        $data = $view->getData();
        $service = $data['service'] ?? null;
        $savedIcon = is_object($service) ? ($service->icon_class ?? null) : null;

        return response()->json(array_merge($data, [
            'icon_options' => $this->iconOptionsList(Service::iconClassSelectOptionsForEdit($savedIcon)),
            'service_statuses' => ['active', 'inactive'],
        ]));
    }

    public function update(Request $request, int|string $id): JsonResponse
    {
        return $this->adminWebJson(app(ServicesController::class)->update($request, $id));
    }

    public function destroy(int|string $id): JsonResponse
    {
        return $this->adminWebJson(app(ServicesController::class)->destroy($id));
    }

    private function viewDataJson(View $view): JsonResponse
    {
        return response()->json($view->getData());
    }

    /**
     * Normalize the `class => label` map used by the Blade pickers into a flat list of
     * `{ value, label }` objects which is friendlier for a React `<select>` consumer.
     *
     * @param  array<string, string>  $map
     * @return list<array{value:string,label:string}>
     */
    private function iconOptionsList(array $map): array
    {
        $out = [];
        foreach ($map as $value => $label) {
            $out[] = [
                'value' => (string) $value,
                'label' => (string) $label,
            ];
        }

        return $out;
    }
}
