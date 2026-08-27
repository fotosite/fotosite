{{--
    FILE:    resources/views/emails/trusted-device-added.blade.php
    VERSION: 1.0.0
    DATE:    2026-07-10

    DESCRIPTION:
      Benachrichtigung nach Aktivierung von "Dieses Gerät als sicher merken"
      (cust/mand-Login). Struktur 1:1 nach emails/two-factor-code.blade.php
      (gleicher Wrapper/Header/Body/Footer, gleiche Akzentfarbe #1a1a2e).

    DATA FROM MAILABLE:
      $deviceLabel   — string, kosmetische Geräte-Bezeichnung (guessDeviceLabel())
      $recipientName — string, Vorname des Empfängers (kann leer sein)
      $timestamp     — string, formatiertes Datum/Uhrzeit
--}}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Neues vertrauenswürdiges Gerät</title>
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
        .device-box {
            background-color: #f0f4ff;
            border: 2px solid #3b5bdb;
            border-radius: 6px;
            text-align: center;
            padding: 24px 16px;
            margin: 24px 0;
        }
        .device-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #555555;
            margin-bottom: 10px;
        }
        .device-value {
            font-size: 20px;
            font-weight: bold;
            color: #1a1a2e;
        }
        .device-timestamp {
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
        .warning {
            font-size: 14px;
            line-height: 1.6;
            color: #92400e;
            background-color: #fff7ed;
            border: 1px solid #fdba74;
            border-radius: 6px;
            padding: 16px;
            margin-top: 24px;
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
            <p class="greeting">
                @if($recipientName)
                    Hallo {{ $recipientName }},
                @else
                    Hallo,
                @endif
            </p>

            <div>{!! renderMarkdownVariant(storage_path('app/private/ui-texte/all/a_mail_trusted_device_hinweis.md'), 'INTRO') !!}</div>

            <div class="device-box">
                <div class="device-label">Gerät</div>
                <div class="device-value">{{ $deviceLabel }}</div>
                <div class="device-timestamp">{{ $timestamp }}</div>
            </div>

            <div class="warning">
                {!! renderMarkdownVariant(storage_path('app/private/ui-texte/all/a_mail_trusted_device_hinweis.md'), 'WARNUNG') !!}
            </div>
        </div>

        <div class="footer">
            Diese E-Mail wurde automatisch von Fotogalerie versandt. Bitte antworten Sie nicht auf diese E-Mail.
        </div>
    </div>
</body>
</html>
