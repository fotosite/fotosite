<?php
/**
 * FILE:        app/Mail/InviteMail.php
 * VERSION:     1.2.0
 *
 * FUNCTIONS:   __construct()   — Accepts invite URL, type ('register'|'pw_reset'),
 *                                and userType ('syst'|'mand'|'cust', default 'syst')
 *              envelope()      — Sets subject based on type
 *              content()       — Returns emails.invite view with $inviteUrl, $type, $userType
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

class InviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $inviteUrl,
        public readonly string $type,
        public readonly string $userType = 'syst',
    ) {}

    public function envelope(): Envelope
    {
        $subject = match(true) {
            $this->type === 'pw_reset'                              => 'Fotosite V8 — Passwort zurücksetzen',
            $this->type === 'register' && $this->userType === 'mand' => 'Einladung: Fotosite Mandant',
            $this->type === 'register' && $this->userType === 'cust' => 'Einladung: Fotosite User',
            default                                                  => 'Einladung: Fotosite V8 System-Account erstellen',
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invite',
            with: [
                'inviteUrl' => $this->inviteUrl,
                'type'      => $this->type,
                'userType'  => $this->userType,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
