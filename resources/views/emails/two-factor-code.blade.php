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
            <h1>Fotosite</h1>
        </div>

        <div class="body">
            @if($recipientName)
                <p class="greeting">Hallo {{ $recipientName }},</p>
            @else
                <p class="greeting">Hallo,</p>
            @endif

            <p>Sie haben eine Anmeldung angefordert. Bitte geben Sie den folgenden Sicherheitscode ein, um den Vorgang abzuschließen:</p>

            <div class="code-box">
                <div class="code-label">Ihr Sicherheitscode</div>
                <div class="code-value">{{ $code }}</div>
                <div class="validity">Gültig für {{ $validMinutes }} Minuten</div>
            </div>

            <p class="info">
                Falls Sie diese Anmeldung nicht angefordert haben, können Sie diese E-Mail ignorieren.<br>
                Ihr Konto bleibt unverändert.
            </p>
        </div>

        <div class="footer">
            Diese E-Mail wurde automatisch von Fotosite versandt. Bitte antworten Sie nicht auf diese E-Mail.
        </div>
    </div>
</body>
</html>
