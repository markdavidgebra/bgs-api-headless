<?php

namespace App\Notifications\Patient;

use App\Models\AppointmentPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PatientPaymentConfirmedNotification extends Notification
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
        $this->appointmentPayment->loadMissing('appointment:id,appointment_no');

        $mail = (new MailMessage)
            ->subject(__('Payment received'))
            ->line(__('We received your payment for appointment billing.'))
            ->line(__('**Amount:** ₱:amount', ['amount' => number_format((float) $this->appointmentPayment->amount, 2)]));
        if (filled($this->appointmentPayment->invoice_no)) {
            $mail->line(__('**Invoice:** :inv', ['inv' => (string) $this->appointmentPayment->invoice_no]));
        }

        return $mail->action(__('View receipt'), route('patient.payments.show', $this->appointmentPayment->id));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'payment_confirmed',
            'title' => __('Payment confirmed'),
            'message' => __('Payment of ₱:amount was recorded.', [
                'amount' => number_format((float) $this->appointmentPayment->amount, 2),
            ]),
            'action_url' => route('patient.payments.show', $this->appointmentPayment->id),
            'appointment_payment_id' => $this->appointmentPayment->id,
        ];
    }
}
