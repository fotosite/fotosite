{{--
    FILE:    resources/views/mandant/konto.blade.php
    VERSION: 1.4.2
    DATE:    2026-06-08

    DESCRIPTION:
      Mandant Eigenverwaltung — Kontaktdaten und Passwort bearbeiten.
      Standalone (kein Layout-Erbe), gleiches Strukturmuster wie mandant/dashboard.
      Accent-Farbe: indigo.

    DATA FROM CONTROLLER:
      $mand (MandUser) — vollständige Instanz (ab Abschnitt 3)

    ROUTES USED:
      GET   mandant.dashboard       — Zurück-Link
      PATCH mandant.konto.update    — Kontaktdaten speichern
      PATCH mandant.konto.password  — Passwort ändern
      POST  mandant.logout          — Abmelden
--}}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Konto · Fotogalerie</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-50 text-gray-900 antialiased"
      x-data>

    @php $mandUname = \App\Models\UserDb\MandUser::find(session('_mand_id'))?->mand_uname ?? ''; @endphp

    {{-- ══════════════════════════════════════════════════════
         TOP BAR
    ══════════════════════════════════════════════════════ --}}
    <header class="sticky top-0 z-20 border-b border-gray-200 bg-white shadow-sm">
        <div class="mx-auto max-w-3xl px-6 h-14
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
    <main class="mx-auto max-w-3xl px-6 pt-10 pb-24">

        {{-- Zurück-Link --}}
        <div class="mt-4 mb-6">
            <a href="{{ route('mandant.dashboard') }}"
               class="inline-flex items-center gap-1.5 text-xs text-indigo-500
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

        {{-- Seitenüberschrift --}}
        <div class="mb-8">
            <h1 class="text-xl font-semibold tracking-tight text-gray-800">
                Konto
            </h1>
            <p class="mt-1.5 text-sm text-zinc-600">
                Kontaktdaten und Passwort verwalten.
            </p>
        </div>

        {{-- ── Sektion 1: Kontaktdaten ─────────────────────── --}}

        {{-- Flash: Kontaktdaten gespeichert --}}
        @if(session('status'))
            <div class="mb-6 rounded-lg border border-indigo-200
                        bg-indigo-50 px-4 py-3 text-sm text-indigo-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">

            <h2 class="text-sm font-semibold text-gray-800 tracking-wide mb-5">
                Kontaktdaten
            </h2>

            <form method="POST"
                  action="{{ route('mandant.konto.update') }}"
                  autocomplete="off">
                @csrf
                @method('PATCH')

                <div class="space-y-4">

                    {{-- mand_uname (optional) --}}
                    <div>
                        <label for="mand_uname"
                               class="block text-sm font-medium text-gray-700">
                            Benutzername
                            <span class="text-gray-400 font-normal">(optional)</span>
                        </label>
                        <input id="mand_uname" name="mand_uname" type="text"
                               value="{{ old('mand_uname', $mand->mand_uname) }}"
                               class="mt-1 block w-full rounded-md border-gray-300
                                      shadow-sm text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500
                                      @error('mand_uname') border-red-400 @enderror">
                        @error('mand_uname')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- mand_email --}}
                    <div>
                        <label for="mand_email"
                               class="block text-sm font-medium text-gray-700">
                            E-Mail
                        </label>
                        <input id="mand_email" name="mand_email" type="email"
                               value="{{ old('mand_email', $mand->mand_email) }}"
                               required
                               class="mt-1 block w-full rounded-md border-gray-300
                                      shadow-sm text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500
                                      @error('mand_email') border-red-400 @enderror">
                        @error('mand_email')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- mand_tel --}}
                    <div>
                        <label for="mand_tel"
                               class="block text-sm font-medium text-gray-700">
                            Telefon
                        </label>
                        <input id="mand_tel" name="mand_tel" type="text"
                               value="{{ old('mand_tel', $mand->mand_tel) }}"
                               required
                               class="mt-1 block w-full rounded-md border-gray-300
                                      shadow-sm text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500
                                      @error('mand_tel') border-red-400 @enderror">
                        @error('mand_tel')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- mand_firstname / mand_lastname --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        <div>
                            <label for="mand_firstname"
                                   class="block text-sm font-medium text-gray-700">
                                Vorname
                            </label>
                            <input id="mand_firstname" name="mand_firstname" type="text"
                                   value="{{ old('mand_firstname', $mand->mand_firstname) }}"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300
                                          shadow-sm text-sm
                                          focus:border-indigo-500 focus:ring-indigo-500
                                          @error('mand_firstname') border-red-400 @enderror">
                            @error('mand_firstname')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="mand_lastname"
                                   class="block text-sm font-medium text-gray-700">
                                Nachname
                            </label>
                            <input id="mand_lastname" name="mand_lastname" type="text"
                                   value="{{ old('mand_lastname', $mand->mand_lastname) }}"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300
                                          shadow-sm text-sm
                                          focus:border-indigo-500 focus:ring-indigo-500
                                          @error('mand_lastname') border-red-400 @enderror">
                            @error('mand_lastname')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>

                    {{-- mand_street+nr --}}
                    <div>
                        <label for="mand_street_nr"
                               class="block text-sm font-medium text-gray-700">
                            Straße und Hausnummer
                        </label>
                        <input id="mand_street_nr" name="mand_street+nr" type="text"
                               value="{{ old('mand_street+nr', $mand->{'mand_street+nr'}) }}"
                               required
                               class="mt-1 block w-full rounded-md border-gray-300
                                      shadow-sm text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500
                                      @error('mand_street+nr') border-red-400 @enderror">
                        @error('mand_street+nr')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- mand_postcode+city --}}
                    <div>
                        <label for="mand_postcode_city"
                               class="block text-sm font-medium text-gray-700">
                            PLZ und Ort
                        </label>
                        <input id="mand_postcode_city" name="mand_postcode+city" type="text"
                               value="{{ old('mand_postcode+city', $mand->{'mand_postcode+city'}) }}"
                               required
                               class="mt-1 block w-full rounded-md border-gray-300
                                      shadow-sm text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500
                                      @error('mand_postcode+city') border-red-400 @enderror">
                        @error('mand_postcode+city')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- mand_company --}}
                    <div>
                        <label for="mand_company"
                               class="block text-sm font-medium text-gray-700">
                            Firma / Organisation
                        </label>
                        <input id="mand_company" name="mand_company" type="text"
                               value="{{ old('mand_company', $mand->mand_company) }}"
                               required
                               class="mt-1 block w-full rounded-md border-gray-300
                                      shadow-sm text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500
                                      @error('mand_company') border-red-400 @enderror">
                        @error('mand_company')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- mand_2fa_opt_in --}}
                    <div class="pt-1">
                        <div class="flex items-start gap-3">
                            <div class="flex h-5 items-center mt-0.5">
                                <input id="mand_2fa_opt_in" name="mand_2fa_opt_in"
                                       type="checkbox" value="1"
                                       {{ old('mand_2fa_opt_in', $mand->mand_2fa_opt_in) ? 'checked' : '' }}
                                       class="h-4 w-4 rounded border-gray-300
                                              text-indigo-600 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label for="mand_2fa_opt_in"
                                       class="text-sm font-medium text-gray-700 cursor-pointer">
                                    2FA per E-Mail aktivieren
                                </label>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    Deaktivieren nur wenn Passkey registriert
                                </p>
                            </div>
                        </div>
                        @error('mand_2fa_opt_in')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- mand_cust_2fa --}}
                    <div>
                        <label for="mand_cust_2fa"
                               class="block text-sm font-medium text-gray-700">
                            2FA-Schwellwert für Mitglieder
                        </label>
                        <p class="text-xs text-gray-400 mt-0.5 mb-1.5">
                            Ab welcher Sicherheitsstufe müssen sich Ihre Mitglieder mit 2FA anmelden.
                        </p>
                        @php $cur2fa = old('mand_cust_2fa', $mand->mand_cust_2fa); @endphp
                        <select id="mand_cust_2fa" name="mand_cust_2fa"
                                class="mt-1 block w-full rounded-md border-gray-300
                                       shadow-sm text-sm
                                       focus:border-indigo-500 focus:ring-indigo-500
                                       @error('mand_cust_2fa') border-red-400 @enderror">
                            <option value="0" {{ $cur2fa == 0 ? 'selected' : '' }}>Nie — kein 2FA für Mitglieder</option>
                            <option value="1" {{ $cur2fa == 1 ? 'selected' : '' }}>Ab Stufe 1 — Bekannte</option>
                            <option value="2" {{ $cur2fa == 2 ? 'selected' : '' }}>Ab Stufe 2 — Großfamilie</option>
                            <option value="3" {{ $cur2fa == 3 ? 'selected' : '' }}>Ab Stufe 3 — Freunde (Standard)</option>
                            <option value="4" {{ $cur2fa == 4 ? 'selected' : '' }}>Ab Stufe 4 — Enge Freunde &amp; Kernfamilie</option>
                            <option value="5" {{ $cur2fa == 5 ? 'selected' : '' }}>Ab Stufe 5 — Vertraulich</option>
                            <option value="6" {{ $cur2fa == 6 ? 'selected' : '' }}>Ab Stufe 6 — Streng vertraulich</option>
                            <option value="7" {{ $cur2fa == 7 ? 'selected' : '' }}>Immer — alle Mitglieder</option>
                        </select>
                        @error('mand_cust_2fa')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                <div class="mt-6">
                    <button type="submit"
                            class="w-full flex justify-center py-2 px-4 rounded-md
                                   text-sm font-medium text-white bg-indigo-600
                                   hover:bg-indigo-700 transition-colors
                                   focus:outline-none focus:ring-2
                                   focus:ring-indigo-500 focus:ring-offset-2">
                        Kontaktdaten speichern
                    </button>
                </div>

            </form>
        </div>

        {{-- ── Sektion 2: Passwort ändern ──────────────────── --}}

        {{-- Flash: Passwort gespeichert --}}
        @if(session('password_status'))
            <div class="mb-6 rounded-lg border border-indigo-200
                        bg-indigo-50 px-4 py-3 text-sm text-indigo-700">
                {{ session('password_status') }}
            </div>
        @endif

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">

            <h2 class="text-sm font-semibold text-gray-800 tracking-wide mb-5">
                Passwort ändern
            </h2>

            <form method="POST"
                  action="{{ route('mandant.konto.password') }}"
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
                               autocomplete="current-password"
                               class="mt-1 block w-full rounded-md border-gray-300
                                      shadow-sm text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500
                                      @error('current_password') border-red-400 @enderror">
                        @error('current_password')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password"
                               class="block text-sm font-medium text-gray-700">
                            Neues Passwort
                        </label>
                        <input id="password" name="password"
                               type="password" required
                               autocomplete="new-password"
                               class="mt-1 block w-full rounded-md border-gray-300
                                      shadow-sm text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500
                                      @error('password') border-red-400 @enderror">
                        <p class="text-sm text-gray-500 mt-1">Mindestanforderungen: 12 Zeichen, Groß- und Kleinbuchstaben, Ziffern, Sonderzeichen.</p>
                        @error('password')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation"
                               class="block text-sm font-medium text-gray-700">
                            Passwort bestätigen
                        </label>
                        <input id="password_confirmation"
                               name="password_confirmation"
                               type="password" required
                               autocomplete="new-password"
                               class="mt-1 block w-full rounded-md border-gray-300
                                      shadow-sm text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500
                                      @error('password_confirmation') border-red-400 @enderror">
                        @error('password_confirmation')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                <div class="mt-6">
                    <button type="submit"
                            class="w-full flex justify-center py-2 px-4 rounded-md
                                   text-sm font-medium text-white bg-indigo-600
                                   hover:bg-indigo-700 transition-colors
                                   focus:outline-none focus:ring-2
                                   focus:ring-indigo-500 focus:ring-offset-2">
                        Passwort ändern
                    </button>
                </div>

            </form>
        </div>

    </main>

    {{-- ══════════════════════════════════════════════════════
         FOOTER
    ══════════════════════════════════════════════════════ --}}
    <footer class="fixed bottom-0 inset-x-0 border-t border-gray-200 bg-white shadow-sm">
        <div class="mx-auto max-w-3xl px-6 h-9
                    flex items-center justify-between">
            <span class="text-[10px] font-mono tracking-widest
                         uppercase text-gray-400">
                Fotogalerie · Mandanten-Bereich
            </span>
            <span class="text-[10px] text-gray-400">
                Session aktiv
            </span>
        </div>
    </footer>

</body>
</html>
