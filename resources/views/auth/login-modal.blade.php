<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Anmelden</title>
    <style>[x-cloak]{display:none!important}</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-900 flex items-center justify-center px-4">

    {{--
        Alpine-State wird aus old()-Werten vorbelegt, damit nach einem
        Formular-Submit mit Validierungsfehler die richtige Seite / der
        richtige Tab direkt sichtbar ist (kein Zurückspringen zum Default).
    --}}
    <div x-data="{ page: '{{ old('_form', 'cust') }}', custTab: '{{ old('_tab', 'anon') }}' }"
         class="w-full max-w-md bg-white rounded-2xl shadow-2xl px-8 py-10">

        {{-- Flash: abgelaufene Session --}}
        @if (session('session'))
            <div class="mb-5 rounded-lg bg-orange-50 border border-orange-200 px-4 py-3 text-sm text-orange-800">
                {{ session('session') }}
            </div>
        @endif

        {{-- Flash: allgemeine Fehlermeldung (Middleware, Redirect->with('error')) --}}
        @if (session('error'))
            <div class="mb-5 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif


        {{-- ═══════════════════════════════════════════════════════════
             SEITE 1 — Customer Login
        ═══════════════════════════════════════════════════════════ --}}
        <div x-show="page === 'cust'" x-cloak>

            <h2 class="text-xl font-semibold text-gray-800 mb-5">Anmelden</h2>

            {{-- Tab Bar --}}
            <div class="flex border-b border-gray-200 mb-6">
                <button type="button"
                        @click="custTab = 'anon'"
                        :class="custTab === 'anon'
                            ? 'border-b-2 border-indigo-600 text-indigo-600'
                            : 'text-gray-400 hover:text-gray-600'"
                        class="mr-6 pb-2 text-sm font-medium transition-colors">
                    Anonym
                </button>
                <button type="button"
                        @click="custTab = 'reg'"
                        :class="custTab === 'reg'
                            ? 'border-b-2 border-indigo-600 text-indigo-600'
                            : 'text-gray-400 hover:text-gray-600'"
                        class="pb-2 text-sm font-medium transition-colors">
                    Registriert
                </button>
            </div>


            {{-- Tab: Anonym --}}
            <div x-show="custTab === 'anon'" x-cloak>
                <form method="POST" action="/customer/login/anon">
                    @csrf
                    <input type="hidden" name="_form" value="cust">
                    <input type="hidden" name="_tab"  value="anon">

                    @error('pw_code')
                        <div class="mb-3 text-sm text-red-600">{{ $message }}</div>
                    @enderror

                    <div>
                        <label for="pw_code"
                               class="block text-sm font-medium text-gray-700 mb-1">
                            Passwort
                        </label>
                        <input id="pw_code"
                               type="password"
                               name="pw_code"
                               class="block w-full rounded-lg border-gray-300 shadow-sm
                                      focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                               required autofocus>
                    </div>

                    <div class="mt-5">
                        <button type="submit"
                                class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm
                                       font-semibold text-white hover:bg-indigo-700
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500
                                       focus:ring-offset-2 transition-colors">
                            Anmelden
                        </button>
                    </div>
                </form>
            </div>


            {{-- Tab: Registriert --}}
            <div x-show="custTab === 'reg'" x-cloak>
                <form method="POST" action="/customer/login">
                    @csrf
                    <input type="hidden" name="_form" value="cust">
                    <input type="hidden" name="_tab"  value="reg">

                    @error('username')
                        <div class="mb-3 text-sm text-red-600">{{ $message }}</div>
                    @enderror
                    @error('password')
                        <div class="mb-3 text-sm text-red-600">{{ $message }}</div>
                    @enderror
                    @error('credentials')
                        <div class="mb-3 text-sm text-red-600">{{ $message }}</div>
                    @enderror

                    <div>
                        <label for="cust_uname"
                               class="block text-sm font-medium text-gray-700 mb-1">
                            Benutzername
                        </label>
                        <input id="cust_uname"
                               type="text"
                               name="username"
                               value="{{ old('username') }}"
                               class="block w-full rounded-lg border-gray-300 shadow-sm
                                      focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                               required>
                    </div>

                    <div class="mt-4">
                        <label for="cust_password"
                               class="block text-sm font-medium text-gray-700 mb-1">
                            Passwort
                        </label>
                        <input id="cust_password"
                               type="password"
                               name="password"
                               class="block w-full rounded-lg border-gray-300 shadow-sm
                                      focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                               required>
                    </div>

                    <div class="mt-5">
                        <button type="submit"
                                class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm
                                       font-semibold text-white hover:bg-indigo-700
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500
                                       focus:ring-offset-2 transition-colors">
                            Anmelden
                        </button>
                    </div>
                </form>
            </div>


            {{-- Link: Mandanten-Login --}}
            <div class="mt-7 text-center">
                <button type="button"
                        @click="page = 'mand'"
                        class="text-xs text-gray-400 hover:text-gray-500 transition-colors">
                    Mandanten-Login
                </button>
            </div>

        </div>


        {{-- ═══════════════════════════════════════════════════════════
             SEITE 2 — Mandant Login
        ═══════════════════════════════════════════════════════════ --}}
        <div x-show="page === 'mand'" x-cloak>

            <h2 class="text-xl font-semibold text-gray-800 mb-6">Mandanten-Anmeldung</h2>

            <form method="POST" action="/mandant/login">
                @csrf
                <input type="hidden" name="_form" value="mand">

                @error('email')
                    <div class="mb-3 text-sm text-red-600">{{ $message }}</div>
                @enderror
                @error('password')
                    <div class="mb-3 text-sm text-red-600">{{ $message }}</div>
                @enderror
                @error('credentials')
                    <div class="mb-3 text-sm text-red-600">{{ $message }}</div>
                @enderror

                <div>
                    <label for="mand_uname"
                           class="block text-sm font-medium text-gray-700 mb-1">
                        Benutzername
                    </label>
                    <input id="mand_uname"
                           type="text"
                           name="username"
                           value="{{ old('username') }}"
                           class="block w-full rounded-lg border-gray-300 shadow-sm
                                  focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                           required autofocus>
                </div>

                <div class="mt-4">
                    <label for="mand_password"
                           class="block text-sm font-medium text-gray-700 mb-1">
                        Passwort
                    </label>
                    <input id="mand_password"
                           type="password"
                           name="password"
                           class="block w-full rounded-lg border-gray-300 shadow-sm
                                  focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                           required>
                </div>

                <div class="mt-5">
                    <button type="submit"
                            class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm
                                   font-semibold text-white hover:bg-indigo-700
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500
                                   focus:ring-offset-2 transition-colors">
                        Anmelden
                    </button>
                </div>
            </form>

            <div class="mt-5 flex items-center justify-between">
                <button type="button"
                        @click="page = 'cust'"
                        class="text-xs text-gray-400 hover:text-gray-600 transition-colors">
                    ← Zurück
                </button>
                <a href="#"
                   class="text-xs text-gray-400 hover:text-gray-600 transition-colors">
                    Passwort vergessen?
                </a>
            </div>

        </div>

    </div>

</body>
</html>
