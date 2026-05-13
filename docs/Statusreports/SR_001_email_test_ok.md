# Statusreport — Fotosite V08 — Git-Tag: email_test_ok

## Stand
Email-Infrastruktur lokal und auf Server getestet.

## Infrastruktur

| | Lokal | Server |
|---|---|---|
| Betriebssystem | Windows (PowerShell) | Linux (Alfahosting) |
| PHP | 8.5.6 | 8.4.12 |
| Deployment | FTP + SSH | — |
| Git Remote | github.com/fotosite/fotosite.git | — |
| Mailer | Mailpit (127.0.0.1:1025) | host159.alfahosting-server.de:587 |
| Absender | noreply@martinwagner.de | noreply@martinwagner.de |

## Ergebnis
Email-Versand lokal via Mailpit und auf dem Server via SMTP vollständig getestet und funktionsfähig.

## Nächster Schritt
System-Login mit 2FA implementieren → Tag: system_login_2fa_ok
