<?php

namespace App\Observers;

use App\Models\AppointmentPayment;
use App\Models\Patient;
use App\Notifications\Patient\PatientInvoiceAvailableNotification;
use App\Notifications\Patient\PatientPaymentConfirmedNotification;
use Illuminate\Support\Facades\Notification;

class AppointmentPaymentObserver
{
    public function created(AppointmentPayment $appointmentPayment): void
    {
        if (filled($appointmentPayment->invoice_no)) {
            $this->sendInvoiceNotification($appointmentPayment);
        }
        if ($appointmentPayment->is_paid) {
            $this->sendPaymentConfirmedNotification($appointmentPayment);
        }
    }

    public function updated(AppointmentPayment $appointmentPayment): void
    {
        if ($appointmentPayment->wasChanged('invoice_no') && filled($appointmentPayment->invoice_no)) {
            $this->sendInvoiceNotification($appointmentPayment);
        }

        if ($appointmentPayment->wasChanged('is_paid') && $appointmentPayment->is_paid) {
            $this->sendPaymentConfirmedNotification($appointmentPayment);
        }
    }

    private function sendInvoiceNotification(AppointmentPayment $appointmentPayment): void
    {
        $patient = $this->resolvePatient($appointmentPayment);
        if ($patient && filled($patient->email)) {
            Notification::send($patient, new PatientInvoiceAvailableNotification($appointmentPayment));
        }
    }

    private function sendPaymentConfirmedNotification(AppointmentPayment $appointmentPayment): void
    {
        $patient = $this->resolvePatient($appointmentPayment);
        if ($patient && filled($patient->email)) {
            Notification::send($patient, new PatientPaymentConfirmedNotification($appointmentPayment));
        }
    }

    private function resolvePatient(AppointmentPayment $appointmentPayment): ?Patient
    {
        $appointmentPayment->loadMissing('appointment:id,patient_id');
        $patientId = $appointmentPayment->appointment?->patient_id;
        if (! $patientId) {
            return null;
        }

        return Patient::query()->find($patientId);
    }
}
