{{--
    FILE:    resources/views/policy/mand_update.blade.php
    VERSION: 1.0.0
    DATE:    2026-06-18

    DESCRIPTION:
      Blockierendes Popup für Mandanten — wird angezeigt, wenn die aktuelle
      Datenschutz- oder Upload-Bedingungen-Version noch nicht akzeptiert wurde.
      Standalone (kein Dashboard-Nav), Stil wie Login-/Passwort-Reset-Seiten.

    DATA FROM CONTROLLER:
      $type — string, 'ds' | 'upload'

    ROUTES USED:
      POST mandant.policy.confirm — Bestätigung absenden
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
                    Fotogalerie · Galerist:in
                </span>
            </div>

            @if($type === 'ds')
                <h1 class="text-xl font-semibold tracking-tight text-gray-800 mb-2">
                    Datenschutzerklärung aktualisiert
                </h1>
                <div class="text-sm text-gray-500 mb-6 leading-relaxed">
                    {!! uiText('all', 'a_pol_ds_update_hinweis') !!}
                </div>
                <button type="button"
                        x-data="{}"
                        @click="window.open('{{ route('customer.datenschutz.erlaeuterung') }}', '_blank')"
                        class="block text-sm text-indigo-600 hover:underline mb-6 select-none">
                    Datenschutzerklärung ansehen →
                </button>
            @else
                <h1 class="text-xl font-semibold tracking-tight text-gray-800 mb-2">
                    Upload-Bedingungen aktualisiert
                </h1>
                <div class="text-sm text-gray-500 mb-6 leading-relaxed">
                    {!! uiText('all', 'a_pol_upload_update_hinweis') !!}
                </div>
                <button type="button"
                        x-data="{}"
                        @click="window.open('{{ route('customer.datenschutz.upload-bedingungen-pdf') }}', '_blank')"
                        class="block text-sm text-indigo-600 hover:underline mb-6 select-none">
                    Upload-Bedingungen ansehen →
                </button>
            @endif

            <form method="POST" action="{{ route('mandant.policy.confirm') }}">
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
