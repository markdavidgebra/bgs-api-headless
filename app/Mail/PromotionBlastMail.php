<?php

namespace App\Mail;

use App\Models\Promotion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PromotionBlastMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Promotion $promotion,
        public string $subjectLine,
        public ?string $customMessage,
        public ?string $recipientName = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.promotion-blast'
        );
    }
}
