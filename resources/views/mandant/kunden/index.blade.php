{{--
    FILE:    resources/views/mandant/kunden/index.blade.php
    VERSION: 1.0.0
    AUTHOR:  Martin Wagner
    DATE:    2026-05-30

    DESCRIPTION:
      Kundenliste des eingeloggten Mandanten.
      Gleiches Struktur-/Layout-Muster wie mandant/dashboard.blade.php.

    ROUTES USED:
      POST /mandant/logout          — Mandant-Logout (route('mandant.logout'))
      GET  /mandant/kunden/einladen — Kunden einladen (route('mandant.kunden.invite'))
      GET  /mandant/dashboard       — Dashboard (route('mandant.dashboard'))
--}}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Kundenliste · Fotosite V8</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-50 text-gray-900 antialiased"
      x-data>

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
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold tracking-tight text-gray-800">
                    Kundenliste
                </h1>
                <p class="mt-1.5 text-sm text-zinc-600">
                    Ihre eingeladenen Kunden und deren Passcode-Status.
                </p>
            </div>
            <a href="{{ route('mandant.kunden.invite') }}"
               class="inline-flex items-center gap-2 rounded-lg
                      border border-indigo-300 bg-indigo-50 px-4 py-2
                      text-sm font-medium text-indigo-700
                      hover:bg-indigo-100 hover:border-indigo-400
                      transition-colors duration-150">
                Kunden einladen
            </a>
        </div>

        {{-- Flash-Meldung --}}
        @if(session('status'))
            <div class="mb-6 rounded-lg border border-indigo-200
                        bg-indigo-50 px-4 py-3 text-sm text-indigo-700">
                {{ session('status') }}
            </div>
        @endif

        {{-- Platzhalter --}}
        <div class="rounded-xl border border-dashed border-gray-300
                    bg-white px-6 py-12 text-center text-sm text-gray-400">
            Kundenliste — folgt
        </div>

        {{-- Zurück --}}
        <div class="mt-8">
            <a href="{{ route('mandant.dashboard') }}"
               class="text-xs text-gray-400 hover:text-indigo-600
                      transition-colors duration-150 tracking-wide">
                ← Zurück zum Dashboard
            </a>
        </div>

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
