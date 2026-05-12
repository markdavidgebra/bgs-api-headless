<?php

namespace App\Mail;

use App\Models\Admin;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StaffAccountCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Admin $staff,
        public string $temporaryPassword
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Staff Account Has Been Created'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.staff-account-created'
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
