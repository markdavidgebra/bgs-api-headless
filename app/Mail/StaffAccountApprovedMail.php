<?php

namespace App\Mail;

use App\Models\Admin;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StaffAccountApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Admin $staff,
        public ?string $plainPassword = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Staff Account Has Been Approved'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.staff-account-approved'
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
