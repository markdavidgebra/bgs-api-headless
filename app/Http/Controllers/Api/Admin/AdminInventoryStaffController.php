<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Concerns\AdminPortalResponses;
use App\Http\Controllers\Controller;
use App\Mail\NewStaffDraftAdminMail;
use App\Mail\StaffAccountApprovedMail;
use App\Mail\StaffAccountCreatedMail;
use App\Models\Admin;
use App\Support\AdminNotificationRecipients;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminInventoryStaffController extends Controller
{
    use AdminPortalResponses;

    public function index(Request $request): JsonResponse
    {
        $query = Admin::query()->inventoryOfficers()->orderBy('name');

        if ($request->filled('search')) {
            $term = $request->string('search')->toString();
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            });
        }

        $perPage = max(1, min((int) $request->integer('limit', 20), 100));
        $paginator = $query->paginate($perPage)->withQueryString();

        return response()->json([
            'data' => $paginator->getCollection()
                ->map(fn (Admin $s) => $this->staffPayload($s))
                ->values(),
            'meta' => $this->paginationMeta($paginator),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('admins', 'email')],
        ]);

        $temporaryPassword = Str::password(12);

        $staff = Admin::query()->create([
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'role' => Admin::INVENTORY_ROLE,
            'status' => 'draft',
            'approved_at' => null,
            'password' => Str::password(32),
            'pending_password_plain' => Crypt::encryptString($temporaryPassword),
        ]);

        Mail::to($staff->email)->send(new StaffAccountCreatedMail($staff, $temporaryPassword));

        $notifyEmails = array_values(array_unique(array_merge(
            AdminNotificationRecipients::emailsForPermission('inventory_staff.manage'),
            AdminNotificationRecipients::superAdminEmails(),
        )));
        if ($notifyEmails !== []) {
            Mail::to($notifyEmails)->send(new NewStaffDraftAdminMail($staff));
        }

        return response()->json([
            'message' => __('Inventory staff saved as draft. Approve them to activate inventory portal login.'),
            'staff' => $this->staffPayload($staff),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json([
            'staff' => $this->staffPayload($this->inventoryOfficer($id)),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $staff = $this->inventoryOfficer($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('admins', 'email')->ignore($staff->id)],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $payload = [
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'role' => Admin::INVENTORY_ROLE,
        ];

        if (! empty($validated['password'])) {
            if (($staff->status ?? 'draft') === 'approved') {
                $payload['password'] = $validated['password'];
                $payload['pending_password_plain'] = null;
            } else {
                $payload['pending_password_plain'] = Crypt::encryptString($validated['password']);
            }
        }

        $staff->update($payload);

        return response()->json([
            'message' => __('Inventory staff account updated.'),
            'staff' => $this->staffPayload($staff->fresh()),
        ]);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $staff = $this->inventoryOfficer($id);
        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(['draft', 'approved', 'disapproved'])],
        ]);

        $targetStatus = strtolower(trim($validated['status']));
        $previousStatus = strtolower((string) ($staff->status ?? 'draft'));
        $payload = ['status' => $targetStatus];
        $approvalPassword = null;

        if ($targetStatus === 'approved') {
            if ($previousStatus !== 'approved') {
                $payload['approved_at'] = now();
            }

            if (! empty($staff->pending_password_plain)) {
                try {
                    $approvalPassword = Crypt::decryptString($staff->pending_password_plain);
                    $payload['password'] = $approvalPassword;
                    $payload['pending_password_plain'] = null;
                } catch (\Throwable) {
                    // Keep the current password if decrypt fails.
                }
            }

            if ($previousStatus !== 'approved' && ($approvalPassword === null || $approvalPassword === '')) {
                $approvalPassword = Str::password(12);
                $payload['password'] = $approvalPassword;
                $payload['pending_password_plain'] = null;
            }
        } else {
            $payload['approved_at'] = null;
        }

        $staff->update($payload);

        if ($targetStatus === 'approved' && $previousStatus !== 'approved') {
            Mail::to($staff->email)->send(new StaffAccountApprovedMail($staff->fresh(), $approvalPassword));
        }

        return response()->json([
            'message' => __('Inventory staff status updated.'),
            'staff' => $this->staffPayload($staff->fresh()),
            'password' => $targetStatus === 'approved' && $previousStatus !== 'approved' ? $approvalPassword : null,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $currentId = Auth::guard('admin')->id();
        if ($currentId !== null && (int) $id === (int) $currentId) {
            return response()->json([
                'message' => __('You cannot delete your own account.'),
            ], 422);
        }

        $staff = $this->inventoryOfficer($id);
        $staff->delete();

        return response()->json([
            'message' => __('Inventory staff member deleted.'),
        ]);
    }

    private function inventoryOfficer(int $id): Admin
    {
        return Admin::query()->inventoryOfficers()->findOrFail($id);
    }
}
