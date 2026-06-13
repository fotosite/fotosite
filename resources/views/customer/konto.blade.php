{{--
    FILE:    resources/views/customer/konto.blade.php
    VERSION: 1.0.0
    DATE:    2026-06-12

    DESCRIPTION:
      Customer Eigenverwaltung — Kontaktdaten und Passwort bearbeiten.
      Standalone (kein Layout-Erbe), gleiches Strukturmuster wie customer/dashboard.
      Accent-Farbe: indigo.

    DATA FROM CONTROLLER:
      $cust (CustUser) — vollständige Instanz

    ROUTES USED:
      GET   customer.dashboard      — Zurück-Link
      PATCH customer.konto.update   — Kontaktdaten speichern
      PATCH customer.konto.password — Passwort ändern
      POST  customer.logout         — Abmelden
--}}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Mein Konto · Fotogalerie</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-50 text-gray-900 antialiased">

    @php $custName = \App\Models\UserDb\CustUser::find(session('_cust_id'))?->cust_firstname ?? ''; @endphp

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
                    Mitglied
                </span>
                <span class="text-sm text-indigo-200">{{ $custName }}</span>
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
    <main class="mx-auto max-w-3xl px-6 pt-10 pb-24">

        {{-- Zurück-Link --}}
        <div class="mt-4 mb-6">
            <a href="{{ route('customer.dashboard') }}"
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
                Mein Konto
            </h1>
            <p class="mt-1.5 text-sm text-zinc-600">
                Kontaktdaten und Passwort verwalten.
            </p>
        </div>

        {{-- ── Card 1: Kontaktdaten ────────────────────────── --}}

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
                  action="{{ route('customer.konto.update') }}"
                  autocomplete="off">
                @csrf
                @method('PATCH')

                <div class="space-y-4">

                    {{-- cust_email (Pflicht) --}}
                    <div>
                        <label for="cust_email"
                               class="block text-sm font-medium text-gray-700">
                            E-Mail <span class="text-red-500">*</span>
                        </label>
                        <input id="cust_email" name="cust_email" type="email"
                               value="{{ old('cust_email', $cust->cust_email) }}"
                               required
                               class="mt-1 block w-full rounded-md border-gray-300
                                      shadow-sm text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500
                                      @error('cust_email') border-red-400 @enderror">
                        @error('cust_email')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- cust_firstname / cust_lastname --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        <div>
                            <label for="cust_firstname"
                                   class="block text-sm font-medium text-gray-700">
                                Vorname
                                <span class="text-gray-400 font-normal">(optional)</span>
                            </label>
                            <input id="cust_firstname" name="cust_firstname" type="text"
                                   value="{{ old('cust_firstname', $cust->cust_firstname) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300
                                          shadow-sm text-sm
                                          focus:border-indigo-500 focus:ring-indigo-500
                                          @error('cust_firstname') border-red-400 @enderror">
                            @error('cust_firstname')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="cust_lastname"
                                   class="block text-sm font-medium text-gray-700">
                                Nachname
                                <span class="text-gray-400 font-normal">(optional)</span>
                            </label>
                            <input id="cust_lastname" name="cust_lastname" type="text"
                                   value="{{ old('cust_lastname', $cust->cust_lastname) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300
                                          shadow-sm text-sm
                                          focus:border-indigo-500 focus:ring-indigo-500
                                          @error('cust_lastname') border-red-400 @enderror">
                            @error('cust_lastname')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>

                    {{-- cust_tel --}}
                    <div>
                        <label for="cust_tel"
                               class="block text-sm font-medium text-gray-700">
                            Telefon
                            <span class="text-gray-400 font-normal">(optional)</span>
                        </label>
                        <input id="cust_tel" name="cust_tel" type="text"
                               value="{{ old('cust_tel', $cust->cust_tel) }}"
                               class="mt-1 block w-full rounded-md border-gray-300
                                      shadow-sm text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500
                                      @error('cust_tel') border-red-400 @enderror">
                        @error('cust_tel')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- cust_street+nr --}}
                    <div>
                        <label for="cust_street_nr"
                               class="block text-sm font-medium text-gray-700">
                            Straße und Hausnummer
                            <span class="text-gray-400 font-normal">(optional)</span>
                        </label>
                        <input id="cust_street_nr" name="cust_street+nr" type="text"
                               value="{{ old('cust_street+nr', $cust->{'cust_street+nr'}) }}"
                               class="mt-1 block w-full rounded-md border-gray-300
                                      shadow-sm text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500
                                      @error('cust_street+nr') border-red-400 @enderror">
                        @error('cust_street+nr')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- cust_postcode_city --}}
                    <div>
                        <label for="cust_postcode_city"
                               class="block text-sm font-medium text-gray-700">
                            PLZ und Ort
                            <span class="text-gray-400 font-normal">(optional)</span>
                        </label>
                        <input id="cust_postcode_city" name="cust_postcode_city" type="text"
                               value="{{ old('cust_postcode_city', $cust->cust_postcode_city) }}"
                               class="mt-1 block w-full rounded-md border-gray-300
                                      shadow-sm text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500
                                      @error('cust_postcode_city') border-red-400 @enderror">
                        @error('cust_postcode_city')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- cust_company --}}
                    <div>
                        <label for="cust_company"
                               class="block text-sm font-medium text-gray-700">
                            Firma / Organisation
                            <span class="text-gray-400 font-normal">(optional)</span>
                        </label>
                        <input id="cust_company" name="cust_company" type="text"
                               value="{{ old('cust_company', $cust->cust_company) }}"
                               class="mt-1 block w-full rounded-md border-gray-300
                                      shadow-sm text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500
                                      @error('cust_company') border-red-400 @enderror">
                        @error('cust_company')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                <div class="mt-6">
                    <button type="submit"
                            class="w-full flex justify-center py-3 md:py-2 px-4
                                   rounded-md text-sm font-medium text-white
                                   bg-indigo-600 hover:bg-indigo-700 transition-colors
                                   focus:outline-none focus:ring-2
                                   focus:ring-indigo-500 focus:ring-offset-2">
                        Kontaktdaten speichern
                    </button>
                </div>

            </form>
        </div>

        {{-- ── Card 2: Passwort ändern ─────────────────────── --}}

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">

            <h2 class="text-sm font-semibold text-gray-800 tracking-wide mb-5">
                Passwort ändern
            </h2>

            <form method="POST"
                  action="{{ route('customer.konto.password') }}"
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
                        <p class="text-xs text-gray-400 mt-1">
                            Mindestens 10 Zeichen.
                        </p>
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
                            class="w-full flex justify-center py-3 md:py-2 px-4
                                   rounded-md text-sm font-medium text-white
                                   bg-indigo-600 hover:bg-indigo-700 transition-colors
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
                Fotogalerie · Mitglieder-Bereich
            </span>
            <span class="text-[10px] text-gray-400">
                Session aktiv
            </span>
        </div>
    </footer>

</body>
</html>
