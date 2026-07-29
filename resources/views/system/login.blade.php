<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Anmelden</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans antialiased">

<div class="min-h-screen flex items-center justify-center px-4"
     x-data="{ show2fa: {{ session('show_2fa') ? 'true' : 'false' }}, dirty: false }">

    <div class="w-full max-w-sm bg-white rounded-lg shadow-md px-8 py-8">

        <h1 class="text-xl font-semibold text-gray-800 mb-6">Anmelden</h1>

        @if ($errors->syst->any())
            <div class="mb-5 space-y-1" x-show="!dirty">
                @foreach ($errors->syst->all() as $error)
                    <p class="text-sm text-red-600">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        {{-- Zustand 1: E-Mail + Passwort --}}
        <form x-show="!show2fa"
              method="POST" action="{{ route('system.backstage.handle') }}"
              autocomplete="off"
              x-data="{ submitted: false }">
            @csrf

            <div>
                <label for="email"
                       class="block text-sm font-medium text-gray-700">E-Mail</label>
                <input id="email" name="email" type="email"
                       placeholder="Email-Adresse"
                       @input="dirty = true"
                       required autofocus
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm
                              focus:border-gray-500 focus:ring-gray-500">
            </div>

            <div class="mt-4">
                <label for="password"
                       class="block text-sm font-medium text-gray-700">Passwort</label>
                <input id="password" name="password" type="password"
                       @input="dirty = true"
                       required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm
                              focus:border-gray-500 focus:ring-gray-500">
            </div>

            <div class="mt-6">
                <button type="button"
                        @click="submitted = true; $el.closest('form').submit()"
                        :disabled="submitted"
                        class="w-full flex justify-center py-2 px-4 rounded-md text-sm font-medium
                               text-white bg-gray-800 hover:bg-gray-700 transition-colors
                               focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    Anmelden
                </button>
            </div>
        </form>

        {{-- Zustand 2: SMS-Code --}}
        <div x-show="show2fa">

            <form method="POST" action="{{ route('system.login.verify') }}"
                  autocomplete="off">
                @csrf

                <p class="text-sm text-gray-600 mb-5">
                    Ein Code wurde an Ihre hinterlegte E-Mail-Adresse gesendet.
                </p>

                <div>
                    <label for="code"
                           class="block text-sm font-medium text-gray-700">Bestätigungscode</label>
                    <input id="code" name="code" type="text"
                           inputmode="numeric" pattern="[0-9]{6}" maxlength="6"
                           @input="dirty = true"
                           required autocomplete="one-time-code"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm
                                  text-center text-xl tracking-widest font-mono
                                  focus:border-gray-500 focus:ring-gray-500">
                </div>

                <div class="mt-5">
                    <button type="button"
                            @click="$el.closest('form').submit()"
                            class="w-full flex justify-center py-2 px-4 rounded-md text-sm font-medium
                                   text-white bg-gray-800 hover:bg-gray-700 transition-colors
                                   focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                        Bestätigen
                    </button>
                </div>
            </form>

            <div class="mt-5 text-center">
                <button type="button"
                        @click="window.location='{{ route('system.backstage.login') }}'"
                        class="text-sm text-gray-500 hover:text-gray-700 transition-colors select-none">
                    ← Zurück zum Login
                </button>
            </div>

        </div>

    </div>
</div>

</body>
</html>
