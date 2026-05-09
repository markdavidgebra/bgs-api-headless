<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\DoctorNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DoctorNotificationController extends Controller
{
    private const TABS = ['all', 'unread', 'appointments', 'follow_ups', 'reminders'];

    public function index(Request $request): View
    {
        $doctor = auth('doctor')->user();
        $tab = in_array($request->query('tab'), self::TABS, true)
            ? $request->query('tab')
            : 'all';

        $query = DoctorNotification::query()
            ->forDoctor((int) $doctor->id)
            ->with(['appointment:id,appointment_no,patient_id,service_id,appointment_date,appointment_time', 'patient:id,name'])
            ->tab($tab)
            ->orderByDesc('created_at');

        $notifications = $query->paginate(15)->withQueryString();

        $unreadCount = DoctorNotification::query()
            ->forDoctor((int) $doctor->id)
            ->unread()
            ->count();

        return view('doctor.notifications.index', compact('notifications', 'tab', 'unreadCount'));
    }

    public function show(DoctorNotification $notification): View
    {
        $this->assertOwnsNotification($notification);

        if ($notification->read_at === null) {
            $notification->forceFill(['read_at' => now()])->save();
        }

        $notification->load(['appointment.patient', 'appointment.service', 'patient']);

        return view('doctor.notifications.show', compact('notification'));
    }

    public function markRead(DoctorNotification $notification): RedirectResponse
    {
        $this->assertOwnsNotification($notification);

        $notification->forceFill(['read_at' => now()])->save();

        return back()->with('success', __('Marked as read.'));
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $doctorId = (int) auth('doctor')->id();

        DoctorNotification::query()
            ->forDoctor($doctorId)
            ->unread()
            ->update(['read_at' => now()]);

        return redirect()
            ->route('doctor.notifications', ['tab' => $request->input('tab', 'all')])
            ->with('success', __('All notifications marked as read.'));
    }

    public function clearRead(Request $request): RedirectResponse
    {
        $doctorId = (int) auth('doctor')->id();

        DoctorNotification::query()
            ->forDoctor($doctorId)
            ->whereNotNull('read_at')
            ->delete();

        return redirect()
            ->route('doctor.notifications', ['tab' => $request->input('tab', 'all')])
            ->with('success', __('Read notifications cleared.'));
    }

    public function destroy(DoctorNotification $notification): RedirectResponse
    {
        $this->assertOwnsNotification($notification);

        $notification->delete();

        return redirect()
            ->route('doctor.notifications')
            ->with('success', __('Notification removed.'));
    }

    private function assertOwnsNotification(DoctorNotification $notification): void
    {
        abort_unless(
            $notification->doctor_id === (int) auth('doctor')->id(),
            403
        );
    }
}
