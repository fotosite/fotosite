{{--
    FILE:    resources/views/customer/content.blade.php
    VERSION: 1.0.0
    DATE:    2026-06-13

    DESCRIPTION:
      Kunden-Inhaltsseite — Landing-Page nach erfolgreichem Login für
      registrierte Mitglieder (cust) und anonyme Besucher (anon).
      Standalone (kein Layout-Erbe). Accent-Farbe: indigo.

    DATA FROM CONTROLLER:
      $userType — 'cust' oder 'anon'
      $mand     — MandUser|null (aktiver Mandant)

    ROUTES USED:
      POST customer.logout     — Abmelden (nur cust)
      GET  customer.dashboard  — Zum Mitglieder-Dashboard (nur cust)
--}}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Fotogalerie</title>
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
                    @if($userType === 'cust')
                        Mitglied
                    @else
                        Gast
                    @endif
                </span>
            </div>

            {{-- Logout — nur für registrierte Mitglieder --}}
            @if($userType === 'cust')
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
            @endif

        </div>
    </header>

    {{-- ══════════════════════════════════════════════════════
         MAIN
    ══════════════════════════════════════════════════════ --}}
    <main class="mx-auto max-w-4xl px-6 pt-10 pb-24">

        {{-- Flash: Status --}}
        @if(session('status'))
            <div class="mb-6 rounded-lg border border-indigo-200
                        bg-indigo-50 px-4 py-3 text-sm text-indigo-700">
                {{ session('status') }}
            </div>
        @endif

        {{-- Platzhalter --}}
        <div class="rounded-xl border border-dashed border-gray-300
                    bg-white px-6 py-16 text-center">
            <p class="text-sm text-gray-500">
                Hier entstehen die Galerie-Inhalte von
                <span class="font-medium text-gray-700">
                    {{ $mand?->mand_uname ?? 'diesem Galeristen' }}
                </span>.
            </p>
            <p class="mt-1 text-xs text-gray-400">
                Phase 7 — in Entwicklung
            </p>
        </div>

        @if($userType === 'cust')
            {{-- Dashboard-Link für registrierte Mitglieder --}}
            <div class="mt-6 text-center">
                <a href="{{ route('customer.dashboard') }}"
                   class="text-sm text-indigo-600 hover:text-indigo-800
                          transition-colors">
                    ← Zum Mitglieder-Dashboard
                </a>
            </div>
        @else
            {{-- Hinweis für anonyme Besucher --}}
            <p class="mt-4 text-center text-xs text-gray-400">
                Diese Sitzung läuft nach Inaktivität automatisch ab.
            </p>
        @endif

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
