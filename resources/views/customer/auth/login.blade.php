{{--
    FILE:    resources/views/customer/auth/login.blade.php
    VERSION: 2.0.0
    DATE:    2026-06-08

    HINWEIS:
    Diese View wird nicht mehr verwendet. Der Customer-Login
    läuft über das Modal auf welcome.blade.php
    (Route customer.login redirected dorthin).
    Diese Datei bleibt als Platzhalter erhalten, falls die
    Route versehentlich noch direkt darauf zeigt.
--}}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex, nofollow">
    <title>Fotogalerie</title>
</head>
<body style="font-family: sans-serif; text-align: center; padding: 4rem;" x-data="{}">
    <h1>Seite nicht verfügbar</h1>
    <p>Diese Seite wird nicht mehr verwendet.</p>
    <p><button type="button" @click="window.location='{{ route('home') }}'" class="select-none">Zurück zur Startseite</button></p>
</body>
</html>
