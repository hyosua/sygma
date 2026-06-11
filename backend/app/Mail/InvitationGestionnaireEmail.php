<?php

namespace App\Mail;

use App\Models\InvitationGestionnaire;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvitationGestionnaireEmail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public string $lienInscription;

    public function __construct(InvitationGestionnaire $invitation)
    {
        $this->lienInscription = config('app.frontend_url') . '/inscription/gestionnaire/' . $invitation->token;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invitation gestionnaire - Sygma',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invitation_gestionnaire',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
