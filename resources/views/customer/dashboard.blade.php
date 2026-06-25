{{--
    FILE:    resources/views/customer/dashboard.blade.php
    VERSION: 2.9.0
    DATE:    2026-06-25

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
      POST customer.logout              — Abmelden
      GET  customer.konto               — Konto-Verwaltung
      POST customer.konto.passwort      — Passwort-Modal speichern
      POST customer.konto.email-aendern — E-Mail-Modal senden
      GET  customer.galerien            — Galerien-Verwaltung
      GET  customer.passkeys            — Passkey-Verwaltung
      POST customer.passkeys.dismiss    — "Nie wieder fragen"
      GET  customer.datenschutz.erlaeuterung          — Datenschutz-Erläuterung (neuer Tab)
      GET  customer.datenschutz.upload-bedingungen-pdf — Upload-Bedingungen (neuer Tab)
      GET  customer.faq.index                   — FAQ und Infos

    CHANGES: 2.8.0 (2026-06-25) Android-Touch-Targets vergroessert: Logout-
             Button, Passwort/E-Mail-aendern-Buttons, Rechtliches-Links,
             Modal-Buttons (Abbrechen/Speichern/Senden) und Passkey-Banner-
             Aktionen auf min-h-11 angehoben.
    CHANGES: 2.7.0 (2026-06-23) Spam-Hinweis im E-Mail-Modal ergänzt: weist
             darauf hin, dass die Bestätigungsmail oft im Spam-Ordner landet.
             2.6.0 (2026-06-22) Begleittext im E-Mail-Modal ersetzt durch
             Hinweis auf 2FA-Codes und Passwort-Erneuerung.
             2.5.0 (2026-06-22) E-Mail-Feld im E-Mail-Modal vereinheitlicht:
             Placeholder ergänzt, Fehlermeldung blendet sich bei Eingabe aus.
             2.4.0 (2026-06-20) Button "FAQ und Infos" in der Rechtliches-
             Sektion ergänzt, Link zu customer.faq.index (siehe FaqController).
             2.3.0 (2026-06-18) "Rechtliches"-Sektion ergänzt: Buttons
             "Datenschutz-Erläuterung" / "Upload-Bedingungen", öffnen jeweils in
             neuem Tab.
             2.2.0 (2026-06-18) Passwort-Modal und E-Mail-Modal ergänzt (Buttons
             "Passwort ändern" / "E-Mail ändern").
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

<body class="min-h-screen bg-gray-50 text-gray-900 antialiased"
      x-data="{
          pwModalOpen: {{ $errors->hasAny(['current_password', 'password', 'password_confirmation']) ? 'true' : 'false' }},
          emailModalOpen: {{ $errors->has('email') ? 'true' : 'false' }}
      }">

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
                            class="min-h-11 py-2 px-3 text-sm text-gray-400 hover:text-red-500
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

        {{-- Sicherheits-Aktionen: Passwort / E-Mail ändern --}}
        <div class="flex flex-wrap gap-3 mb-8">
            <button type="button" @click="pwModalOpen = true"
                    class="px-4 py-2 min-h-11 text-sm font-medium text-indigo-700
                           bg-indigo-50 border border-indigo-200 rounded-lg
                           hover:bg-indigo-100 transition-colors">
                Passwort ändern
            </button>
            <button type="button" @click="emailModalOpen = true"
                    class="px-4 py-2 min-h-11 text-sm font-medium text-indigo-700
                           bg-indigo-50 border border-indigo-200 rounded-lg
                           hover:bg-indigo-100 transition-colors">
                E-Mail ändern
            </button>
        </div>

        {{-- Rechtliches: Datenschutz / Upload-Bedingungen --}}
        <div class="mb-8">
            <h2 class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">
                Rechtliches
            </h2>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('customer.datenschutz.erlaeuterung') }}" target="_blank"
                   class="px-4 py-2 min-h-11 text-sm font-medium text-gray-600
                          bg-white border border-gray-300 rounded-lg
                          hover:bg-gray-50 transition-colors">
                    Datenschutz-Erläuterung
                </a>
                <a href="{{ route('customer.datenschutz.upload-bedingungen-pdf') }}" target="_blank"
                   class="px-4 py-2 min-h-11 text-sm font-medium text-gray-600
                          bg-white border border-gray-300 rounded-lg
                          hover:bg-gray-50 transition-colors">
                    Upload-Bedingungen
                </a>
                <a href="{{ route('customer.faq.index') }}"
                   class="px-4 py-2 min-h-11 text-sm font-medium text-gray-600
                          bg-white border border-gray-300 rounded-lg
                          hover:bg-gray-50 transition-colors">
                    FAQ und Infos
                </a>
            </div>
        </div>

        {{-- Modal: Passwort ändern --}}
        <div x-show="pwModalOpen" x-cloak
             class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 px-4">
            <div class="bg-white rounded-xl p-6 max-w-sm w-full shadow-xl"
                 @click.outside="pwModalOpen = false">
                <h3 class="font-semibold text-gray-800 mb-4">Passwort ändern</h3>

                <p class="mb-4 text-xs text-gray-400">
                    Nach erfolgreicher Änderung werden Sie zur Anmeldung weitergeleitet.
                </p>

                <form method="POST" action="{{ route('customer.konto.passwort') }}" autocomplete="off">
                    @csrf
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                Aktuelles Passwort
                            </label>
                            <div class="relative mt-1" x-data="{ show: false }">
                                <input name="current_password" :type="show ? 'text' : 'password'" required
                                       autocomplete="current-password"
                                       class="block w-full rounded-md border-gray-300
                                              shadow-sm text-sm pr-10
                                              focus:border-indigo-500 focus:ring-indigo-500
                                              @error('current_password') border-red-400 @enderror">
                                <button type="button"
                                        @click="show = !show"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3
                                               text-gray-400 hover:text-gray-600">
                                    <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943
                                                 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943
                                                 -9.542-7z"/>
                                    </svg>
                                    <svg x-show="show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943
                                                 -9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243
                                                 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532
                                                 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5
                                                 c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132
                                                 5.411m0 0L21 21"/>
                                    </svg>
                                </button>
                            </div>
                            @error('current_password')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                Neues Passwort
                            </label>
                            <div class="relative mt-1" x-data="{ show: false }">
                                <input name="password" :type="show ? 'text' : 'password'" required
                                       autocomplete="new-password"
                                       class="block w-full rounded-md border-gray-300
                                              shadow-sm text-sm pr-10
                                              focus:border-indigo-500 focus:ring-indigo-500
                                              @error('password') border-red-400 @enderror">
                                <button type="button"
                                        @click="show = !show"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3
                                               text-gray-400 hover:text-gray-600">
                                    <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943
                                                 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943
                                                 -9.542-7z"/>
                                    </svg>
                                    <svg x-show="show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943
                                                 -9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243
                                                 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532
                                                 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5
                                                 c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132
                                                 5.411m0 0L21 21"/>
                                    </svg>
                                </button>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">Mindestens 10 Zeichen.</p>
                            @error('password')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                Passwort bestätigen
                            </label>
                            <div class="relative mt-1" x-data="{ show: false }">
                                <input name="password_confirmation" :type="show ? 'text' : 'password'" required
                                       autocomplete="new-password"
                                       class="block w-full rounded-md border-gray-300
                                              shadow-sm text-sm pr-10
                                              focus:border-indigo-500 focus:ring-indigo-500">
                                <button type="button"
                                        @click="show = !show"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3
                                               text-gray-400 hover:text-gray-600">
                                    <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943
                                                 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943
                                                 -9.542-7z"/>
                                    </svg>
                                    <svg x-show="show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943
                                                 -9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243
                                                 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532
                                                 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5
                                                 c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132
                                                 5.411m0 0L21 21"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 flex gap-3">
                        <button type="button" @click="pwModalOpen = false"
                                class="w-full px-4 py-2 min-h-11 text-sm text-gray-500
                                       border border-gray-300 rounded-lg hover:bg-gray-50">
                            Abbrechen
                        </button>
                        <button type="submit"
                                class="w-full px-4 py-2 min-h-11 text-sm font-medium text-white
                                       bg-indigo-600 rounded-lg hover:bg-indigo-700">
                            Speichern
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal: E-Mail ändern --}}
        <div x-show="emailModalOpen" x-cloak
             class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 px-4">
            <div class="bg-white rounded-xl p-6 max-w-sm w-full shadow-xl"
                 @click.outside="emailModalOpen = false">
                <h3 class="font-semibold text-gray-800 mb-4">E-Mail ändern</h3>

                @if(session('email_change_status'))
                    <div class="mb-4 rounded-lg border border-indigo-200
                                bg-indigo-50 px-3 py-2 text-sm text-indigo-700">
                        {{ session('email_change_status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('customer.konto.email-aendern') }}" autocomplete="off">
                    @csrf
                    <div class="space-y-3">
                        <div x-data="{ dirty: false }">
                            <label class="block text-sm font-medium text-gray-700">
                                Neue E-Mail-Adresse
                            </label>
                            <input name="email" type="email" required
                                   placeholder="ihre@email.de"
                                   @input="dirty = true"
                                   class="mt-1 block w-full rounded-md border-gray-300
                                          shadow-sm text-sm
                                          focus:border-indigo-500 focus:ring-indigo-500
                                          @error('email') border-red-400 @enderror">
                            @error('email')
                                <p class="mt-1 text-xs text-red-600" x-show="!dirty">{{ $message }}</p>
                            @enderror
                        </div>
                        <p class="mt-1 text-sm text-gray-600">Diese E-Mail-Adresse wird genutzt, um dir Sicherheitscodes bei einem 2-Faktor-Login zu senden. Sie wird auch verwendet, wenn du dein Passwort erneuern musst. Verwende daher eine E-Mail-Adresse, auf die du in solchen Fällen zugreifen kannst, z.B. mit einem E-Mail-Programm auf deinem Handy.</p>
                        <p class="mt-2 text-sm text-amber-600">Bitte denk daran, dass E-Mails wie
                        diese oft im Spam-Ordner landen. Wenn du die E-Mail nicht bekommst,
                        prüfe den Spam-Ordner.</p>
                    </div>
                    <div class="mt-5 flex gap-3">
                        <button type="button" @click="emailModalOpen = false"
                                class="w-full px-4 py-2 min-h-11 text-sm text-gray-500
                                       border border-gray-300 rounded-lg hover:bg-gray-50">
                            Abbrechen
                        </button>
                        <button type="submit"
                                class="w-full px-4 py-2 min-h-11 text-sm font-medium text-white
                                       bg-indigo-600 rounded-lg hover:bg-indigo-700">
                            Senden
                        </button>
                    </div>
                </form>
            </div>
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
                   class="w-full md:w-auto text-center px-4 py-3 md:py-2 min-h-11
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
                    class="w-full md:w-auto px-4 py-3 md:py-2 min-h-11 text-sm text-gray-500
                           border border-gray-300 rounded-lg hover:bg-gray-50">
                    Nie wieder
                </button>
                <button @click="open = false"
                        class="w-full md:w-auto px-4 py-3 md:py-2 min-h-11 text-sm text-gray-400
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
