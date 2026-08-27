{{--
    FILE:    resources/views/emails/two-factor-code.blade.php
    VERSION: 1.2.0
    DATE:    2026-06-08

    DESCRIPTION:
      2FA-Sicherheitscode-E-Mail — wird bei jeder Mandant/System-Anmeldung gesendet.

    DATA FROM MAILABLE:
      $code         — string, 6-stelliger Sicherheitscode
      $validMinutes — int, Gültigkeitsdauer des Codes in Minuten
--}}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ihr Sicherheitscode</title>
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
        .greeting {
            font-size: 15px;
            margin-bottom: 20px;
        }
        .code-box {
            background-color: #f0f4ff;
            border: 2px solid #3b5bdb;
            border-radius: 6px;
            text-align: center;
            padding: 24px 16px;
            margin: 24px 0;
        }
        .code-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #555555;
            margin-bottom: 10px;
        }
        .code-value {
            font-size: 42px;
            font-weight: bold;
            letter-spacing: 12px;
            color: #1a1a2e;
            font-family: 'Courier New', Courier, monospace;
        }
        .validity {
            font-size: 13px;
            color: #777777;
            margin-top: 12px;
        }
        .info {
            font-size: 14px;
            line-height: 1.6;
            color: #555555;
            margin-top: 20px;
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
            <div>{!! renderMarkdownVariant(storage_path('app/private/ui-texte/all/a_mail_2fa_hinweis.md'), 'INTRO') !!}</div>

            <p style="font-size: 32px; font-weight: bold;
                      letter-spacing: 8px; text-align: center;">
                {{ $code }}
            </p>

            <div>{!! renderMarkdownVariant(storage_path('app/private/ui-texte/all/a_mail_2fa_hinweis.md'), 'GUELTIGKEIT', ['validMinutes' => $validMinutes]) !!}</div>

            <div>{!! renderMarkdownVariant(storage_path('app/private/ui-texte/all/a_mail_2fa_hinweis.md'), 'ABSCHLUSS') !!}</div>
        </div>

        <div class="footer">
            Diese E-Mail wurde automatisch von Fotogalerie versandt. Bitte antworten Sie nicht auf diese E-Mail.
        </div>
    </div>
</body>
</html>
