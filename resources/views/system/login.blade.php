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
     x-data="{
         show2fa: {{ session('show_2fa') ? 'true' : 'false' }},
         countdown: 300,
         expired: false,
         init() {
             if (this.show2fa) {
                 this.startCountdown();
             }
         },
         startCountdown() {
             const interval = setInterval(() => {
                 if (this.countdown > 0) {
                     this.countdown--;
                 } else {
                     this.expired = true;
                     clearInterval(interval);
                 }
             }, 1000);
         },
         formattedCountdown() {
             const m = String(Math.floor(this.countdown / 60)).padStart(2, '0');
             const s = String(this.countdown % 60).padStart(2, '0');
             return m + ':' + s;
         }
     }">

    <div class="w-full max-w-sm bg-white rounded-lg shadow-md px-8 py-8">

        <h1 class="text-xl font-semibold text-gray-800 mb-6">Anmelden</h1>

        @if ($errors->any())
            <div class="mb-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <p class="text-sm text-red-600">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        {{-- Zustand 1: E-Mail + Passwort --}}
        <form x-show="!show2fa"
              method="POST" action="/backstage"
              autocomplete="off">
            @csrf

            <div>
                <label for="email"
                       class="block text-sm font-medium text-gray-700">E-Mail</label>
                <input id="email" name="email" type="email"
                       placeholder="Email-Adresse"
                       required autofocus
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm
                              focus:border-gray-500 focus:ring-gray-500">
            </div>

            <div class="mt-4">
                <label for="password"
                       class="block text-sm font-medium text-gray-700">Passwort</label>
                <input id="password" name="password" type="password"
                       required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm
                              focus:border-gray-500 focus:ring-gray-500">
            </div>

            <div class="mt-6">
                <button type="submit"
                        class="w-full flex justify-center py-2 px-4 rounded-md text-sm font-medium
                               text-white bg-gray-800 hover:bg-gray-700 transition-colors
                               focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    Anmelden
                </button>
            </div>
        </form>

        {{-- Zustand 2: SMS-Code --}}
        <div x-show="show2fa">

            <form x-show="!expired"
                  method="POST" action="{{ route('system.login.verify') }}"
                  autocomplete="off">
                @csrf

                <p class="text-sm text-gray-600 mb-5">
                    Ein Code wurde an Ihre hinterlegte Nummer gesendet.
                </p>

                <div>
                    <label for="code"
                           class="block text-sm font-medium text-gray-700">Bestätigungscode</label>
                    <input id="code" name="code" type="text"
                           inputmode="numeric" pattern="[0-9]{6}" maxlength="6"
                           required autocomplete="one-time-code"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm
                                  text-center text-xl tracking-widest font-mono
                                  focus:border-gray-500 focus:ring-gray-500">
                </div>

                <p class="mt-3 text-center text-xs text-gray-400">
                    Gültig noch
                    <span class="font-mono text-gray-600" x-text="formattedCountdown()"></span>
                </p>

                <div class="mt-5">
                    <button type="submit"
                            class="w-full flex justify-center py-2 px-4 rounded-md text-sm font-medium
                                   text-white bg-gray-800 hover:bg-gray-700 transition-colors
                                   focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                        Bestätigen
                    </button>
                </div>
            </form>

            <p x-show="expired" class="text-sm text-red-600">
                Code abgelaufen — bitte neu anmelden.
            </p>

        </div>

    </div>
</div>

</body>
</html>
