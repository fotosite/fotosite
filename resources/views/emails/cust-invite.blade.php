<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Einladung zu Fotosite</title>
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
        .validity {
            font-size: 13px;
            color: #555555;
            background-color: #f8f8f8;
            border-left: 3px solid #1a1a2e;
            padding: 10px 14px;
            margin: 0 0 20px 0;
            border-radius: 0 4px 4px 0;
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
            <h1>Fotosite V8</h1>
        </div>

        <div class="body">
            <p>Sie wurden zu <strong>{{ genitivName($mandUname) }}</strong> Fotogalerie eingeladen.<br>
               Klicken Sie auf den folgenden Link, um Ihren Account zu erstellen:</p>

            <a href="{{ $registerUrl }}" class="btn">Jetzt registrieren</a>

            <div class="validity">
                Dieser Einladungslink ist <strong>48 Stunden</strong> gültig.
            </div>

            <p>Falls der Button nicht funktioniert, kopieren Sie diesen Link in Ihren Browser:</p>
            <p style="word-break:break-all; font-size:12px; color:#666666;">
                {{ $registerUrl }}
            </p>

            <p class="note">Falls Sie diese Einladung nicht erwartet haben,
               können Sie diese E-Mail ignorieren. Ihr Konto bleibt unverändert.</p>
        </div>

        <div class="footer">
            Diese E-Mail wurde automatisch von Fotosite versandt. Bitte antworten Sie nicht auf diese E-Mail.
        </div>

    </div>
</body>
</html>
