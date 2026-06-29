{{--
    FILE:    resources/views/customer/auth/two-factor.blade.php
    VERSION: 1.1.3
    AUTHOR:  Martin Wagner
    DATE:    2026-06-08

    DESCRIPTION:
      Cust-Login 2FA — 6-stelliger Bestätigungscode-Eingabe.
      Standalone (kein Layout-Erbe). Accent-Farbe: indigo.

    ROUTES USED:
      POST customer.login.2fa.verify — Code prüfen
      GET  customer.login            — Zurück zum Login
--}}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Bestätigungscode · Fotogalerie</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-100 font-sans antialiased">

<div class="min-h-screen flex items-center justify-center px-4">
    <div x-data="{}" class="w-full max-w-sm bg-white rounded-lg shadow-md px-8 py-8">

        <p class="text-[11px] font-mono tracking-widest uppercase text-gray-400 mb-1">
            Fotogalerie
        </p>
        <h1 class="text-xl font-semibold text-gray-800 mb-6">Bestätigungscode</h1>

        @if ($errors->any())
            <div class="mb-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <p class="text-sm text-red-600">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <p class="text-sm text-gray-600 mb-5">
            Bitte geben Sie den 6-stelligen Code ein, den wir an Ihre
            E-Mail-Adresse gesendet haben. Der Code ist 2 Minuten gültig.
        </p>

        <form method="POST"
              action="{{ route('customer.login.2fa.verify') }}"
              autocomplete="off">
            @csrf

            <div>
                <label for="tfa_code"
                       class="block text-sm font-medium text-gray-700">
                    Bestätigungscode
                </label>
                <input id="tfa_code" name="tfa_code" type="text"
                       inputmode="numeric" pattern="[0-9]{6}" maxlength="6"
                       required autofocus autocomplete="one-time-code"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm
                              text-center text-xl tracking-widest font-mono
                              focus:border-indigo-500 focus:ring-indigo-500
                              @error('tfa_code') border-red-400 @enderror">
                @error('tfa_code')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6">
                <button type="submit"
                        class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold
                               text-white hover:bg-indigo-700 focus:outline-none focus:ring-2
                               focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
                    Bestätigen
                </button>
            </div>
        </form>

        <div class="mt-5 text-center">
            <a href="{{ route('customer.login') }}"
               class="text-sm text-gray-500 hover:text-indigo-600 transition-colors">
                ← Zurück zum Login
            </a>
        </div>

    </div>
</div>

</body>
</html>
