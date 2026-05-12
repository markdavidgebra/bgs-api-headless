<?php

namespace App\Notifications\Patient;

use App\Models\AppointmentPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PatientInvoiceAvailableNotification extends Notification
{
    use Queueable;

    public function __construct(public AppointmentPayment $appointmentPayment) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->appointmentPayment->loadMissing('appointment:id,appointment_no,patient_id');

        $inv = (string) ($this->appointmentPayment->invoice_no ?? '');

        return (new MailMessage)
            ->subject(__('New invoice: :inv', ['inv' => $inv !== '' ? $inv : __('Appointment billing')]))
            ->line(__('An invoice or billing record is available for your appointment.'))
            ->line(__('**Invoice:** :inv', ['inv' => $inv !== '' ? $inv : '—']))
            ->line(__('**Amount:** ₱:amount', ['amount' => number_format((float) $this->appointmentPayment->amount, 2)]))
            ->action(__('View in patient portal'), route('patient.payments.show', $this->appointmentPayment->id));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $this->appointmentPayment->loadMissing('appointment:id,appointment_no');

        $inv = (string) ($this->appointmentPayment->invoice_no ?? '');

        return [
            'type' => 'invoice_available',
            'title' => __('Invoice / billing'),
            'message' => __('Invoice :inv (₱:amount) is ready. View details under Payments.', [
                'inv' => $inv !== '' ? $inv : '#'.$this->appointmentPayment->id,
                'amount' => number_format((float) $this->appointmentPayment->amount, 2),
            ]),
            'action_url' => route('patient.payments.show', $this->appointmentPayment->id),
            'appointment_payment_id' => $this->appointmentPayment->id,
        ];
    }
}
