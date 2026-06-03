{{--
    FILE:    resources/views/mandant/dashboard.blade.php
    VERSION: 2.5.0

    DESCRIPTION:
      Mandanten-Dashboard — Einstiegsseite nach erfolgreichem Mand-Login + 2FA.
      Standalone (kein Layout-Erbe), gleiches Strukturmuster wie system/dashboard.
      Accent-Farbe: indigo (passend zum Login-Modal).

    DATA FROM CONTROLLER:
      (keine — Route-Closure übergibt noch keine Variablen)

    ROUTES USED:
      POST /mandant/logout  — Mandant-Logout (route('mandant.logout'))
      GET  /mandant/konto          — Konto-Verwaltung (route('mandant.konto'))
      GET  /mandant/passwortliste — Passwortliste (route('mandant.pwlist'))
--}}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Mandanten-Dashboard · Fotosite V8</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-50 text-gray-900 antialiased"
      x-data>

    @php $mandUname = \App\Models\UserDb\MandUser::find(session('_mand_id'))?->mand_uname ?? ''; @endphp

    {{-- ══════════════════════════════════════════════════════
         TOP BAR
    ══════════════════════════════════════════════════════ --}}
    <header class="sticky top-0 z-20 border-b border-gray-200 bg-white shadow-sm">
        <div class="mx-auto max-w-4xl px-6 h-14
                    flex items-center justify-between">

            {{-- Brand --}}
            <div class="flex items-center gap-3">
                <span class="text-[11px] font-mono tracking-widest
                             uppercase text-gray-400">
                    Fotosite&thinsp;V8
                </span>
                <span class="text-zinc-800 select-none">|</span>
                <span class="text-sm font-semibold tracking-widest
                             uppercase text-indigo-600">
                    Mandant
                </span>
                <span class="text-sm text-indigo-200">{{ $mandUname }}</span>
            </div>

            {{-- Logout --}}
            <div class="flex items-center">
                <form method="POST" action="{{ route('mandant.logout') }}">
                    @csrf
                    <button type="submit"
                            class="text-xs text-gray-400 hover:text-red-500
                                   transition-colors duration-150 tracking-wide">
                        Abmelden
                    </button>
                </form>
            </div>

        </div>
    </header>

    {{-- ══════════════════════════════════════════════════════
         MAIN
    ══════════════════════════════════════════════════════ --}}
    <main class="mx-auto max-w-4xl px-6 pt-14 pb-24">

        {{-- Seitenüberschrift --}}
        <div class="mb-10">
            <h1 class="text-xl font-semibold tracking-tight text-gray-800">
                Mandanten-Dashboard
            </h1>
            <p class="mt-1.5 text-sm text-zinc-600">
                Willkommen in Ihrem Verwaltungsbereich.
            </p>
        </div>

        {{-- Flash: Status-Meldung (z. B. nach Logout-Redirect) --}}
        @if(session('status'))
            <div class="mb-8 rounded-lg border border-indigo-200
                        bg-indigo-50 px-4 py-3 text-sm text-indigo-700">
                {{ session('status') }}
            </div>
        @endif

        {{-- Passwortliste: Status-Hinweise --}}
        @php
            $mandId   = session('_mand_id');
            $pwlist   = $mandId
                ? \App\Models\SessionDb\PwList::where('mand_id', $mandId)->first()
                : null;
            $pwExpired = $pwlist && $pwlist->valid_until < now();
            $pwMissing = ! $pwlist;
        @endphp

        @if($pwExpired)
            <div class="p-4 rounded-lg mb-4 text-sm
                        bg-amber-50 border border-amber-300 text-amber-800">
                ⚠️ Der Gültigkeitszeitraum für Ihre Passwortliste ist abgelaufen.
                Bitte aktualisieren Sie die
                <a href="{{ route('mandant.pwlist') }}"
                   class="font-semibold underline hover:no-underline">Passwortliste</a>.
            </div>
        @endif

        @if($pwMissing)
            <div class="p-4 rounded-lg mb-4 text-sm
                        bg-blue-50 border border-blue-300 text-blue-800">
                ℹ️ Sie haben noch keine Passwortliste angelegt.
                <a href="{{ route('mandant.pwlist') }}"
                   class="font-semibold underline hover:no-underline">Jetzt anlegen</a>.
            </div>
        @endif

        {{-- ── Navigations-Kacheln ──────────────────────────── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

            {{-- 1. Gruppen & Subgruppen (geplant) --}}
            <div class="relative flex flex-col gap-5 rounded-xl
                        border border-gray-100 bg-gray-50 p-6
                        opacity-40 cursor-default select-none">

                <div class="w-9 h-9 rounded-lg border border-gray-200
                            bg-gray-100 flex items-center justify-center">
                    <svg class="w-[18px] h-[18px] text-gray-400"
                         xmlns="http://www.w3.org/2000/svg"
                         fill="none" viewBox="0 0 24 24"
                         stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1
                                 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25
                                 2.25 0 0 0 2.25 6v8.25m19.5 0v.75A2.25 2.25 0 0 1 19.5
                                 17.25h-15A2.25 2.25 0 0 1 2.25 15v-.75"/>
                    </svg>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-gray-700 tracking-wide mb-1">
                        Gruppen
                    </h2>
                    <p class="text-xs text-gray-400 leading-relaxed">
                        Aktivitätsgruppen und<br>Subgruppen verwalten
                    </p>
                </div>

                <span class="absolute top-4 right-4 text-[9px] font-mono
                             tracking-widest uppercase text-gray-300
                             border border-gray-200 rounded px-1.5 py-0.5">
                    geplant
                </span>
            </div>

            {{-- 2. Fotos (geplant) --}}
            <div class="relative flex flex-col gap-5 rounded-xl
                        border border-gray-100 bg-gray-50 p-6
                        opacity-40 cursor-default select-none">

                <div class="w-9 h-9 rounded-lg border border-gray-200
                            bg-gray-100 flex items-center justify-center">
                    <svg class="w-[18px] h-[18px] text-gray-400"
                         xmlns="http://www.w3.org/2000/svg"
                         fill="none" viewBox="0 0 24 24"
                         stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159
                                 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909
                                 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0
                                 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0
                                 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375
                                 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                    </svg>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-gray-700 tracking-wide mb-1">
                        Fotos
                    </h2>
                    <p class="text-xs text-gray-400 leading-relaxed">
                        Foto-Objekte hochladen,<br>bearbeiten, zuordnen
                    </p>
                </div>

                <span class="absolute top-4 right-4 text-[9px] font-mono
                             tracking-widest uppercase text-gray-300
                             border border-gray-200 rounded px-1.5 py-0.5">
                    geplant
                </span>
            </div>

            {{-- 3. Mitglieder --}}
            <a href="{{ route('mandant.kunden.index') }}"
               class="relative flex flex-col gap-5 rounded-xl
                      border border-indigo-100 bg-white p-6
                      hover:border-indigo-300 hover:shadow-sm
                      transition-all duration-150">

                <div class="w-9 h-9 rounded-lg border border-indigo-200
                            bg-indigo-50 flex items-center justify-center">
                    <svg class="w-[18px] h-[18px] text-indigo-500"
                         xmlns="http://www.w3.org/2000/svg"
                         fill="none" viewBox="0 0 24 24"
                         stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0
                                 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003
                                 c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318
                                 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109
                                 a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1
                                 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625
                                 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>
                    </svg>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-gray-800 tracking-wide mb-1">
                        Mitglieder
                    </h2>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Mitglieder einladen, Passcodes<br>verwalten, löschen
                    </p>
                </div>

            </a>


            {{-- 4. Konto --}}
            <a href="{{ route('mandant.konto') }}"
               class="relative flex flex-col gap-5 rounded-xl
                      border border-indigo-100 bg-white p-6
                      hover:border-indigo-300 hover:shadow-sm
                      transition-all duration-150">

                <div class="w-9 h-9 rounded-lg border border-indigo-200
                            bg-indigo-50 flex items-center justify-center">
                    <svg class="w-[18px] h-[18px] text-indigo-500"
                         xmlns="http://www.w3.org/2000/svg"
                         fill="none" viewBox="0 0 24 24"
                         stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5
                                 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933
                                 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                    </svg>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-gray-800 tracking-wide mb-1">
                        Konto
                    </h2>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Profil und Passwort<br>verwalten
                    </p>
                </div>

            </a>

            {{-- 5. Passwortliste --}}
            <a href="{{ route('mandant.pwlist') }}"
               class="relative flex flex-col gap-5 rounded-xl
                      border border-indigo-100 bg-white p-6
                      hover:border-indigo-300 hover:shadow-sm
                      transition-all duration-150">

                <div class="w-9 h-9 rounded-lg border border-indigo-200
                            bg-indigo-50 flex items-center justify-center">
                    <svg class="w-[18px] h-[18px] text-indigo-500"
                         xmlns="http://www.w3.org/2000/svg"
                         fill="none" viewBox="0 0 24 24"
                         stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25
                                 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25
                                 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>
                    </svg>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-gray-800 tracking-wide mb-1">
                        Passwortliste
                    </h2>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Passwörter und Gültigkeit<br>verwalten
                    </p>
                </div>

            </a>

        </div>{{-- /grid --}}

    </main>

    {{-- ══════════════════════════════════════════════════════
         FOOTER
    ══════════════════════════════════════════════════════ --}}
    <footer class="fixed bottom-0 inset-x-0 border-t border-gray-200 bg-white shadow-sm">
        <div class="mx-auto max-w-4xl px-6 h-9
                    flex items-center justify-between">
            <span class="text-[10px] font-mono tracking-widest
                         uppercase text-gray-400">
                Fotosite V8 · Mandanten-Bereich
            </span>
            <span class="text-[10px] text-gray-400">
                Session aktiv
            </span>
        </div>
    </footer>

</body>
</html>
