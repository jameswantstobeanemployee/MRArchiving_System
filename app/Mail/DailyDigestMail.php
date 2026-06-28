<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailyDigestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public array $stats
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Daily Digest — Medical Records Archive — {$this->stats['date']}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.daily_digest');
    }
}
