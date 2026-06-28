{{--
    FILE:    resources/views/system/users/password_reset.blade.php
    VERSION: 1.2.0
    DATE:    2026-06-08

    DESCRIPTION:
      Standalone password-reset page for system users.
      No session header (user is not yet logged in).
      Light theme matching dashboard.blade.php.

    DATA FROM CONTROLLER:
      $token (string) — raw token for form action URL

    ROUTES USED:
      POST system.password.reset.handle — submit new password form
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
            <div class="flex items-center gap-3 mb-8">
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

            <h1 class="text-xl font-semibold tracking-tight text-gray-800 mb-6">
                Neues Passwort setzen
            </h1>

            {{-- Error notice --}}
            @if($errors->any())
                <div class="mb-6 rounded-lg border border-red-300
                            bg-red-50 px-4 py-3 text-sm text-red-700 space-y-1">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST"
                  action="{{ route('system.password.reset.handle', ['token' => $token]) }}"
                  autocomplete="off">
                @csrf

                <div class="space-y-4">

                    <div>
                        <label for="password"
                               class="block text-sm font-medium text-gray-700">
                            Neues Passwort
                        </label>
                        <div class="relative mt-1" x-data="{ show: false }">
                            <input id="password" name="password" :type="show ? 'text' : 'password'"
                                   required autofocus
                                   class="block w-full rounded-md border-gray-300
                                          shadow-sm text-sm pr-10
                                          focus:border-gray-500 focus:ring-gray-500">
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
                        <p class="text-sm text-gray-500 mt-1">Mindestanforderungen: 14 Zeichen, Groß- und Kleinbuchstaben, Ziffern, Sonderzeichen.</p>
                    </div>

                    <div>
                        <label for="password_confirmation"
                               class="block text-sm font-medium text-gray-700">
                            Passwort bestätigen
                        </label>
                        <div class="relative mt-1" x-data="{ show: false }">
                            <input id="password_confirmation"
                                   name="password_confirmation" :type="show ? 'text' : 'password'"
                                   required
                                   class="block w-full rounded-md border-gray-300
                                          shadow-sm text-sm pr-10
                                          focus:border-gray-500 focus:ring-gray-500">
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

                <div class="mt-6">
                    <button type="submit"
                            class="w-full flex justify-center py-2 px-4 rounded-md
                                   text-sm font-medium text-white bg-gray-800
                                   hover:bg-gray-700 transition-colors active:opacity-75 active:scale-95 transition-all duration-75 select-none
                                   focus:outline-none focus:ring-2
                                   focus:ring-gray-500 focus:ring-offset-2">
                        Passwort setzen
                    </button>
                </div>

            </form>
        </div>
    </div>

</body>
</html>
