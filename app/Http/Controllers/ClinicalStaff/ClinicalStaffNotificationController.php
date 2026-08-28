<?php

namespace App\Http\Controllers\ClinicalStaff;

use App\Http\Controllers\Controller;
use App\Models\ClinicalStaffNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClinicalStaffNotificationController extends Controller
{
    private const TABS = ['all', 'unread', 'appointments', 'follow_ups', 'reminders'];

    public function index(Request $request): View
    {
        $doctor = auth('clinical_staff')->user();
        $tab = in_array($request->query('tab'), self::TABS, true)
            ? $request->query('tab')
            : 'all';

        $query = ClinicalStaffNotification::query()
            ->forClinicalStaff((int) $doctor->id)
            ->with(['appointment:id,appointment_no,patient_id,service_id,appointment_date,appointment_time', 'patient:id,name'])
            ->tab($tab)
            ->orderByDesc('created_at');

        $notifications = $query->paginate(15)->withQueryString();

        $unreadCount = ClinicalStaffNotification::query()
            ->forClinicalStaff((int) $doctor->id)
            ->unread()
            ->count();

        return view('clinical-staff.notifications.index', compact('notifications', 'tab', 'unreadCount'));
    }

    public function show(ClinicalStaffNotification $notification): View
    {
        $this->assertOwnsNotification($notification);

        if ($notification->read_at === null) {
            $notification->forceFill(['read_at' => now()])->save();
        }

        $notification->load(['appointment.patient', 'appointment.service', 'patient']);

        return view('clinical-staff.notifications.show', compact('notification'));
    }

    public function markRead(ClinicalStaffNotification $notification): RedirectResponse
    {
        $this->assertOwnsNotification($notification);

        $notification->forceFill(['read_at' => now()])->save();

        return back()->with('success', __('Marked as read.'));
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $doctorId = (int) auth('clinical_staff')->id();

        ClinicalStaffNotification::query()
            ->forClinicalStaff($doctorId)
            ->unread()
            ->update(['read_at' => now()]);

        return redirect()
            ->route('clinical_staff.notifications', ['tab' => $request->input('tab', 'all')])
            ->with('success', __('All notifications marked as read.'));
    }

    public function clearRead(Request $request): RedirectResponse
    {
        $doctorId = (int) auth('clinical_staff')->id();

        ClinicalStaffNotification::query()
            ->forClinicalStaff($doctorId)
            ->whereNotNull('read_at')
            ->delete();

        return redirect()
            ->route('clinical_staff.notifications', ['tab' => $request->input('tab', 'all')])
            ->with('success', __('Read notifications cleared.'));
    }

    public function destroy(ClinicalStaffNotification $notification): RedirectResponse
    {
        $this->assertOwnsNotification($notification);

        $notification->delete();

        return redirect()
            ->route('clinical_staff.notifications')
            ->with('success', __('Notification removed.'));
    }

    private function assertOwnsNotification(ClinicalStaffNotification $notification): void
    {
        abort_unless(
            $notification->clinical_staff_id === (int) auth('clinical_staff')->id(),
            403
        );
    }
}
