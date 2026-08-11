<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Support\AppointmentBookingRules;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Shared appointment-booking helpers used by both the patient self-service
 * portal ({@see \App\Http\Controllers\Api\PatientPortalController}) and the
 * admin-initiated booking flow ({@see \App\Http\Controllers\Api\Admin\AdminAppointmentsController}),
 * so both keep the exact same doctor-availability and numbering rules.
 */
trait BooksAppointments
{
    /**
     * @param  int|list<int>|null  $serviceId
     */
    protected function bookableDoctorsQuery(?string $appointmentDate = null, int|array|null $serviceId = null): Builder
    {
        $q = Doctor::query()
            ->where('status', 'active')
            ->whereHas('weeklySchedules', fn (Builder $sub) => $sub->where('is_active', true));

        $serviceIds = is_array($serviceId)
            ? array_values(array_unique(array_map('intval', $serviceId)))
            : ($serviceId !== null ? [(int) $serviceId] : []);

        foreach ($serviceIds as $sid) {
            if ($sid > 0 && DB::table('doctor_service')->where('service_id', $sid)->exists()) {
                $q->whereHas('services', fn (Builder $sub) => $sub->where('services.id', $sid));
            }
        }

        if ($appointmentDate === null || $appointmentDate === '') {
            return $q->orderBy('name');
        }

        if (AppointmentBookingRules::isClosedWeekday($appointmentDate)) {
            return $q->whereRaw('1 = 0')->orderBy('name');
        }

        try {
            $weekday = (int) Carbon::parse($appointmentDate)->format('N');
        } catch (\Throwable) {
            return $q->whereRaw('1 = 0')->orderBy('name');
        }

        $q->whereHas('weeklySchedules', fn (Builder $sub) => $sub
            ->where('weekday', $weekday)
            ->where('is_active', true));

        $q->whereDoesntHave('blockedDates', fn (Builder $sub) => $sub
            ->whereDate('blocked_date', $appointmentDate));

        return $q->orderBy('name');
    }

    protected function generateAppointmentNo(): string
    {
        $year = now()->format('Y');
        $prefix = 'APT-'.$year.'-';

        $last = Appointment::query()
            ->where('appointment_no', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('appointment_no');

        $lastSeq = 0;
        if (is_string($last) && str_starts_with($last, $prefix)) {
            $lastSeq = (int) substr($last, strlen($prefix));
        }

        return $prefix.str_pad((string) ($lastSeq + 1), 4, '0', STR_PAD_LEFT);
    }
}
