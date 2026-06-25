{{--
    FILE:    resources/views/system/profile.blade.php
    VERSION: 1.3.1
    DATE:    2026-06-25

    DESCRIPTION:
      System-Eigenverwaltung — profile and password management for the
      logged-in system user. Standalone page, light theme matching
      system/dashboard.blade.php.

    DISPLAYS:
      - Sticky header with brand, username, logout button
      - Back link to system.dashboard
      - Page title "Eigenverwaltung"
      - Card 1: profile fields (PATCH system.profile.update)
      - Card 2: password change (PATCH system.profile.password)

    DATA FROM CONTROLLER:
      $user (SystUser) — full model instance

    ROUTES USED:
      GET  system.dashboard        — back link
      PATCH system.profile.update  — update profile fields
      PATCH system.profile.password — update password
      POST  logout                 — Breeze logout

    CHANGES: 1.3.0 (2026-06-22) Read-only-Anzeige "Primärer System-User: Ja/Nein"
             ergänzt ($user->is_primary, text-gray-600, kein Eingabefeld).
             1.2.0 (2026-06-22) Begleittext zur E-Mail-Adresse ergänzt (Hinweis
             auf 2FA-Codes und Passwort-Erneuerung; Feld ist aktiv/editierbar).
    CHANGES: 1.3.1 (2026-06-25) Android-Touch-Targets vergroessert: Logout-
             Button, Zurueck-Link und beide Submit-Buttons (Speichern/
             Passwort ändern) auf min-h-11 angehoben.
--}}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Eigenverwaltung · Fotogalerie</title>
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
                             uppercase text-amber-600">
                    System
                </span>
            </div>

            {{-- User + Logout --}}
            <div class="flex items-center gap-5">
                <span class="hidden sm:block text-xs text-gray-500
                             truncate max-w-[180px]">
                    {{ $user?->syst_uname ?? 'System' }}
                </span>
                <form method="POST" action="{{ route('system.logout') }}">
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

        {{-- Back link --}}
        <div class="mt-4 mb-6">
            <a href="{{ route('system.dashboard') }}"
               class="inline-flex items-center gap-1.5 min-h-11 py-2 text-sm text-indigo-500
                      hover:text-indigo-700 transition-colors">
                <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg"
                     fill="none" viewBox="0 0 24 24" stroke-width="2"
                     stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M15.75 19.5 8.25 12l7.5-7.5"/>
                </svg>
                Dashboard
            </a>
        </div>

        {{-- Page title --}}
        <div class="mb-8">
            <h1 class="text-xl font-semibold tracking-tight text-gray-800">
                Eigenverwaltung
            </h1>
        </div>

        {{-- Status notice --}}
        @if(session('status'))
            <div class="mb-6 rounded-lg border border-amber-300
                        bg-amber-50 px-4 py-3 text-sm text-amber-700">
                {{ session('status') }}
            </div>
        @endif

        {{-- Error notice --}}
        @if($errors->any())
            <div class="mb-6 rounded-lg border border-red-300
                        bg-red-50 px-4 py-3 text-sm text-red-700 space-y-1">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        {{-- ── Cards ──────────────────────────────────────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- ── Card 1: Profil ── --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">

                <h2 class="text-sm font-semibold text-gray-800 tracking-wide mb-5">
                    Profil
                </h2>

                <form method="POST"
                      action="{{ route('system.profile.update') }}"
                      autocomplete="off">
                    @csrf
                    @method('PATCH')

                    <div class="space-y-4">

                        <div>
                            <label for="syst_uname"
                                   class="block text-sm font-medium text-gray-700">
                                Benutzername
                            </label>
                            <input id="syst_uname" name="syst_uname" type="text"
                                   value="{{ old('syst_uname', $user?->syst_uname) }}"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300
                                          shadow-sm text-sm
                                          focus:border-gray-500 focus:ring-gray-500">
                        </div>

                        <div>
                            <label for="syst_email"
                                   class="block text-sm font-medium text-gray-700">
                                E-Mail
                            </label>
                            <input id="syst_email" name="syst_email" type="email"
                                   value="{{ old('syst_email', $user?->syst_email) }}"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300
                                          shadow-sm text-sm
                                          focus:border-gray-500 focus:ring-gray-500">
                            <p class="mt-1 text-sm text-gray-600">Diese E-Mail-Adresse wird genutzt, um dir Sicherheitscodes bei einem 2-Faktor-Login zu senden. Sie wird auch verwendet, wenn du dein Passwort erneuern musst. Verwende daher eine E-Mail-Adresse, auf die du in solchen Fällen zugreifen kannst, z.B. mit einem E-Mail-Programm auf deinem Handy.</p>
                        </div>

                        <div>
                            <label for="syst_tel"
                                   class="block text-sm font-medium text-gray-700">
                                Telefon
                            </label>
                            <input id="syst_tel" name="syst_tel" type="text"
                                   value="{{ old('syst_tel', $user?->syst_tel) }}"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300
                                          shadow-sm text-sm
                                          focus:border-gray-500 focus:ring-gray-500">
                        </div>

                        <div>
                            <label for="syst_firstname"
                                   class="block text-sm font-medium text-gray-700">
                                Vorname
                            </label>
                            <input id="syst_firstname" name="syst_firstname" type="text"
                                   value="{{ old('syst_firstname', $user?->syst_firstname) }}"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300
                                          shadow-sm text-sm
                                          focus:border-gray-500 focus:ring-gray-500">
                        </div>

                        <div>
                            <label for="syst_lastname"
                                   class="block text-sm font-medium text-gray-700">
                                Nachname
                            </label>
                            <input id="syst_lastname" name="syst_lastname" type="text"
                                   value="{{ old('syst_lastname', $user?->syst_lastname) }}"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300
                                          shadow-sm text-sm
                                          focus:border-gray-500 focus:ring-gray-500">
                        </div>

                    </div>

                    <p class="mt-4 text-sm text-gray-600">
                        Primärer System-User: {{ $user?->is_primary ? 'Ja' : 'Nein' }}
                    </p>

                    <div class="mt-6">
                        <button type="submit"
                                class="w-full flex justify-center py-2 px-4 min-h-11 rounded-md
                                       text-sm font-medium text-white bg-gray-800
                                       hover:bg-gray-700 transition-colors
                                       focus:outline-none focus:ring-2
                                       focus:ring-gray-500 focus:ring-offset-2">
                            Speichern
                        </button>
                    </div>

                </form>
            </div>

            {{-- ── Card 2: Passwort ändern ── --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">

                <h2 class="text-sm font-semibold text-gray-800 tracking-wide mb-5">
                    Passwort ändern
                </h2>

                <form method="POST"
                      action="{{ route('system.profile.password') }}"
                      autocomplete="off">
                    @csrf
                    @method('PATCH')

                    <div class="space-y-4">

                        <div>
                            <label for="current_password"
                                   class="block text-sm font-medium text-gray-700">
                                Aktuelles Passwort
                            </label>
                            <input id="current_password" name="current_password"
                                   type="password" required
                                   class="mt-1 block w-full rounded-md border-gray-300
                                          shadow-sm text-sm
                                          focus:border-gray-500 focus:ring-gray-500">
                        </div>

                        <div>
                            <label for="password"
                                   class="block text-sm font-medium text-gray-700">
                                Neues Passwort
                            </label>
                            <input id="password" name="password"
                                   type="password" required
                                   class="mt-1 block w-full rounded-md border-gray-300
                                          shadow-sm text-sm
                                          focus:border-gray-500 focus:ring-gray-500">
                            <p class="text-sm text-gray-500 mt-1">Mindestanforderungen: 14 Zeichen, Groß- und Kleinbuchstaben, Ziffern, Sonderzeichen.</p>
                        </div>

                        <div>
                            <label for="password_confirmation"
                                   class="block text-sm font-medium text-gray-700">
                                Passwort bestätigen
                            </label>
                            <input id="password_confirmation"
                                   name="password_confirmation"
                                   type="password" required
                                   class="mt-1 block w-full rounded-md border-gray-300
                                          shadow-sm text-sm
                                          focus:border-gray-500 focus:ring-gray-500">
                        </div>

                    </div>

                    <div class="mt-6">
                        <button type="submit"
                                class="w-full flex justify-center py-2 px-4 min-h-11 rounded-md
                                       text-sm font-medium text-white bg-gray-800
                                       hover:bg-gray-700 transition-colors
                                       focus:outline-none focus:ring-2
                                       focus:ring-gray-500 focus:ring-offset-2">
                            Passwort ändern
                        </button>
                    </div>

                </form>
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
                Fotogalerie · System-Bereich
            </span>
            <span class="text-[10px] text-gray-400">
                Session aktiv
            </span>
        </div>
    </footer>

</body>
</html>
