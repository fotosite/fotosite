<?php
/**
 * FILE:        app/Mail/MandActivatedMail.php
 * VERSION:     1.0.0
 * DATE:        2026-09-04
 *
 * FUNCTIONS:   __construct()   — Nimmt $mandName entgegen (Vorname des Mandanten)
 *              envelope()      — Betreff: "Dein Konto wurde freigeschaltet"
 *              content()       — Gibt emails.mand-account-activated View zurück (mit mandName)
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

class MandActivatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $mandName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Dein Konto wurde freigeschaltet');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.mand-account-activated',
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
