{{--
    FILE:    resources/views/emails/mand-account-deactivated.blade.php
    VERSION: 1.0.0
    DATE:    2026-09-04

    DESCRIPTION:
      Benachrichtigung an Galerist:in, deren Konto von der Systemadministration
      vorübergehend deaktiviert wurde (Karenzzeit vor endgültiger Löschung).

    DATA FROM MAILABLE (MandDeactivatedMail):
      $mandName     — string, Vorname des Mandanten
      $contactEmail — string|null, Kontakt-E-Mail aus config('mand_deactivation.contact_email')
      $graceDays    — int, Karenzzeit in Tagen aus config('mand_deactivation.grace_days')
--}}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dein Galerist:innen-Konto wurde deaktiviert</title>
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
            <p>Hallo {{ $mandName }},</p>

            {!! uiText('mand', 'm_mail_account_deactivated', ['contact_email' => $contactEmail, 'grace_days' => $graceDays]) !!}
        </div>

        <div class="footer">
            Antworten an diese E-Mail-Adresse werden nicht gelesen und nicht beantwortet.
        </div>

    </div>
</body>
</html>
