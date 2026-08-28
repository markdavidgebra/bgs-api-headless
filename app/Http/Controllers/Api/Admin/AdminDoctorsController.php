<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Concerns\DoctorPortalResponses;
use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminDoctorsController extends Controller
{
    use DoctorPortalResponses;

    public function index(Request $request): JsonResponse
    {
        $query = Doctor::query()->orderBy('name');

        if ($request->filled('search')) {
            $term = $request->string('search')->toString();
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%")
                    ->orWhere('specialty', 'like', "%{$term}%")
                    ->orWhere('license_no', 'like', "%{$term}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $perPage = max(1, min((int) $request->integer('limit', 15), 100));
        $paginator = $query->paginate($perPage)->withQueryString();

        return response()->json([
            'data' => $paginator->getCollection()
                ->map(fn (Doctor $d) => $this->doctorPayload($d))
                ->values(),
            'meta' => $this->doctorPaginationMeta($paginator),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validatedDoctor($request);

        $plainPassword = Str::password(12);

        $doctor = Doctor::query()->create([
            ...collect($validated)->except(['photo'])->all(),
            'password' => $plainPassword,
            'pending_password_plain' => Crypt::encryptString($plainPassword),
            'status' => 'pending',
            'approved_at' => null,
        ]);

        $this->storePhoto($request, $doctor);

        return response()->json([
            'message' => __('Doctor created and saved as pending approval.'),
            'doctor' => $this->doctorPayload($doctor->fresh()),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $doctor = Doctor::query()->findOrFail($id);

        return response()->json([
            'doctor' => $this->doctorPayload($doctor),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $doctor = Doctor::query()->findOrFail($id);
        $validated = $this->validatedDoctor($request, $doctor);

        $data = collect($validated)->except(['photo', 'password', 'remove_photo'])->all();

        if (! empty($validated['password'] ?? null)) {
            if ($doctor->isActive()) {
                $data['password'] = $validated['password'];
                $data['pending_password_plain'] = null;
            } else {
                $data['pending_password_plain'] = Crypt::encryptString($validated['password']);
            }
        }

        if ($request->boolean('remove_photo')) {
            $this->removePhoto($doctor->image_path);
            $data['image_path'] = null;
        }

        $doctor->update($data);
        $this->storePhoto($request, $doctor);

        return response()->json([
            'message' => __('Doctor updated.'),
            'doctor' => $this->doctorPayload($doctor->fresh()),
        ]);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $doctor = Doctor::query()->findOrFail($id);
        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(['pending', 'active', 'inactive'])],
        ]);

        $targetStatus = strtolower(trim($validated['status']));
        $previousStatus = strtolower((string) ($doctor->status ?? 'pending'));
        $payload = ['status' => $targetStatus];
        $approvalPassword = null;

        if ($targetStatus === 'active') {
            if ($previousStatus !== 'active') {
                $payload['approved_at'] = now();
            }

            if (! empty($doctor->pending_password_plain)) {
                try {
                    $approvalPassword = Crypt::decryptString($doctor->pending_password_plain);
                    $payload['password'] = $approvalPassword;
                    $payload['pending_password_plain'] = null;
                } catch (\Throwable) {
                    // Keep the current password if decrypt fails.
                }
            }

            if ($approvalPassword === null || $approvalPassword === '') {
                $approvalPassword = Str::password(12);
                $payload['password'] = $approvalPassword;
                $payload['pending_password_plain'] = null;
            }
        } else {
            $payload['approved_at'] = null;
        }

        $doctor->update($payload);

        return response()->json([
            'message' => __('Doctor status updated.'),
            'doctor' => $this->doctorPayload($doctor->fresh()),
            'password' => $targetStatus === 'active' && $previousStatus !== 'active' ? $approvalPassword : null,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $doctor = Doctor::query()->findOrFail($id);
        $this->removePhoto($doctor->image_path);
        $doctor->delete();

        return response()->json([
            'message' => __('Doctor deleted.'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatedDoctor(Request $request, ?Doctor $doctor = null): array
    {
        $emailRule = Rule::unique('doctors', 'email');
        if ($doctor) {
            $emailRule = $emailRule->ignore($doctor->id);
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', $emailRule],
            'phone' => ['nullable', 'string', 'max:32'],
            'specialty' => ['nullable', 'string', 'max:255'],
            'license_no' => ['nullable', 'string', 'max:255'],
            'prc_expiry' => ['nullable', 'date'],
            'ptr_no' => ['nullable', 'string', 'max:255'],
            's2_license_no' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:5000'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'remove_photo' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);
    }

    protected function storePhoto(Request $request, Doctor $doctor): void
    {
        if (! $request->hasFile('photo')) {
            return;
        }

        $this->removePhoto($doctor->image_path);

        $dir = public_path('uploads/doctor-portal');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $file = $request->file('photo');
        $ext = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'jpg';
        $filename = $doctor->id.'_'.uniqid('', true).'.'.$ext;
        $file->move($dir, $filename);

        $doctor->update(['image_path' => 'uploads/doctor-portal/'.$filename]);
    }

    protected function removePhoto(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        $normalized = ltrim($path, '/');
        if (str_starts_with($normalized, 'uploads/')) {
            $fullPath = public_path($normalized);
            if (is_file($fullPath)) {
                unlink($fullPath);
            }

            return;
        }

        Storage::disk('public')->delete($normalized);
    }
}
