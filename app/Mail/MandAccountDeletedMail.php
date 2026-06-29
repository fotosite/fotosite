<?php
/**
 * FILE:        app/Mail/MandAccountDeletedMail.php
 * VERSION:     1.0.0
 * DATE:        2026-06-29
 *
 * FUNCTIONS:   __construct()   — Nimmt $mandName entgegen (Vorname des Mandanten)
 *              envelope()      — Betreff: "Ihr Galerist:innen-Konto wurde geloescht"
 *              content()       — Gibt emails.mand-account-deleted View zurück (mit mandName)
 *              attachments()   — Gibt leeres Array zurück
 *
 * CALLS:       —
 *
 * DB ACCESS:   —
 */

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MandAccountDeletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $mandName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Ihr Galerist:innen-Konto wurde geloescht');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.mand-account-deleted',
            with: [
                'mandName' => $this->mandName,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
