<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PatientRegistrationApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $name,
        public string $emailAddress,
        public string $plainPassword
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your BioGlow Solutions Account Has Been Approved',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.patient-registration-approved'
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
