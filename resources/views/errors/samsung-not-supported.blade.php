{{--
    FILE:    resources/views/errors/samsung-not-supported.blade.php
    VERSION: 1.0.0

    DESCRIPTION:
      Statische Hinweisseite für Besucher mit Samsung Internet
      (BlockSamsungBrowser-Middleware). Kein <x-*>-Layout, kein Header/
      Footer — muss für JEDEN Aufruf inkl. nicht eingeloggter Besucher
      greifen, bevor irgendeine andere Fotosite-Seite gerendert wird.
--}}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Browser nicht unterstützt</title>
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center px-4">

    <div class="w-full max-w-md bg-white rounded-xl border border-gray-200 p-6 text-center">
        <div class="prose prose-sm mx-auto text-gray-700">
            {!! uiText('all', 'a_samsung_browser_blockiert') !!}
        </div>
    </div>

</body>
</html>
