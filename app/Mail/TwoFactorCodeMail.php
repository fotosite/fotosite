<?php
/**
 * FILE:        app/Mail/TwoFactorCodeMail.php
 * VERSION:     1.0.0
 *
 * FUNCTIONS:   __construct()    — Accepts the 6-digit 2FA code and optional recipient name
 *              envelope()       — Sets subject and sender name
 *              content()        — Returns Blade view with code and validity
 *              attachments()    — Returns empty array (no attachments)
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

class TwoFactorCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $code,
        public readonly string $recipientName = '',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Ihr Sicherheitscode — Fotosite',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.two-factor-code',
            with: [
                'code'          => $this->code,
                'recipientName' => $this->recipientName,
                'validMinutes'  => 10,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
