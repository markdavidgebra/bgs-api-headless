<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class AdminLowStockAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  Collection<int, \App\Models\Product>  $products
     */
    public function __construct(
        public Collection $products,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Inventory alert: low or out-of-stock products'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-low-stock-alert',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
