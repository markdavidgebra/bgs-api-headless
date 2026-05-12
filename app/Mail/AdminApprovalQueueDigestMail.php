<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminApprovalQueueDigestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public int $pendingPatients,
        public int $pendingDoctors,
        public int $draftStaff,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Daily digest: items awaiting approval'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-approval-queue-digest',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
