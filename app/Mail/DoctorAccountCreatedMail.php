<?php

namespace App\Mail;

use App\Models\Doctor;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DoctorAccountCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Doctor $doctor,
        public string $plainPassword
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Doctor Account Credentials'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.doctor-account-created'
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
