{{--
    FILE:    resources/views/customer/auth/register.blade.php
    VERSION: 1.8.0
    AUTHOR:  Martin Wagner
    DATE:    2026-06-22

    DESCRIPTION:
      Mitglieder-Registrierungsformular — wird per Einladungs-Token aufgerufen.
      Zwei Modi:
        $alreadyExists = true  → Kompaktform für bereits registrierte User
        $alreadyExists = false → Vollständiges Registrierungsformular

    DATA FROM CONTROLLER:
      $token         — Einladungs-Token (string)
      $cust_email    — E-Mail-Adresse aus CustInvite (vorausgefüllt / readonly)
      $alreadyExists — bool: true wenn cust_email bereits in cust_user vorhanden

    ROUTES USED:
      POST /customer/register/{token} — Registrierung abschicken (route('customer.register.store'))
      GET  /                          — Startseite (route('home'))

    CHANGES: 1.6.0 (2026-06-18) cust_uname-Feld ergänzt (Pflicht); cust_tel auf
             optional umgestellt; cust_company-Label vereinheitlicht ("Firma /
             Organisation"); Change-Tracking via Alpine dirty-Flag + beforeunload
             ergänzt (Passwortfelder von dirty-Tracking ausgenommen).
             1.7.0 (2026-06-22) Begleittext zur E-Mail-Adresse ergänzt (Hinweis
             auf 2FA-Codes und Passwort-Erneuerung).
             1.8.0 (2026-06-22) Begleittext zur E-Mail-Adresse durch Hinweis auf
             Voreinstellung ersetzt (Feld ist read-only).
--}}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Mitglieder-Konto erstellen · Fotogalerie</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-100 font-sans antialiased">

<div class="min-h-screen flex items-center justify-center px-4 py-12">
    <div class="w-full bg-white rounded-xl shadow-md px-8 py-8
                {{ $alreadyExists ? 'max-w-sm' : 'max-w-lg' }}">

        {{-- Kopfzeile --}}
        <div class="mb-7">
            <p class="text-[11px] font-mono tracking-widest uppercase text-gray-400 mb-1">
                Fotogalerie
            </p>
            <h1 class="text-xl font-semibold text-gray-800">
                {{ $alreadyExists ? 'Einladung annehmen' : 'Mitglieder-Konto erstellen' }}
            </h1>
        </div>

        {{-- Flash-Meldung --}}
        @if(session('status'))
            <div class="mb-6 rounded-lg border border-indigo-200
                        bg-indigo-50 px-4 py-3 text-sm text-indigo-700">
                {{ session('status') }}
            </div>
        @endif

        {{-- Fehlerübersicht --}}
        @if($errors->any())
            <div class="mb-6 rounded-lg border border-red-200
                        bg-red-50 px-4 py-3 text-sm text-red-700 space-y-1">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        @if($alreadyExists)

        {{-- ── Kompaktform: bereits registrierter User ──────── --}}
        <div class="mb-6 space-y-2 text-sm text-gray-600">
            <p>Sie wurden von einem Galeristen eingeladen.</p>
            <p>
                Ihr Account ist bereits registriert:
                <span class="font-medium text-gray-800">{{ $cust_email }}</span>
            </p>
            <p class="text-gray-400 text-xs">
                Mit einem Klick auf "Einladung annehmen" wird Ihr Zugang für diesen
                Galeristen freigeschaltet.
            </p>
        </div>

        <form method="POST"
              action="{{ route('customer.register.store', ['token' => $token]) }}">
            @csrf
            <input type="hidden" name="token"      value="{{ $token }}">
            <input type="hidden" name="cust_email" value="{{ $cust_email }}">
            <input type="hidden" name="existing"   value="1">

            <button type="submit"
                    class="w-full flex justify-center rounded-lg
                           bg-indigo-600 px-4 py-2.5 text-sm font-medium
                           text-white hover:bg-indigo-700
                           transition-colors duration-150
                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Einladung annehmen
            </button>
        </form>

        @else

        {{-- ── Vollständiges Registrierungsformular ─────────── --}}
        <p class="mb-6 text-sm text-gray-500">
            Füllen Sie das Formular aus, um Ihren Zugang zu aktivieren.
        </p>

        <form method="POST"
              action="{{ route('customer.register.store', ['token' => $token]) }}"
              autocomplete="off"
              x-data="{ dirty: false }"
              x-init="window.addEventListener('beforeunload', (e) => { if (dirty) { e.preventDefault(); e.returnValue = ''; } })"
              @input="dirty = true"
              @change="dirty = true"
              @submit="dirty = false">
            @csrf
            <input type="hidden" name="token"    value="{{ $token }}">
            <input type="hidden" name="existing" value="0">

            <div class="space-y-5">

                {{-- Benutzername --}}
                <div>
                    <label for="cust_uname"
                           class="block text-sm font-medium text-gray-700 mb-1">
                        Benutzername <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="cust_uname" name="cust_uname"
                           value="{{ old('cust_uname') }}"
                           required
                           class="w-full rounded-lg border px-3 py-2 text-sm text-gray-800 shadow-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-400
                                  @error('cust_uname') border-red-400 bg-red-50 @else border-gray-300 @enderror">
                    @error('cust_uname')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Vorname / Nachname --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="cust_firstname"
                               class="block text-sm font-medium text-gray-700 mb-1">
                            Vorname <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               id="cust_firstname" name="cust_firstname"
                               value="{{ old('cust_firstname') }}"
                               required
                               class="w-full rounded-lg border px-3 py-2 text-sm text-gray-800 shadow-sm
                                      focus:outline-none focus:ring-2 focus:ring-indigo-400
                                      @error('cust_firstname') border-red-400 bg-red-50 @else border-gray-300 @enderror">
                        @error('cust_firstname')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="cust_lastname"
                               class="block text-sm font-medium text-gray-700 mb-1">
                            Nachname <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               id="cust_lastname" name="cust_lastname"
                               value="{{ old('cust_lastname') }}"
                               required
                               class="w-full rounded-lg border px-3 py-2 text-sm text-gray-800 shadow-sm
                                      focus:outline-none focus:ring-2 focus:ring-indigo-400
                                      @error('cust_lastname') border-red-400 bg-red-50 @else border-gray-300 @enderror">
                        @error('cust_lastname')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- E-Mail (readonly) --}}
                <div>
                    <label for="cust_email"
                           class="block text-sm font-medium text-gray-700 mb-1">
                        E-Mail-Adresse
                    </label>
                    <input type="email"
                           id="cust_email" name="cust_email"
                           value="{{ $cust_email }}"
                           readonly
                           class="w-full rounded-lg border border-gray-200 bg-gray-50
                                  px-3 py-2 text-sm text-gray-500 shadow-sm cursor-not-allowed">
                    @error('cust_email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-600">Diese E-Mail-Adresse ist eine Voreinstellung, die später geändert werden kann.</p>
                </div>

                {{-- Telefon (optional) --}}
                <div>
                    <label for="cust_tel"
                           class="block text-sm font-medium text-gray-700 mb-1">
                        Telefon
                        <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                    <input type="text"
                           id="cust_tel" name="cust_tel"
                           value="{{ old('cust_tel') }}"
                           class="w-full rounded-lg border px-3 py-2 text-sm text-gray-800 shadow-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-400
                                  @error('cust_tel') border-red-400 bg-red-50 @else border-gray-300 @enderror">
                    @error('cust_tel')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Firma / Organisation (optional) --}}
                <div>
                    <label for="cust_company"
                           class="block text-sm font-medium text-gray-700 mb-1">
                        Firma / Organisation
                        <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                    <input type="text"
                           id="cust_company" name="cust_company"
                           value="{{ old('cust_company') }}"
                           class="w-full rounded-lg border px-3 py-2 text-sm text-gray-800 shadow-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-400
                                  @error('cust_company') border-red-400 bg-red-50 @else border-gray-300 @enderror">
                    @error('cust_company')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Straße + Nr --}}
                <div>
                    <label for="cust_street_nr"
                           class="block text-sm font-medium text-gray-700 mb-1">
                        Straße und Hausnummer <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="cust_street_nr" name="cust_street+nr"
                           value="{{ old('cust_street+nr') }}"
                           required
                           class="w-full rounded-lg border px-3 py-2 text-sm text-gray-800 shadow-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-400
                                  @error('cust_street+nr') border-red-400 bg-red-50 @else border-gray-300 @enderror">
                    @error('cust_street+nr')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- PLZ + Ort --}}
                <div>
                    <label for="cust_postcode_city"
                           class="block text-sm font-medium text-gray-700 mb-1">
                        PLZ und Ort <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="cust_postcode_city" name="cust_postcode_city"
                           value="{{ old('cust_postcode_city') }}"
                           required
                           class="w-full rounded-lg border px-3 py-2 text-sm text-gray-800 shadow-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-400
                                  @error('cust_postcode_city') border-red-400 bg-red-50 @else border-gray-300 @enderror">
                    @error('cust_postcode_city')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Passwort --}}
                <div>
                    <label for="password"
                           class="block text-sm font-medium text-gray-700 mb-1">
                        Passwort <span class="text-red-500">*</span>
                    </label>
                    <input type="password"
                           id="password" name="password"
                           required autocomplete="new-password"
                           @input.stop="" @change.stop=""
                           class="w-full rounded-lg border px-3 py-2 text-sm text-gray-800 shadow-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-400
                                  @error('password') border-red-400 bg-red-50 @else border-gray-300 @enderror">
                    @error('password')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1.5 text-xs text-gray-400">
                        Mindestanforderungen: 10 Zeichen, Groß- und Kleinbuchstaben, Ziffern.
                    </p>
                </div>

                {{-- Passwort bestätigen --}}
                <div>
                    <label for="password_confirmation"
                           class="block text-sm font-medium text-gray-700 mb-1">
                        Passwort bestätigen <span class="text-red-500">*</span>
                    </label>
                    <input type="password"
                           id="password_confirmation" name="password_confirmation"
                           required autocomplete="new-password"
                           @input.stop="" @change.stop=""
                           class="w-full rounded-lg border px-3 py-2 text-sm text-gray-800 shadow-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-400
                                  @error('password_confirmation') border-red-400 bg-red-50 @else border-gray-300 @enderror">
                    @error('password_confirmation')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

            </div>{{-- /space-y-5 --}}

            {{-- Datenschutz-Checkbox --}}
            <div class="mt-5">
                <label class="flex items-start gap-3 cursor-pointer group">
                    <input type="checkbox"
                           id="ds_accepted" name="ds_accepted"
                           value="1"
                           class="mt-0.5 h-4 w-4 rounded border-gray-300 text-indigo-600
                                  focus:ring-indigo-400
                                  @error('ds_accepted') border-red-400 @enderror">
                    <span class="text-sm text-gray-600 leading-snug">
                        Ich habe die
                        <a href="{{ route('customer.datenschutz.erlaeuterung') }}"
                           target="_blank"
                           class="text-indigo-600 hover:underline">
                            Datenschutzerklärung
                        </a>
                        gelesen und stimme zu.
                        <span class="text-red-500">*</span>
                    </span>
                </label>
                @error('ds_accepted')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Submit --}}
            <div class="mt-7">
                <button type="submit"
                        class="w-full flex justify-center rounded-lg
                               bg-indigo-600 px-4 py-2.5 text-sm font-medium
                               text-white hover:bg-indigo-700
                               transition-colors duration-150
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Mitglieder-Konto erstellen
                </button>
            </div>

        </form>

        @endif

        {{-- Zurück --}}
        <div class="mt-6 text-center">
            <a href="{{ route('home') }}"
               class="text-sm text-gray-400 hover:text-indigo-600
                      transition-colors duration-150">
                ← Zurück zur Startseite
            </a>
        </div>

    </div>
</div>

</body>
</html>
