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

    public function __construct(
        public Collection $items,
        public bool       $isManual = false
    ) {}

    public function envelope(): Envelope
    {
        $count = $this->items->count();

        return new Envelope(
            subject: "⚠ Low Stock Alert — {$count} item" . ($count > 1 ? 's' : '') . " need restocking",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.low-stock-alert',
        );
    }
}
