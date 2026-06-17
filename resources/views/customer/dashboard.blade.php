{{--
    FILE:    resources/views/customer/dashboard.blade.php
    VERSION: 2.1.0
    DATE:    2026-06-13

    DESCRIPTION:
      Kunden-Dashboard — Verwaltungsübersicht für registrierte Mitglieder (cust).
      Standalone (kein Layout-Erbe). Accent-Farbe: indigo.
      Anonyme Besucher (anon) werden nach Login direkt zu customer.content
      geleitet und erreichen dieses Dashboard nicht mehr.

    DATA FROM CONTROLLER:
      $cust              — CustUser|null
      $showPasskeyPrompt — bool, einmaliger Passkey-Prompt-Flag
      $passkeyOs         — string, erkanntes OS ('win'|'andr'|'ios'|'unknown')

    ROUTES USED:
      POST customer.logout           — Abmelden
      GET  customer.konto            — Konto-Verwaltung
      GET  customer.galerien         — Galerien-Verwaltung
      GET  customer.passkeys         — Passkey-Verwaltung
      POST customer.passkeys.dismiss — "Nie wieder fragen"
--}}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Einstellungen · Fotogalerie</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-50 text-gray-900 antialiased">

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
                    Fotogalerie
                </span>
                <span class="text-zinc-800 select-none">|</span>
                <span class="text-sm font-semibold tracking-widest
                             uppercase text-indigo-600">
                    {{ $cust?->cust_firstname ?? 'Mitglied' }}
                </span>
            </div>

            {{-- Logout --}}
            <div class="flex items-center">
                <form method="POST" action="{{ route('customer.logout') }}">
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
                Meine Einstellungen
            </h1>
            <p class="mt-1.5 text-sm text-zinc-600">
                Willkommen, {{ $cust?->cust_firstname ?? 'Mitglied' }}!
            </p>
        </div>

        {{-- Flash: Status --}}
        @if(session('status'))
            <div class="mb-8 rounded-lg border border-indigo-200
                        bg-indigo-50 px-4 py-3 text-sm text-indigo-700">
                {{ session('status') }}
            </div>
        @endif

        {{-- Passkey-Aufforderung Banner --}}
        @if($showPasskeyPrompt)
        <div x-data="{ open: true }" x-show="open" x-cloak
             class="bg-indigo-50 border border-indigo-200 rounded-lg
                    p-4 mb-8 flex flex-col md:flex-row md:items-start gap-4">
            <div class="flex-1">
                <p class="text-sm font-medium text-indigo-800">
                    Passkey einrichten — schneller und sicherer anmelden
                </p>
                <p class="text-xs text-indigo-600 mt-1">
                    @if($passkeyOs === 'win')
                        Der Passkey wird lokal gespeichert und ist an dieses
                        Windows-Konto gebunden.
                    @elseif($passkeyOs === 'ios')
                        Der Passkey wird in Ihrer iCloud Keychain gespeichert —
                        auf allen Apple-Geräten verfügbar.
                    @elseif($passkeyOs === 'andr')
                        Der Passkey wird im Google Passwort-Manager gespeichert —
                        auf allen Android-Geräten mit demselben Google-Konto
                        verfügbar.
                    @endif
                </p>
            </div>
            <div class="flex flex-col gap-2 md:flex-row md:gap-3 w-full md:w-auto">
                <a href="{{ route('customer.passkeys') }}"
                   class="w-full md:w-auto text-center px-4 py-3 md:py-2
                          bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700">
                    Einrichten
                </a>
                <button @click="
                    open = false;
                    fetch('{{ route('customer.passkeys.dismiss') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    })"
                    class="w-full md:w-auto px-4 py-3 md:py-2 text-sm text-gray-500
                           border border-gray-300 rounded-lg hover:bg-gray-50">
                    Nie wieder
                </button>
                <button @click="open = false"
                        class="w-full md:w-auto px-4 py-3 md:py-2 text-sm text-gray-400
                               hover:text-gray-600">
                    Später
                </button>
            </div>
        </div>
        @endif

        {{-- ── Navigations-Kacheln ──────────────────────────── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

            {{-- 1. Mein Konto --}}
            <a href="{{ route('customer.konto') }}"
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
                        Mein Konto
                    </h2>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Kontaktdaten und Passwort<br>verwalten.
                    </p>
                </div>

            </a>

            {{-- 2. Meine Galerien --}}
            <a href="{{ route('customer.galerien') }}"
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
                              d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159
                                 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909
                                 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0
                                 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0
                                 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375
                                 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                    </svg>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-gray-800 tracking-wide mb-1">
                        Meine Galerien
                    </h2>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Reihenfolge und Benachrichtigungen<br>für deine Galeristen verwalten.
                    </p>
                </div>

            </a>

            {{-- 3. Passkeys verwalten --}}
            <a href="{{ route('customer.passkeys') }}"
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
                              d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5
                                 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1
                                 .43-1.563A6 6 0 0 1 21.75 8.25Z"/>
                    </svg>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-gray-800 tracking-wide mb-1">
                        Passkeys verwalten
                    </h2>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Mit Fingerabdruck oder Gesichtserkennung<br>anmelden.
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
                Fotogalerie · Mitglieder-Bereich
            </span>
            <span class="text-[10px] text-gray-400">
                Session aktiv
            </span>
        </div>
    </footer>

</body>
</html>
