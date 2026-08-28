<?php

namespace App\Mail;

use App\Models\ClinicalStaff;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClinicalStaffAccountCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ClinicalStaff $doctor,
        public string $plainPassword
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Clinical Staff Account Credentials'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.clinical-staff-account-created'
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
