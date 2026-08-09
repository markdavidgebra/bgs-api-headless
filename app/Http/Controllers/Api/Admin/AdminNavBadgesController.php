<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Appointment;
use App\Models\Inquiry;
use App\Models\Patient;
use App\Support\AdminPermissions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Powers the sidebar badges for Appointments, Inquiries and Registrations.
 *
 * These are live "needs attention" queue counts, not per-admin "unseen since
 * last visit" counters — a badge stays put while it's viewed and only clears
 * once the underlying item is actually resolved (appointment confirmed/
 * cancelled, registration approved/disapproved, inquiry handled/deleted).
 */
class AdminNavBadgesController extends Controller
{
    /**
     * @return array<string, array{permission: string, count: callable(): int}>
     */
    private function sections(): array
    {
        return [
            'appointments' => [
                'permission' => 'appointments.manage',
                'count' => fn () => Appointment::query()->where('status', 'pending')->count(),
            ],
            'inquiries' => [
                // Inquiries have no status field — every row is an unhandled
                // message until an admin deals with it and deletes it.
                'permission' => 'inquiries.manage',
                'count' => fn () => Inquiry::query()->count(),
            ],
            'registrations' => [
                'permission' => 'registrations.manage',
                'count' => fn () => Patient::query()->where('status', 'pending')->count(),
            ],
        ];
    }

    public function index(Request $request): JsonResponse
    {
        /** @var Admin $admin */
        $admin = $request->user('admin');

        $badges = [];

        foreach ($this->sections() as $key => $section) {
            if (! AdminPermissions::canAccess($admin, $section['permission'])) {
                continue;
            }

            $badges[$key] = ($section['count'])();
        }

        return response()->json(['badges' => $badges]);
    }
}
