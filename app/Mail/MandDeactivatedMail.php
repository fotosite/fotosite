<?php
/**
 * FILE:        app/Mail/MandDeactivatedMail.php
 * VERSION:     1.0.0
 * DATE:        2026-09-04
 *
 * FUNCTIONS:   __construct()   — Nimmt $mandName entgegen (Vorname des Mandanten)
 *              envelope()      — Betreff: "Dein Galerist:innen-Konto wurde deaktiviert"
 *              content()       — Gibt emails.mand-account-deactivated View zurück (mit mandName,
 *                                 contactEmail, graceDays)
 *              attachments()   — Gibt leeres Array zurück
 *
 * CALLS:       config('mand_deactivation.contact_email')
 *              config('mand_deactivation.grace_days')
 *
 * DB ACCESS:   —
 */

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MandDeactivatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $mandName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Dein Galerist:innen-Konto wurde deaktiviert');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.mand-account-deactivated',
            with: [
                'mandName'     => $this->mandName,
                'contactEmail' => config('mand_deactivation.contact_email'),
                'graceDays'    => config('mand_deactivation.grace_days'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
