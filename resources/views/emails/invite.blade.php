{{--
    FILE:    resources/views/emails/invite.blade.php
    VERSION: 1.6.0
    DATE:    2026-06-18

    DESCRIPTION:
      E-Mail-Template für Einladungen und Passwort-Zurücksetzungen.
      Unterstützt type='register' (syst/mand/cust) und type='pw_reset' (syst/mand/cust).

    DATA FROM MAILABLE (InviteMail):
      $inviteUrl  — string, Einladungs- oder Reset-Link
      $type       — string, 'register' | 'pw_reset'
      $userType   — string, 'syst' | 'mand' | 'cust'

    CHANGES: 1.6.0 (2026-06-18) pw_reset-Zweig: Button "Passwort ändern" ergänzt
             (vorher nur reine URL als Text); URL-Text-Zeile um Praefix
             "Oder verwende die URL:" ergänzt, analog zu cust-invite.blade.php.
--}}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fotogalerie</title>
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
            @if($type === 'register')
                @if($userType === 'mand')
                    <p>Du wurdest eingeladen, einen Galerist:innen-Account für die Fotogalerie
                       anzulegen. Klicke auf den folgenden Button — er ist 24 Stunden
                       gültig:</p>
                @elseif($userType === 'cust')
                    <p>Sie wurden eingeladen, einen Mitglieder-Account für Fotogalerie
                       anzulegen. Klicken Sie auf den folgenden Link — er ist 24 Stunden
                       gültig:</p>
                @else
                    <p>Sie wurden eingeladen, einen System-Account für Fotogalerie
                       anzulegen. Klicken Sie auf den folgenden Link — er ist 24 Stunden
                       gültig:</p>
                @endif

                <a href="{{ $inviteUrl }}" class="btn">Link zum Account erstellen</a>

                @if($userType === 'mand')
                <p style="word-break:break-all; font-size:12px; color:#666666;">
                    Oder verwende die URL: {{ $inviteUrl }}
                </p>
                <p style="font-size:13px; color:#555555; border-left:3px solid #1a1a2e;
                          padding:8px 12px; margin:0 0 20px 0; border-radius:0 4px 4px 0;">
                    <strong>Hinweis:</strong> Bei der Registrierung sind die Zustimmung
                    zur Datenschutzerklärung sowie zu den Bedingungen für den Upload
                    von Daten erforderlich. Beide Dokumente werden dir während der
                    Registrierung angezeigt.
                </p>
                @elseif($userType === 'cust')
                <p style="font-size:13px; color:#555555; border-left:3px solid #1a1a2e;
                          padding:8px 12px; margin:0 0 20px 0; border-radius:0 4px 4px 0;">
                    <strong>Hinweis:</strong> Bei der Registrierung ist die Zustimmung
                    zur Datenschutzerklärung erforderlich. Diese wird Ihnen während der
                    Registrierung angezeigt.
                </p>
                @endif

                @if($userType === 'mand')
                <p class="note">Falls du diese Einladung nicht erwartet hast,
                   kannst du diese E-Mail ignorieren.</p>
                @else
                <p class="note">Falls Sie diese Einladung nicht erwartet haben,
                   können Sie diese E-Mail ignorieren.</p>
                @endif
            @else
                <p>Sie haben Ihr Passwort vergessen? Kein Problem: mit
                diesem Link können Sie ein neues Passwort festlegen:</p>

                <a href="{{ $inviteUrl }}" class="btn">Passwort ändern</a>

                <p style="word-break:break-all; font-size:12px; color:#666666;">
                    Oder verwende die URL: {{ $inviteUrl }}
                </p>

                <p>Falls dies nicht von Ihnen veranlasst war, können Sie
                diese Email ignorieren. Ihr Konto bleibt dann unverändert.</p>
            @endif
        </div>

        <div class="footer">
            Diese E-Mail wurde automatisch von Fotogalerie versandt. Antworten an diese E-Mail-Adresse werden nicht gelesen und nicht beantwortet.
        </div>
    </div>
</body>
</html>
