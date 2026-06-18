<?php
/**
 * FILE:        app/Mail/EmailChangeMail.php
 * VERSION:     1.0.0
 *
 * FUNCTIONS:   __construct()   — Accepts confirmation URL, new email address,
 *                                and firstname (for greeting, optional)
 *              envelope()      — Sets subject
 *              content()       — Returns emails.email_change view with
 *                                $confirmUrl, $newEmail, $firstname
 *              attachments()   — Returns empty array
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

class EmailChangeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $confirmUrl,
        public readonly string $newEmail,
        public readonly ?string $firstname = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Fotogalerie — E-Mail-Adresse bestätigen');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.email_change',
            with: [
                'confirmUrl' => $this->confirmUrl,
                'newEmail'   => $this->newEmail,
                'firstname'  => $this->firstname,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
