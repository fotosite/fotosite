<?php
/**
 * FILE:        app/Mail/TrustedDeviceAddedMail.php
 * VERSION:     1.0.0
 * DATE:        2026-07-10
 *
 * PURPOSE:     Benachrichtigung, wenn ein neues Gerät für den Trusted-Device-
 *              Login (cust/mand) als vertrauenswürdig markiert wurde — künftige
 *              Logins auf diesem Gerät überspringen die 2FA-Abfrage.
 *
 * FUNCTIONS:   __construct()    — Nimmt Geräte-Bezeichnung und optionalen Empfängernamen entgegen
 *              envelope()       — Setzt Betreff
 *              content()        — Gibt Blade-View mit deviceLabel, recipientName, timestamp zurück
 *              attachments()    — Gibt leeres Array zurück (keine Anhänge)
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

class TrustedDeviceAddedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $deviceLabel,
        public readonly string $recipientName = '',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Neues vertrauenswürdiges Gerät für deinen Account');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.trusted-device-added',
            with: [
                'deviceLabel'   => $this->deviceLabel,
                'recipientName' => $this->recipientName,
                'timestamp'     => now()->format('d.m.Y, H:i \U\h\r'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
