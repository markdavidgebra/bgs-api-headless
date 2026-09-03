<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\NewStaffDraftAdminMail;
use App\Mail\StaffAccountApprovedMail;
use App\Mail\StaffAccountCreatedMail;
use App\Models\Admin;
use App\Models\AdminRole;
use App\Support\AdminNotificationRecipients;
use App\Support\AdminPermissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StaffsController extends Controller
{
    public function index(Request $request): View
    {
        $query = Admin::query()->notInventoryOfficers()->orderBy('name');
        $this->applyVisibleStaffScope($query, Auth::guard('admin')->user());

        if ($request->filled('search')) {
            $term = $request->string('search')->toString();
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('role', 'like', "%{$term}%");
            });
        }

        $staffs = $query->paginate(20)->withQueryString();

        return view('admin.staff.index', compact('staffs'));
    }

    public function create(): View
    {
        return view('admin.staff.create', [
            'roleOptions' => $this->roleOptions(Auth::guard('admin')->user()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $actor = Auth::guard('admin')->user();
        $roleOptions = $this->roleOptions($actor);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('admins', 'email')],
            'role' => ['required', 'string', 'max:50', Rule::in($roleOptions)],
        ]);

        $this->assertCanAssignRole($actor, $validated['role']);

        $temporaryPassword = Str::password(12);

        $staff = Admin::query()->create([
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'role' => strtolower(trim($validated['role'])),
            'status' => 'draft',
            'approved_at' => null,
            // Store a temporary random password; the real password is activated on approval.
            'password' => Str::password(32),
            'pending_password_plain' => Crypt::encryptString($temporaryPassword),
        ]);

        Mail::to($staff->email)->send(new StaffAccountCreatedMail($staff, $temporaryPassword));

        $notifyEmails = array_values(array_unique(array_merge(
            AdminNotificationRecipients::emailsForPermission('staff.manage'),
            AdminNotificationRecipients::superAdminEmails(),
        )));
        if ($notifyEmails !== []) {
            Mail::to($notifyEmails)->send(new NewStaffDraftAdminMail($staff));
        }

        return redirect()
            ->route('admin.staffs.show', $staff->id)
            ->with('status', __('Staff saved as draft. Approve the staff to activate account login.'));
    }

    public function show(int $id): View
    {
        $staff = $this->adminPortalStaff($id);
        $this->assertCanViewStaff(Auth::guard('admin')->user(), $staff);

        return view('admin.staff.show', compact('staff'));
    }

    public function edit(int $id): View
    {
        $staff = $this->adminPortalStaff($id);
        $this->assertCanViewStaff(Auth::guard('admin')->user(), $staff);

        return view('admin.staff.edit', [
            'staff' => $staff,
            'roleOptions' => $this->roleOptions(Auth::guard('admin')->user()),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $actor = Auth::guard('admin')->user();
        $staff = $this->adminPortalStaff($id);
        $this->assertCanMutateStaff($actor, $staff);
        $roleOptions = $this->roleOptions($actor);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('admins', 'email')->ignore($staff->id)],
            'role' => ['required', 'string', 'max:50', Rule::in($roleOptions)],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $this->assertCanAssignRole($actor, $validated['role'], $staff);

        $payload = [
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'role' => strtolower(trim($validated['role'])),
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

        return redirect()
            ->route('admin.staffs.show', $staff->id)
            ->with('status', __('Staff account updated.'));
    }

    public function destroy(int $id): RedirectResponse
    {
        $currentId = Auth::guard('admin')->id();
        if ($currentId !== null && (int) $id === (int) $currentId) {
            return redirect()
                ->route('admin.staffs')
                ->with('error', __('You cannot delete your own account.'));
        }

        $staff = $this->adminPortalStaff($id);
        $this->assertCanMutateStaff(Auth::guard('admin')->user(), $staff);
        $staff->delete();

        return redirect()
            ->route('admin.staffs')
            ->with('status', __('Staff member deleted.'));
    }

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $staff = $this->adminPortalStaff($id);
        $this->assertCanMutateStaff(Auth::guard('admin')->user(), $staff);
        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(['draft', 'approved', 'disapproved'])],
        ]);
        $targetStatus = strtolower(trim($validated['status']));

        $payload = [
            'status' => $targetStatus,
        ];
        $approvalPassword = null;

        if ($targetStatus === 'approved') {
            if ($staff->status !== 'approved') {
                $payload['approved_at'] = now();
            }

            if (! empty($staff->pending_password_plain)) {
                try {
                    $approvalPassword = Crypt::decryptString($staff->pending_password_plain);
                    $payload['password'] = $approvalPassword;
                    $payload['pending_password_plain'] = null;
                } catch (\Throwable) {
                    // If decrypt fails, keep current password and still approve.
                }
            }
        } else {
            $payload['approved_at'] = null;
        }

        $staff->update($payload);

        if ($targetStatus === 'approved' && $staff->wasChanged('status')) {
            Mail::to($staff->email)->send(new StaffAccountApprovedMail($staff->fresh(), $approvalPassword));
        }

        return redirect()
            ->route('admin.staffs')
            ->with('status', __('Staff status updated.'));
    }

    /**
     * @param  \App\Models\Admin|null  $actor
     * @return list<string>
     */
    public function roleOptions(?Admin $actor = null): array
    {
        $roles = AdminRole::query()
            ->orderBy('name')
            ->pluck('role_value')
            ->map(static fn ($role): string => strtolower(trim((string) $role)))
            ->filter(static fn (string $role): bool => $role !== '')
            ->values()
            ->all();

        foreach (['super admin', 'admin', 'manager', 'ceo', 'developer'] as $defaultRole) {
            if (! in_array($defaultRole, $roles, true)) {
                $roles[] = $defaultRole;
            }
        }

        $roles = array_values(array_filter(
            $roles,
            static fn (string $role): bool => $role !== Admin::INVENTORY_ROLE
                && AdminPermissions::canManagePeopleRole($actor, $role)
        ));

        sort($roles);

        return $roles;
    }

    public function assertCanViewStaff(?Admin $actor, Admin $staff): void
    {
        if (! AdminPermissions::canManagePeopleRole($actor, (string) $staff->role)) {
            abort(404);
        }
    }

    public function assertCanMutateStaff(?Admin $actor, Admin $staff): void
    {
        if (! AdminPermissions::canManagePeopleRole($actor, (string) $staff->role)) {
            abort(403, self::peopleRoleForbiddenMessage((string) $staff->role));
        }
    }

    public function assertCanAssignRole(?Admin $actor, string $role, ?Admin $existing = null): void
    {
        if ($existing) {
            $this->assertCanMutateStaff($actor, $existing);
        }

        if (! AdminPermissions::canManagePeopleRole($actor, $role)) {
            abort(403, self::peopleRoleForbiddenMessage($role));
        }
    }

    public function applyVisibleStaffScope($query, ?Admin $actor)
    {
        $hidden = [];
        foreach (['manager', 'ceo', 'developer'] as $role) {
            if (! AdminPermissions::canManagePeopleRole($actor, $role)) {
                $hidden[] = $role;
            }
        }

        if ($hidden !== []) {
            $placeholders = implode(', ', array_fill(0, count($hidden), '?'));
            $query->whereRaw('LOWER(role) not in ('.$placeholders.')', $hidden);
        }

        return $query;
    }

    private static function peopleRoleForbiddenMessage(string $role): string
    {
        return match (AdminPermissions::normalizeRole($role)) {
            'ceo' => 'Only a developer can manage CEO accounts.',
            'developer' => 'Only a developer can manage developer accounts.',
            default => 'Only the CEO can manage manager accounts.',
        };
    }

    private function adminPortalStaff(int $id): Admin
    {
        return Admin::query()->notInventoryOfficers()->findOrFail($id);
    }
}
