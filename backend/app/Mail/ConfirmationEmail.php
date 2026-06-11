<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConfirmationEmail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public string $lienVerification;

    public function __construct(string $lienVerification)
    {
        $this->lienVerification = $lienVerification;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmez votre adresse email - Sygma',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.confirmation',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
