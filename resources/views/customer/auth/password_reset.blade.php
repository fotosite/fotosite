{{--
    FILE:    resources/views/customer/auth/password_reset.blade.php
    VERSION: 1.0.0
    DATE:    2026-06-12

    DESCRIPTION:
      Neues Passwort setzen für Mitglieder.
      Standalone-Seite, Zugang via Token-Link aus der Reset-Mail.

    DATA FROM CONTROLLER:
      $token (string) — raw token for form action URL

    ROUTES USED:
      POST customer.password.reset.handle — submit new password form
--}}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Neues Passwort setzen · Fotogalerie</title>
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

            <h1 class="text-xl font-semibold tracking-tight text-gray-800 mb-6">
                Neues Passwort setzen
            </h1>

            {{-- Validation errors --}}
            @if($errors->any())
                <div class="mb-6 rounded-lg border border-red-300
                            bg-red-50 px-4 py-3 text-sm text-red-700 space-y-1">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST"
                  action="{{ route('customer.password.reset.handle', ['token' => $token]) }}"
                  autocomplete="off">
                @csrf

                <div class="space-y-4">

                    <div>
                        <label for="password"
                               class="block text-sm font-medium text-gray-700 mb-1">
                            Neues Passwort
                        </label>
                        <input id="password" name="password" type="password"
                               required autofocus
                               class="w-full rounded-lg border border-gray-300 px-3 py-2.5 md:py-2 text-sm text-gray-800 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <p class="mt-1 text-xs text-gray-500">Mindestanforderungen: 10 Zeichen.</p>
                    </div>

                    <div>
                        <label for="password_confirmation"
                               class="block text-sm font-medium text-gray-700 mb-1">
                            Passwort bestätigen
                        </label>
                        <input id="password_confirmation" name="password_confirmation"
                               type="password" required
                               class="w-full rounded-lg border border-gray-300 px-3 py-2.5 md:py-2 text-sm text-gray-800 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>

                </div>

                <div class="mt-6">
                    <button type="submit"
                            class="w-full flex justify-center rounded-lg bg-indigo-600 px-4 py-3 md:py-2 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Passwort setzen
                    </button>
                </div>

            </form>
        </div>
    </div>

</body>
</html>
