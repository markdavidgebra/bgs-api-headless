<?php

namespace App\Mail;

use App\Models\Admin;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewStaffDraftAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Admin $staff) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('New staff account awaiting approval'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-new-staff-draft',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
