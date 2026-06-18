{{--
    FILE:    resources/views/emails/email_change.blade.php
    VERSION: 1.0.0
    DATE:    2026-06-18

    DESCRIPTION:
      Bestätigungs-E-Mail für E-Mail-Adressänderung (mand + cust). Wird an die NEUE
      Adresse gesendet; die alte Adresse bleibt bis zum Klick auf den Bestätigungslink
      aktiv. Stil angelehnt an emails/cust-invite.blade.php.

    DATA FROM MAILABLE (EmailChangeMail):
      $confirmUrl — string, Bestätigungslink (24 h gültig)
      $newEmail   — string, die angeforderte neue Adresse (= Empfänger dieser Mail)
      $firstname  — string|null, Vorname für die Anrede (Fallback: generische Anrede)
--}}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Mail-Adresse bestätigen · Fotogalerie</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            color: #333333;
        }
        .wrapper {
            max-width: 520px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .header {
            background-color: #1a1a2e;
            padding: 24px 32px;
        }
        .header h1 {
            color: #ffffff;
            font-size: 20px;
            margin: 0;
            letter-spacing: 0.5px;
        }
        .body {
            padding: 32px;
        }
        .body p {
            font-size: 14px;
            line-height: 1.7;
            color: #444444;
            margin: 0 0 20px 0;
        }
        .btn {
            display: inline-block;
            margin: 8px 0 24px 0;
            padding: 12px 24px;
            background-color: #1a1a2e;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 0.3px;
        }
        .note {
            font-size: 12px;
            color: #888888;
        }
        .footer {
            background-color: #f8f8f8;
            border-top: 1px solid #e8e8e8;
            padding: 16px 32px;
            font-size: 12px;
            color: #999999;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="wrapper">

        <div class="header">
            <h1>Fotogalerie</h1>
        </div>

        <div class="body">
            <p>Hallo{{ $firstname ? ' ' . $firstname : '' }},</p>

            <p>Du hast eine Änderung deiner E-Mail-Adresse angefordert.</p>

            <p>Bestätigungslink (24 Stunden gültig):</p>

            <a href="{{ $confirmUrl }}" class="btn">E-Mail-Adresse bestätigen</a>

            <p style="word-break:break-all; font-size:12px; color:#666666;">
                Oder verwende die URL: {{ $confirmUrl }}
            </p>

            <p class="note">Falls du diese Änderung nicht angefordert hast, ignoriere diese Mail.</p>
        </div>

        <div class="footer">
            Antworten an diese E-Mail-Adresse werden nicht gelesen und nicht beantwortet.
        </div>

    </div>
</body>
</html>
