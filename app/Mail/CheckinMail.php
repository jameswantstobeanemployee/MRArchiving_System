<?php

namespace App\Mail;

use App\Models\ArchivedChart;
use App\Models\CheckoutHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CheckinMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ArchivedChart $chart,
        public CheckoutHistory $checkout
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Chart Returned — {$this->chart->patient->full_name} (Case: {$this->chart->case_number})"
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.checkin');
    }
}
