{{--
    FILE:    resources/views/customer/dashboard.blade.php
    VERSION: 1.2.0
    AUTHOR:  Martin Wagner
    DATE:    2026-06-07

    DESCRIPTION:
      Kunden-Dashboard — Einstiegsseite für registrierte Mitglieder (cust)
      und anonyme Besucher (anon) nach erfolgreichem Login.
      Standalone (kein Layout-Erbe). Accent-Farbe: indigo.

    DATA FROM CONTROLLER:
      $userType          — 'cust' oder 'anon'
      $mand              — MandUser (aktive/einzige Galerie)
      $secLevel          — int (Sicherheitsstufe dieser Session)
      $cust              — CustUser|null (nur für cust)
      $pcodes            — Collection<CustPcode>|null (nur für cust, mit mandUser)
      $showPasskeyPrompt — bool, einmaliger Passkey-Prompt-Flag (CustDashboardController)
      $passkeyOs         — string, erkanntes OS ('win'|'andr'|'ios'|'unknown')

    ROUTES USED:
      POST customer.logout            — Abmelden
      GET  customer.passkeys          — Passkey-Verwaltung
      POST customer.passkeys.dismiss  — "Nie wieder fragen"
--}}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Dashboard · Fotosite V8</title>
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
                    Fotosite&thinsp;V8
                </span>
                <span class="text-zinc-800 select-none">|</span>
                <span class="text-sm font-semibold tracking-widest
                             uppercase text-indigo-600">
                    @if($userType === 'cust')
                        {{ $cust?->cust_firstname ?? 'Mitglied' }}
                    @else
                        Gast
                    @endif
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
    <main class="mx-auto max-w-4xl px-6 pt-10 pb-24">

        {{-- Flash: Status --}}
        @if(session('status'))
            <div class="mb-6 rounded-lg border border-indigo-200
                        bg-indigo-50 px-4 py-3 text-sm text-indigo-700">
                {{ session('status') }}
            </div>
        @endif

        {{-- Passkey-Aufforderung Banner --}}
        @if($showPasskeyPrompt)
        <div x-data="{ open: true }" x-show="open" x-cloak
             class="bg-indigo-50 border border-indigo-200 rounded-lg
                    p-4 mb-4 flex items-start gap-4">
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
            <div class="flex gap-2 shrink-0">
                <a href="{{ route('customer.passkeys') }}"
                   class="px-3 py-1.5 bg-indigo-600 text-white text-xs
                          rounded-lg hover:bg-indigo-700">
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
                    class="px-3 py-1.5 text-xs text-gray-500
                           border border-gray-300 rounded-lg hover:bg-gray-50">
                    Nie wieder
                </button>
                <button @click="open = false"
                        class="px-3 py-1.5 text-xs text-gray-400
                               hover:text-gray-600">
                    Später
                </button>
            </div>
        </div>
        @endif

        @if($userType === 'anon')

            {{-- ── Anonymer Besucher ──────────────────────── --}}
            <div class="mb-8">
                <h1 class="text-xl font-semibold tracking-tight text-gray-800">
                    Willkommen
                </h1>
                <p class="mt-1.5 text-sm text-zinc-600">
                    Sie sind als Gast bei
                    <span class="font-medium text-gray-800">
                        {{ genitivName($mand?->mand_uname ?? '') }}
                    </span>
                    Fotogalerie angemeldet.
                </p>
                <p class="mt-1 text-xs text-gray-400">
                    Sicherheitsstufe: {{ $secLevel }}
                </p>
            </div>

            <div class="rounded-xl border border-dashed border-gray-300
                        bg-white px-6 py-12 text-center text-sm text-gray-400">
                Fotoinhalte folgen in Phase 7
            </div>

        @else

            {{-- ── Registriertes Mitglied ─────────────────── --}}
            <div class="mb-8">
                <h1 class="text-xl font-semibold tracking-tight text-gray-800">
                    Willkommen, {{ $cust?->cust_firstname ?? 'Mitglied' }}!
                </h1>
                <p class="mt-1.5 text-sm text-zinc-600">
                    Aktive Galerie:
                    <span class="font-medium text-gray-800">
                        {{ $mand?->mand_uname ?? '—' }}
                    </span>
                    · Stufe {{ $secLevel }}
                </p>
            </div>

            {{-- Galerie-Liste --}}
            @if($pcodes && $pcodes->isNotEmpty())
                <div class="mb-8">
                    <h2 class="text-sm font-semibold text-gray-700
                               tracking-wide uppercase mb-3">
                        Meine Galerien
                    </h2>
                    <div class="space-y-2">
                        @foreach($pcodes as $pcode)
                            @php $isActive = $mand && $pcode->mand_id === $mand->mand_id; @endphp
                            <div class="flex items-center justify-between rounded-lg
                                        border px-4 py-3 text-sm
                                        {{ $isActive
                                            ? 'border-indigo-300 bg-indigo-50'
                                            : 'border-gray-200 bg-white' }}">
                                <div>
                                    <span class="font-medium
                                                 {{ $isActive ? 'text-indigo-700' : 'text-gray-800' }}">
                                        {{ $pcode->mandUser?->mand_uname ?? '—' }}
                                    </span>
                                    <span class="ml-2 text-xs text-gray-400">
                                        Stufe {{ $pcode->cust_passcode }}
                                    </span>
                                </div>
                                @if($isActive)
                                    <span class="text-xs font-medium text-indigo-600
                                                 tracking-wide uppercase">
                                        aktiv
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Passkeys verwalten --}}
            <div class="mt-6 mb-4 flex items-center justify-between
                        rounded-lg border border-gray-100 bg-white px-4 py-3">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 text-indigo-400 shrink-0"
                         xmlns="http://www.w3.org/2000/svg"
                         fill="none" viewBox="0 0 24 24"
                         stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5
                                 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1
                                 .43-1.563A6 6 0 0 1 21.75 8.25Z"/>
                    </svg>
                    <span class="text-sm text-gray-700">Passkeys</span>
                </div>
                <a href="{{ route('customer.passkeys') }}"
                   class="text-xs font-medium text-indigo-600
                          hover:text-indigo-800 transition-colors">
                    Verwalten →
                </a>
            </div>

            <div class="rounded-xl border border-dashed border-gray-300
                        bg-white px-6 py-12 text-center text-sm text-gray-400">
                Fotoinhalte folgen in Phase 7
            </div>

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
                Fotosite V8 · Mitglieder-Bereich
            </span>
            <span class="text-[10px] text-gray-400">
                Session aktiv
            </span>
        </div>
    </footer>

</body>
</html>
