<?php

namespace App\Mail;

use App\Models\Doctor;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewDoctorPendingAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Doctor $doctor) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('New doctor account pending approval'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-new-doctor-pending',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
