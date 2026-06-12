<?php
/**
 * FILE:        app/Mail/CustInviteMail.php
 * VERSION:     1.4.0
 * AUTHOR:      Martin Wagner
 * DATE:        2026-06-08
 * PURPOSE:     Einladungs-E-Mail an neues Mitglied — enthält Registrierungslink (48 h gültig)
 *
 * FUNCTIONS:   __construct()   — Nimmt $invite (CustInvite), $registerUrl und $mandUname entgegen
 *              envelope()      — Betreff: "Einladung zur Fotogalerie"
 *              content()       — Gibt emails.cust-invite View zurück (mit registerUrl, mandUname, custName, mandFirstname)
 *                                Lädt MandUser via invite->mand_id; übergibt genitivName(mand_firstname)
 *              attachments()   — Gibt leeres Array zurück
 *
 * CALLS:       App\Models\UserDb\MandUser::find()
 *              genitivName()   — app/helpers.php
 *
 * DB ACCESS:   userdb.mand_user.mand_id, mand_firstname
 */

namespace App\Mail;

use App\Models\UserDb\MandUser;
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
        $mand = MandUser::find($this->invite->mand_id);

        return new Content(
            view: 'emails.cust-invite',
            with: [
                'registerUrl'   => $this->registerUrl,
                'mandUname'     => $this->mandUname,
                'custName'      => $this->invite->cust_alias ?? 'dort',
                'mandFirstname' => genitivName($mand?->mand_firstname ?? ''),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
