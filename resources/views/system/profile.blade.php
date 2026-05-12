{{--
    FILE:    resources/views/system/profile.blade.php
    VERSION: 1.0.0

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
--}}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Eigenverwaltung · Fotosite V8</title>
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

        {{-- Back link --}}
        <div class="mb-6">
            <a href="{{ route('system.dashboard') }}"
               class="text-xs text-gray-400 hover:text-gray-600
                      transition-colors duration-150 tracking-wide">
                ← Dashboard
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

                    <div class="mt-6">
                        <button type="submit"
                                class="w-full flex justify-center py-2 px-4 rounded-md
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
                                class="w-full flex justify-center py-2 px-4 rounded-md
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
                Fotosite V8 · System-Bereich
            </span>
            <span class="text-[10px] text-gray-400">
                Session aktiv
            </span>
        </div>
    </footer>

</body>
</html>
