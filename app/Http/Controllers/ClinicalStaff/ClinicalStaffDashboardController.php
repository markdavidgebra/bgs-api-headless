<?php

namespace App\Http\Controllers\ClinicalStaff;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ClinicalStaffNotification;
use Illuminate\Contracts\View\View;

class ClinicalStaffDashboardController extends Controller
{
    public function index(): View
    {
        $doctor = auth('clinical_staff')->user();
        $today = now()->toDateString();

        $baseQuery = Appointment::query()
            ->with(['patient:id,name', 'service:id,name', 'note', 'clinicalStaff:id,name']);

        $todayAppointmentsCount = (clone $baseQuery)
            ->whereDate('appointment_date', $today)
            ->count();

        $upcomingAppointmentsCount = (clone $baseQuery)
            ->whereDate('appointment_date', '>', $today)
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->count();

        $patientsTodayCount = (clone $baseQuery)
            ->whereDate('appointment_date', $today)
            ->distinct('patient_id')
            ->count('patient_id');

        $pendingNotesCount = Appointment::query()
            ->whereDate('appointment_date', '<=', $today)
            ->where(function ($q) {
                $q->whereDoesntHave('note')
                    ->orWhereHas('note', function ($nq) {
                        $nq->whereNull('clinical_notes')
                            ->orWhere('clinical_notes', '');
                    });
            })
            ->count();

        $scheduleToday = (clone $baseQuery)
            ->whereDate('appointment_date', $today)
            ->orderBy('appointment_time')
            ->orderBy('id')
            ->get();

        $upcomingAppointments = (clone $baseQuery)
            ->whereDate('appointment_date', '>=', $today)
            ->whereNotIn('status', ['cancelled'])
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->limit(5)
            ->get();

        $pendingTodayCount = (clone $baseQuery)
            ->whereDate('appointment_date', $today)
            ->where('status', 'pending')
            ->count();

        $notifications = [];
        if ($pendingTodayCount > 0) {
            $notifications[] = $pendingTodayCount.' appointment request(s) are pending today.';
        }
        if ($pendingNotesCount > 0) {
            $notifications[] = $pendingNotesCount.' appointment note(s) still need attention.';
        }
        if ($todayAppointmentsCount === 0) {
            $notifications[] = 'No appointments scheduled for today.';
        }

        $notificationsUnreadCount = ClinicalStaffNotification::query()
            ->forClinicalStaff((int) $doctor->id)
            ->unread()
            ->count();

        return view('clinical-staff.dashboard.index', compact(
            'doctor',
            'todayAppointmentsCount',
            'upcomingAppointmentsCount',
            'patientsTodayCount',
            'pendingNotesCount',
            'scheduleToday',
            'upcomingAppointments',
            'notifications',
            'notificationsUnreadCount',
        ));
    }
}
