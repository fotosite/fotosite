{{--
    FILE:    resources/views/customer/auth/password_reset_request.blade.php
    VERSION: 1.1.2
    DATE:    2026-06-22

    DESCRIPTION:
      Passwort-Zurücksetzen-Anfrage für Mitglieder.
      Standalone-Seite (kein Nav), zeigt E-Mail-Eingabe.
      Gibt neutrales Feedback — keine Enumeration möglich.

    DATA FROM CONTROLLER:
      (keine) — nutzt session('status') für Bestätigungsmeldung

    ROUTES USED:
      POST customer.password.reset.send — Formular absenden
--}}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Passwort zurücksetzen · Fotogalerie</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-50 text-gray-900 antialiased">

    <div class="min-h-screen flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-sm bg-white rounded-xl border border-gray-200
                    shadow-sm px-8 py-8">

            {{-- Brand --}}
            <div class="mb-8">
                <span class="text-[11px] font-mono tracking-widest uppercase text-gray-400">
                    Fotogalerie
                </span>
            </div>

            <h1 class="text-xl font-semibold tracking-tight text-gray-800 mb-2">
                Passwort zurücksetzen
            </h1>
            <p class="text-sm text-gray-500 mb-6 leading-relaxed">
                Gib deine E-Mail-Adresse ein. Falls ein Mitglieds-Konto existiert,
                senden wir dir einen Link zum Zurücksetzen.
            </p>

            {{-- Status message --}}
            @if(session('status'))
                <div class="mb-6 rounded-lg border border-green-300
                            bg-green-50 px-4 py-3 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Validation errors --}}
            @if($errors->any())
                <div class="mb-6 rounded-lg border border-red-300
                            bg-red-50 px-4 py-3 text-sm text-red-700 space-y-1">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('customer.password.reset.send') }}" autocomplete="off">
                @csrf

                <div x-data="{ dirty: false }">
                    <label for="cust_email"
                           class="block text-sm font-medium text-gray-700 mb-1">
                        E-Mail
                    </label>
                    <input id="cust_email" name="cust_email" type="email"
                           required autofocus
                           value="{{ old('cust_email') }}"
                           placeholder="ihre@email.de"
                           autocomplete="email"
                           @input="dirty = true"
                           class="w-full rounded-lg border px-3 py-2.5 md:py-2 text-sm text-gray-800 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 @error('cust_email') border-red-400 bg-red-50 @else border-gray-300 @enderror">
                    @error('cust_email')
                        <p class="mt-1 text-xs text-red-600" x-show="!dirty">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-5">
                    <button type="submit"
                            class="w-full flex justify-center rounded-lg bg-indigo-600 px-4 py-3 md:py-2 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Link senden
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center" x-data="{}">
                <button type="button"
                        @click="window.location='{{ route('home') }}'"
                        class="text-sm text-indigo-600 hover:underline select-none">
                    Zurück zur Anmeldung
                </button>
            </div>

        </div>
    </div>

</body>
</html>
