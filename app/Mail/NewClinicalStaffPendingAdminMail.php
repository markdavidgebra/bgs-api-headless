<?php

namespace App\Mail;

use App\Models\ClinicalStaff;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewClinicalStaffPendingAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ClinicalStaff $doctor) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('New doctor account pending approval'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-new-clinical-staff-pending',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
