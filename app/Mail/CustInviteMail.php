<?php
/**
 * FILE:        app/Mail/CustInviteMail.php
 * VERSION:     1.1.0
 * AUTHOR:      Martin Wagner
 * DATE:        2026-05-30
 * PURPOSE:     Einladungs-E-Mail an neuen Kunden — enthält Registrierungslink (48 h gültig)
 *
 * FUNCTIONS:   __construct()   — Nimmt $registerUrl und $mandUname entgegen
 *              envelope()      — Betreff: "Einladung zu Fotosite"
 *              content()       — Gibt emails.cust-invite View zurück (mit registerUrl, mandUname)
 *              attachments()   — Gibt leeres Array zurück
 *
 * CALLS:       (none)
 *
 * DB ACCESS:   (none)
 */

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $registerUrl,
        public readonly string $mandUname,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Einladung zu Fotosite');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.cust-invite',
            with: [
                'registerUrl' => $this->registerUrl,
                'mandUname'   => $this->mandUname,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
