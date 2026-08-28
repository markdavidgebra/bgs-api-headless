<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Concerns\DoctorPortalResponses;
use App\Http\Controllers\Controller;
use App\Models\Medication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminMedicationsController extends Controller
{
    use DoctorPortalResponses;

    public function index(Request $request): JsonResponse
    {
        $query = Medication::query()->orderBy('name');

        if ($request->filled('search')) {
            $term = $request->string('search')->toString();
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('generic_name', 'like', "%{$term}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $perPage = max(1, min((int) $request->integer('limit', 20), 100));
        $paginator = $query->paginate($perPage)->withQueryString();

        return response()->json([
            'data' => $paginator->getCollection()
                ->map(fn (Medication $m) => $this->medicationPayload($m))
                ->values(),
            'meta' => $this->doctorPaginationMeta($paginator),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $medication = Medication::query()->create($this->validatedMedication($request));

        return response()->json([
            'message' => __('Medication added to the formulary.'),
            'medication' => $this->medicationPayload($medication),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $medication = Medication::query()->findOrFail($id);

        return response()->json([
            'medication' => $this->medicationPayload($medication),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $medication = Medication::query()->findOrFail($id);
        $medication->update($this->validatedMedication($request, $medication));

        return response()->json([
            'message' => __('Medication updated.'),
            'medication' => $this->medicationPayload($medication->fresh()),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $medication = Medication::query()->findOrFail($id);

        if ($medication->prescriptionItems()->exists()) {
            return response()->json([
                'message' => __('This medication is on existing prescriptions. Mark it inactive instead of deleting it.'),
            ], 422);
        }

        $medication->delete();

        return response()->json([
            'message' => __('Medication removed.'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatedMedication(Request $request, ?Medication $medication = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'generic_name' => ['nullable', 'string', 'max:255'],
            'strength' => ['nullable', 'string', 'max:100'],
            'form' => ['nullable', 'string', 'max:100'],
            'route' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_controlled' => ['nullable', 'boolean'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);
    }
}
