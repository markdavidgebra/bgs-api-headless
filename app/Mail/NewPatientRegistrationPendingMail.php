<?php

namespace App\Mail;

use App\Models\Patient;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewPatientRegistrationPendingMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Patient $patient) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('New patient registration pending approval'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-new-patient-registration',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
