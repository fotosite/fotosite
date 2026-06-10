<?php
/**
 * FILE:        app/Mail/CustInviteMail.php
 * VERSION:     1.3.0
 * AUTHOR:      Martin Wagner
 * DATE:        2026-06-08
 * PURPOSE:     Einladungs-E-Mail an neues Mitglied — enthält Registrierungslink (48 h gültig)
 *
 * FUNCTIONS:   __construct()   — Nimmt $invite (CustInvite), $registerUrl und $mandUname entgegen
 *              envelope()      — Betreff: "Einladung zur Fotogalerie"
 *              content()       — Gibt emails.cust-invite View zurück (mit registerUrl, mandUname, custName)
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
        public readonly \App\Models\SessionDb\CustInvite $invite,
        public readonly string $registerUrl,
        public readonly string $mandUname,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Einladung zur Fotogalerie');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.cust-invite',
            with: [
                'registerUrl' => $this->registerUrl,
                'mandUname'   => $this->mandUname,
                'custName'    => $this->invite->cust_alias ?? 'dort',
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
