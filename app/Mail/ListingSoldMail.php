<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\KijijiListing;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ListingSoldMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public KijijiListing $listing
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Listing Sold: ' . $this->listing->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.listing-sold',
        );
    }
}
