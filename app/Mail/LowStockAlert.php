<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class LowStockAlert extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Collection $products) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⚠️ Low Stock Alert — ' . $this->products->count() . ' product(s) need attention',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.low-stock-alert',
        );
    }
}
