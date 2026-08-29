<?php

namespace App\Notifications\Patient;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentBookedPatientNotification extends Notification
{
    use Queueable;

    public function __construct(public Appointment $appointment) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->appointment->loadMissing(['service:id,name', 'clinicalStaff:id,name', 'assignedAdmin:id,name', 'patient:id,name']);

        return (new MailMessage)
            ->subject(__('Appointment booking confirmation'))
            ->view('emails.patient-appointment-booked', [
                'appointment' => $this->appointment,
                'actionUrl' => $this->patientPortalLoginUrl(),
            ]);
    }

    /**
     * Link the email's "View appointment" button to the patient portal sign-in page
     * (the portal is a separate SPA at PATIENT_PORTAL_URL, not a route on this app).
     */
    protected function patientPortalLoginUrl(): string
    {
        $base = rtrim((string) config('app.patient_portal_url'), '/');
        if ($base === '') {
            $base = rtrim((string) config('app.url'), '/');
        }

        $base = (string) preg_replace('#/login$#i', '', $base);

        return rtrim($base, '/').'/login';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $this->appointment->loadMissing(['service:id,name', 'clinicalStaff:id,name', 'assignedAdmin:id,name']);

        return [
            'type' => 'appointment_booked',
            'title' => __('Appointment confirmed'),
            'message' => __('Your appointment :no with :doctor on :date at :time is booked.', [
                'no' => (string) ($this->appointment->appointment_no ?? '#'.$this->appointment->id),
                'doctor' => (string) ($this->appointment->clinical_staff_name !== '—'
                    ? $this->appointment->clinical_staff_name
                    : __('your doctor')),
                'date' => $this->appointment->appointment_date?->format('M j, Y') ?? '',
                'time' => (string) $this->appointment->time_display,
            ]),
            'action_url' => route('patient.appointments.show', $this->appointment),
            'appointment_id' => $this->appointment->id,
        ];
    }
}
