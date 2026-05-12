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
        return (new MailMessage)
            ->subject(__('Appointment booking confirmation'))
            ->view('emails.patient-appointment-booked', [
                'appointment' => $this->appointment,
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $this->appointment->loadMissing(['service:id,name', 'doctor:id,name']);

        return [
            'type' => 'appointment_booked',
            'title' => __('Appointment confirmed'),
            'message' => __('Your appointment :no with :doctor on :date at :time is booked.', [
                'no' => (string) ($this->appointment->appointment_no ?? '#'.$this->appointment->id),
                'doctor' => (string) ($this->appointment->doctor?->name ?? __('your doctor')),
                'date' => $this->appointment->appointment_date?->format('M j, Y') ?? '',
                'time' => (string) $this->appointment->time_display,
            ]),
            'action_url' => route('patient.appointments.show', $this->appointment),
            'appointment_id' => $this->appointment->id,
        ];
    }
}
