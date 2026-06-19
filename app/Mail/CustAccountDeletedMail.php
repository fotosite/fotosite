<?php
/**
 * FILE:        app/Mail/CustAccountDeletedMail.php
 * VERSION:     1.0.0
 * AUTHOR:      Martin Wagner
 * DATE:        2026-06-19
 * PURPOSE:     Benachrichtigung an Mitglied, dessen Benutzerkonto wegen Galerie-Schließung
 *              (letzte cust_pcode-Referenz entfernt) gelöscht wurde
 *
 * FUNCTIONS:   __construct()   — Nimmt $custName entgegen (Anrede-Fallback "Hallo")
 *              envelope()      — Betreff: "Dein Mitgliedskonto wurde geloescht"
 *              content()       — Gibt emails.cust-account-deleted View zurück (mit custName)
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

class CustAccountDeletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $custName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Dein Mitgliedskonto wurde geloescht');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.cust-account-deleted',
            with: [
                'custName' => $this->custName,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
