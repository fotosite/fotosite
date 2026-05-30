{{--
    FILE:    resources/views/customer/auth/register.blade.php
    VERSION: 1.0.0
    AUTHOR:  Martin Wagner
    DATE:    2026-05-30

    DESCRIPTION:
      Kunden-Registrierungsformular — wird per Einladungs-Token aufgerufen.
      E-Mail ist durch den Einladungslink vorausgefüllt und schreibgeschützt.

    DATA FROM CONTROLLER:
      $token      — Einladungs-Token (string)
      $cust_email — E-Mail-Adresse aus CustInvite (vorausgefüllt, readonly)

    ROUTES USED:
      POST /customer/register/{token} — Registrierung abschicken (route('customer.register.store'))
      GET  /                          — Startseite (route('home'))
--}}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Registrierung · Fotosite V8</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-100 font-sans antialiased">

<div class="min-h-screen flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-lg bg-white rounded-xl shadow-md px-8 py-8">

        {{-- Kopfzeile --}}
        <div class="mb-7">
            <p class="text-[11px] font-mono tracking-widest uppercase text-gray-400 mb-1">
                Fotosite&thinsp;V8
            </p>
            <h1 class="text-xl font-semibold text-gray-800">
                Konto erstellen
            </h1>
            <p class="mt-1 text-sm text-gray-500">
                Füllen Sie das Formular aus, um Ihren Zugang zu aktivieren.
            </p>
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

        <form method="POST"
              action="{{ route('customer.register.store', ['token' => $token]) }}"
              autocomplete="off">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="space-y-5">

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
                </div>

                {{-- Telefon --}}
                <div>
                    <label for="cust_tel"
                           class="block text-sm font-medium text-gray-700 mb-1">
                        Telefon <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="cust_tel" name="cust_tel"
                           value="{{ old('cust_tel') }}"
                           required
                           class="w-full rounded-lg border px-3 py-2 text-sm text-gray-800 shadow-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-400
                                  @error('cust_tel') border-red-400 bg-red-50 @else border-gray-300 @enderror">
                    @error('cust_tel')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Firma (optional) --}}
                <div>
                    <label for="cust_company"
                           class="block text-sm font-medium text-gray-700 mb-1">
                        Firma
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
                           class="w-full rounded-lg border px-3 py-2 text-sm text-gray-800 shadow-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-400
                                  @error('password_confirmation') border-red-400 bg-red-50 @else border-gray-300 @enderror">
                    @error('password_confirmation')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

            </div>{{-- /space-y-5 --}}

            {{-- Submit --}}
            <div class="mt-7">
                <button type="submit"
                        class="w-full flex justify-center rounded-lg
                               bg-indigo-600 px-4 py-2.5 text-sm font-medium
                               text-white hover:bg-indigo-700
                               transition-colors duration-150
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Konto erstellen
                </button>
            </div>

        </form>

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
