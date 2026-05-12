<?php

namespace App\Support;

use App\Models\Appointment;
use App\Models\DoctorNotification;

class DoctorAppointmentAlerts
{
    public static function notifyDoctorOfNewBooking(Appointment $appointment): void
    {
        $appointment->loadMissing(['patient:id,name', 'service:id,name']);

        DoctorNotification::query()->create([
            'doctor_id' => $appointment->doctor_id,
            'type' => DoctorNotification::TYPE_NEW_APPOINTMENT,
            'title' => __('New appointment booking'),
            'message' => __(':patient booked :service on :date at :time.', [
                'patient' => (string) ($appointment->patient?->name ?? __('Patient')),
                'service' => (string) ($appointment->service?->name ?? __('Service')),
                'date' => $appointment->appointment_date?->format('M j, Y') ?? '',
                'time' => (string) $appointment->time_display,
            ]),
            'appointment_id' => $appointment->id,
            'patient_id' => $appointment->patient_id,
        ]);
    }
}
