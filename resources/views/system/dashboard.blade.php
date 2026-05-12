{{--
    FILE:    resources/views/system/dashboard.blade.php
    VERSION: 1.2.0

    DESCRIPTION:
      System-Dashboard — landing page after successful system login + 2FA.
      Standalone page (no layout inheritance), matching the style of
      system/login.blade.php.

    DISPLAYS:
      - Top bar with system user name and logout button
      - Three navigation tiles:
          1. Eigenverwaltung     (stub — not yet built)
          2. Mandantenverwaltung (stub — not yet built)
          3. Content-Verwaltung  (placeholder — planned)

    DATA FROM CONTROLLER:
      $userName  (string) — syst_uname
      $userEmail (string) — syst_email
      $firstName (string) — syst_firstname
      $lastName  (string) — syst_lastname

    ROUTES USED:
      POST /logout — Breeze logout (route('logout'))
--}}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>System-Dashboard · Fotosite V8</title>
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
                             uppercase text-amber-600">
                    System
                </span>
            </div>

            {{-- User + Logout --}}
            <div class="flex items-center gap-5">
                <span class="hidden sm:block text-xs text-gray-500
                             truncate max-w-[180px]">
                    {{ $userName }}
                </span>
                <form method="POST" action="{{ route('logout') }}">
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

        {{-- Page title --}}
        <div class="mb-10">
            <h1 class="text-xl font-semibold tracking-tight text-gray-800">
                Dashboard
            </h1>
            <p class="mt-1.5 text-sm text-zinc-600">
                Angemeldet als
                <span class="text-gray-700 font-medium">{{ $userName }}</span>
                @if($userEmail)
                    &thinsp;·&thinsp;
                    <span class="text-zinc-600">{{ $userEmail }}</span>
                @endif
            </p>
        </div>

        {{-- Flash messages --}}
        @if(session('status'))
            <div class="mb-8 rounded-lg border border-amber-700/40
                        bg-amber-900/20 px-4 py-3 text-sm text-amber-300">
                {{ session('status') }}
            </div>
        @endif

        {{-- ── Navigation Tiles ────────────────────────────── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

            {{-- ── 1. Eigenverwaltung ── --}}
            <a href="{{ route('system.profile') }}"
               class="group relative flex flex-col gap-5 rounded-xl
                      border border-gray-200 bg-white p-6 shadow-sm
                      hover:border-amber-400 hover:shadow-md transition-all duration-200">

                <div class="w-9 h-9 rounded-lg border border-gray-200
                            bg-gray-100 flex items-center justify-center">
                    <svg class="w-[18px] h-[18px] text-gray-500"
                         xmlns="http://www.w3.org/2000/svg"
                         fill="none" viewBox="0 0 24 24"
                         stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z
                                 M4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1
                                 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                    </svg>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-gray-800 tracking-wide mb-1">
                        Eigenverwaltung
                    </h2>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Profil, E-Mail, Passwort,<br>2FA-Einstellungen
                    </p>
                </div>

                <span class="absolute top-4 right-4 text-[9px] font-mono
                             tracking-widest uppercase text-amber-600
                             border border-amber-200 rounded px-1.5 py-0.5">
                    verfügbar
                </span>
            </a>

            {{-- ── 2. Mandantenverwaltung ── --}}
            <div class="group relative flex flex-col gap-5 rounded-xl
                        border border-gray-200 bg-white p-6 shadow-sm
                        opacity-60 cursor-not-allowed select-none"
                 title="Wird im nächsten Schritt umgesetzt"
                 @click="alert('Mandantenverwaltung wird im nächsten Schritt umgesetzt.')">

                <div class="w-9 h-9 rounded-lg border border-gray-200
                            bg-gray-100 flex items-center justify-center">
                    <svg class="w-[18px] h-[18px] text-gray-500"
                         xmlns="http://www.w3.org/2000/svg"
                         fill="none" viewBox="0 0 24 24"
                         stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18
                                 M9 6.75h1.5m-1.5 3.75h1.5m-1.5 3.75h1.5
                                 m3-7.5H15m-1.5 3.75H15m-1.5 3.75H15
                                 M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75
                                 c.621 0 1.125.504 1.125 1.125V21"/>
                    </svg>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-gray-800 tracking-wide mb-1">
                        Mandantenverwaltung
                    </h2>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Mandanten anlegen, bearbeiten,<br>löschen · CRUD
                    </p>
                </div>

                <span class="absolute top-4 right-4 text-[9px] font-mono
                             tracking-widest uppercase text-gray-400
                             border border-gray-200 rounded px-1.5 py-0.5">
                    ausstehend
                </span>
            </div>

            {{-- ── 3. Content-Verwaltung (Platzhalter) ── --}}
            <div class="relative flex flex-col gap-5 rounded-xl
                        border border-gray-100 bg-gray-50 p-6
                        opacity-30 cursor-default select-none">

                <div class="w-9 h-9 rounded-lg border border-zinc-800/60
                            bg-zinc-900 flex items-center justify-center">
                    <svg class="w-[18px] h-[18px] text-zinc-700"
                         xmlns="http://www.w3.org/2000/svg"
                         fill="none" viewBox="0 0 24 24"
                         stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159
                                 m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909
                                 m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5
                                 H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z
                                 m10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0
                                 .375.375 0 0 1 .75 0Z"/>
                    </svg>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-gray-500 tracking-wide mb-1">
                        Content-Verwaltung
                    </h2>
                    <p class="text-xs text-gray-400 leading-relaxed">
                        Systemweite Inhaltsübersicht
                    </p>
                </div>

                <span class="absolute top-4 right-4 text-[9px] font-mono
                             tracking-widest uppercase text-gray-300
                             border border-gray-200 rounded px-1.5 py-0.5">
                    geplant
                </span>
            </div>

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
                Fotosite V8 · System-Bereich
            </span>
            <span class="text-[10px] text-gray-400">
                Session aktiv
            </span>
        </div>
    </footer>

</body>
</html>
