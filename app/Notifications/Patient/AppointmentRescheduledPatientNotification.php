<?php

namespace App\Notifications\Patient;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentRescheduledPatientNotification extends Notification
{
    use Queueable;

    public function __construct(public Appointment $appointment) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->appointment->loadMissing(['service:id,name', 'clinicalStaff:id,name']);

        return (new MailMessage)
            ->subject(__('Your appointment was rescheduled'))
            ->line(__('Your appointment :no has a new date and time.', [
                'no' => (string) ($this->appointment->appointment_no ?? '#'.$this->appointment->id),
            ]))
            ->line(__('**Date:** :date', ['date' => $this->appointment->appointment_date?->format('M j, Y') ?? '—']))
            ->line(__('**Time:** :time', ['time' => (string) $this->appointment->time_display]))
            ->action(__('View appointment'), route('patient.appointments.show', $this->appointment));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $this->appointment->loadMissing(['service:id,name', 'clinicalStaff:id,name']);

        return [
            'type' => 'appointment_rescheduled',
            'title' => __('Appointment rescheduled'),
            'message' => __(':no is now on :date at :time.', [
                'no' => (string) ($this->appointment->appointment_no ?? '#'.$this->appointment->id),
                'date' => $this->appointment->appointment_date?->format('M j, Y') ?? '',
                'time' => (string) $this->appointment->time_display,
            ]),
            'action_url' => route('patient.appointments.show', $this->appointment),
            'appointment_id' => $this->appointment->id,
        ];
    }
}
