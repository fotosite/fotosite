{{--
    FILE:    resources/views/system/users/register.blade.php
    VERSION: 1.0.0

    DESCRIPTION:
      Standalone registration page for invited system users.
      No session header (user is not yet logged in).
      Light theme matching dashboard.blade.php.

    DATA FROM CONTROLLER:
      $invite (Invite) — invite record with inv_email
      $token  (string) — raw token for form action URL

    ROUTES USED:
      POST system.register.handle — submit registration form
--}}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>System-Account erstellen · Fotosite V8</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-50 text-gray-900 antialiased">

    <div class="min-h-screen flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-md bg-white rounded-xl border border-gray-200
                    shadow-sm px-8 py-8">

            {{-- Brand --}}
            <div class="flex items-center gap-3 mb-8">
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

            <h1 class="text-xl font-semibold tracking-tight text-gray-800 mb-6">
                System-Account erstellen
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
                  action="{{ route('system.register.handle', ['token' => $token]) }}"
                  autocomplete="off">
                @csrf

                <div class="space-y-4">

                    {{-- Email (read-only) --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            E-Mail
                        </label>
                        <input type="email"
                               value="{{ $invite->inv_email }}"
                               disabled readonly
                               class="mt-1 block w-full rounded-md border-gray-200
                                      bg-gray-50 text-gray-500 shadow-sm text-sm
                                      cursor-not-allowed">
                    </div>

                    <div>
                        <label for="syst_uname"
                               class="block text-sm font-medium text-gray-700">
                            Benutzername
                        </label>
                        <input id="syst_uname" name="syst_uname" type="text"
                               value="{{ old('syst_uname') }}"
                               required autofocus
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
                               value="{{ old('syst_firstname') }}"
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
                               value="{{ old('syst_lastname') }}"
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
                               value="{{ old('syst_tel') }}"
                               required
                               class="mt-1 block w-full rounded-md border-gray-300
                                      shadow-sm text-sm
                                      focus:border-gray-500 focus:ring-gray-500">
                    </div>

                    <div>
                        <label for="password"
                               class="block text-sm font-medium text-gray-700">
                            Passwort
                        </label>
                        <input id="password" name="password" type="password"
                               required
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
                               name="password_confirmation" type="password"
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
                        Account erstellen
                    </button>
                </div>

            </form>
        </div>
    </div>

</body>
</html>
