<?php

namespace App\Notifications\Patient;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentReminderPatientNotification extends Notification
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
        $this->appointment->loadMissing(['service:id,name', 'clinicalStaff:id,name']);

        return (new MailMessage)
            ->subject(__('Reminder: appointment tomorrow'))
            ->view('emails.patient-appointment-reminder-plain', ['appointment' => $this->appointment]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $this->appointment->loadMissing(['service:id,name', 'clinicalStaff:id,name']);

        return [
            'type' => 'appointment_reminder',
            'title' => __('Appointment reminder'),
            'message' => __('Reminder: :no with :doctor on :date at :time.', [
                'no' => (string) ($this->appointment->appointment_no ?? '#'.$this->appointment->id),
                'doctor' => (string) ($this->appointment->clinicalStaff?->name ?? __('your doctor')),
                'date' => $this->appointment->appointment_date?->format('M j, Y') ?? '',
                'time' => (string) $this->appointment->time_display,
            ]),
            'action_url' => route('patient.appointments.show', $this->appointment),
            'appointment_id' => $this->appointment->id,
        ];
    }
}
