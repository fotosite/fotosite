{{--
    FILE:    resources/views/policy/cust_update.blade.php
    VERSION: 1.0.0
    DATE:    2026-06-18

    DESCRIPTION:
      Blockierendes Popup für Mitglieder (cust) — wird angezeigt, wenn die
      aktuelle Datenschutz-Version noch nicht akzeptiert wurde, oder als reiner
      Hinweis bei aktualisierten Upload-Bedingungen (kein DB-Eintrag für cust).
      Standalone (kein Dashboard-Nav), Stil wie Login-Seite.

    DATA FROM CONTROLLER:
      $type — string, 'ds' | 'upload'

    ROUTES USED:
      POST customer.policy.confirm — Bestätigung absenden
--}}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Aktualisierte Bedingungen · Fotogalerie</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-50 text-gray-900 antialiased">

    <div class="min-h-screen flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-sm bg-white rounded-xl border border-gray-200
                    shadow-sm px-8 py-8">

            <div class="mb-8">
                <span class="text-[11px] font-mono tracking-widest uppercase text-gray-400">
                    Fotogalerie · Mitglied
                </span>
            </div>

            @if($type === 'ds')
                <h1 class="text-xl font-semibold tracking-tight text-gray-800 mb-2">
                    Datenschutzerklärung aktualisiert
                </h1>
                <p class="text-sm text-gray-500 mb-6 leading-relaxed">
                    Wir haben unsere Datenschutzerklärung aktualisiert. Informiere dich
                    in deinem Einstellungsbereich.
                </p>
                <button type="button"
                        x-data="{}"
                        @click="window.open('{{ route('customer.datenschutz.erlaeuterung') }}', '_blank')"
                        class="block text-sm text-indigo-600 hover:underline mb-6 select-none">
                    Datenschutzerklärung ansehen →
                </button>
                <p class="text-xs text-gray-400 leading-relaxed mb-6">
                    Die Datenschutz-Erklärung und die Bedingungen für Galerist:innen zum
                    Upload von Fotos findest du auch in deinem Einstellungen-Fenster.
                </p>
            {{-- Unerreichbar seit 2026-07: Upload-Popup für cust deaktiviert
                 (siehe CheckPolicyVersion.php). Zweig bleibt als Sicherheitsnetz
                 bestehen, falls _policy_update dennoch auf 'upload' gesetzt wird. --}}
            @else
                <h1 class="text-xl font-semibold tracking-tight text-gray-800 mb-2">
                    Upload-Bedingungen aktualisiert
                </h1>
                <p class="text-sm text-gray-500 mb-6 leading-relaxed">
                    Wir haben unsere Upload-Bedingungen aktualisiert. Informiere dich
                    in deinem Einstellungsbereich.
                </p>
                <button type="button"
                        x-data="{}"
                        @click="window.open('{{ route('customer.datenschutz.upload-bedingungen-pdf') }}', '_blank')"
                        class="block text-sm text-indigo-600 hover:underline mb-6 select-none">
                    Upload-Bedingungen ansehen →
                </button>
            @endif

            <form method="POST" action="{{ route('customer.policy.confirm') }}">
                @csrf
                <button type="submit"
                        class="w-full flex justify-center rounded-lg bg-indigo-600
                               px-4 py-3 md:py-2 text-sm font-semibold text-white
                               hover:bg-indigo-700 transition-colors focus:outline-none
                               focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    OK
                </button>
            </form>

        </div>
    </div>

</body>
</html>
