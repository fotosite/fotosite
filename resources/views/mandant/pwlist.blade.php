{{--
    FILE:    resources/views/mandant/pwlist.blade.php
    VERSION: 1.8.0

    DESCRIPTION:
      Mandant Passwortliste — pw1–pw6 und Gültigkeitszeitraum bearbeiten.
      Standalone (kein Layout-Erbe), gleiches Strukturmuster wie mandant/konto.
      Accent-Farbe: indigo.
      pw1–pw6: Auge-Icon (toggle show/hide) + Kopieren-Icon je Feld (Alpine.js).

    DATA FROM CONTROLLER:
      $pwlist (PwList|null) — vorhandene Passwortliste (pw1–pw6 entschlüsselt)
                               oder null bei Erstanlage

    ROUTES USED:
      GET   mandant.dashboard     — Zurück-Link
      PATCH mandant.pwlist.update — Passwortliste speichern
      POST  mandant.logout        — Abmelden
--}}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Passwortliste · Fotosite V8</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>

<body class="min-h-screen bg-gray-50 text-gray-900 antialiased"
      x-data>

    {{-- ══════════════════════════════════════════════════════
         TOP BAR
    ══════════════════════════════════════════════════════ --}}
    <header class="sticky top-0 z-20 border-b border-gray-200 bg-white shadow-sm">
        <div class="mx-auto max-w-3xl px-6 h-14
                    flex items-center justify-between">

            {{-- Brand --}}
            <div class="flex items-center gap-3">
                <span class="text-[11px] font-mono tracking-widest
                             uppercase text-gray-400">
                    Fotosite&thinsp;V8
                </span>
                <span class="text-zinc-800 select-none">|</span>
                <span class="text-sm font-semibold tracking-widest
                             uppercase text-indigo-600">
                    Mandant
                </span>
            </div>

            {{-- Logout --}}
            <div class="flex items-center">
                <form method="POST" action="{{ route('mandant.logout') }}">
                    @csrf
                    <button type="submit"
                            class="text-xs text-gray-400 hover:text-red-500
                                   transition-colors duration-150 tracking-wide">
                        Abmelden
                    </button>
                </form>
            </div>

        </div>
    </header>

    {{-- ══════════════════════════════════════════════════════
         MAIN
    ══════════════════════════════════════════════════════ --}}
    <main class="mx-auto max-w-3xl px-6 pt-10 pb-24">

        {{-- Zurück-Link --}}
        <div class="mb-6">
            <a href="{{ route('mandant.dashboard') }}"
               class="text-xs text-gray-400 hover:text-gray-600
                      transition-colors duration-150 tracking-wide">
                ← Dashboard
            </a>
        </div>

        {{-- Seitenüberschrift --}}
        <div class="mb-8">
            <h1 class="text-xl font-semibold tracking-tight text-gray-800">
                Passwortliste
            </h1>
            <p class="mt-1.5 text-sm text-zinc-600">
                Passwörter und Gültigkeitszeitraum verwalten.
            </p>
        </div>

        {{-- Flash: Gespeichert --}}
        @if(session('status'))
            <div class="mb-6 rounded-lg border border-indigo-200
                        bg-indigo-50 px-4 py-3 text-sm text-indigo-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">

            {{-- Hinweistext --}}
            <p class="mb-6 text-xs text-gray-500 leading-relaxed
                       rounded-lg border border-gray-100 bg-gray-50 px-4 py-3">
                Jedes Passwort entspricht einer Sicherheitsstufe (1–6).<br>
                Mindestlänge: 8 Zeichen.
            </p>

            <form method="POST"
                  action="{{ route('mandant.pwlist.update') }}"
                  autocomplete="off"
                  x-data="{
                      validFrom: '{{ old('valid_from', $pwlist?->valid_from?->format('Y-m-d') ?? '') }}',
                      validUntil: '{{ old('valid_until', $pwlist?->valid_until?->format('Y-m-d') ?? '') }}',
                      today: new Date().toISOString().split('T')[0],
                      get fromInPast() { return this.validFrom && this.validFrom < this.today },
                      get untilInPast() { return this.validUntil && this.validUntil < this.today },
                      get untilBeforeFrom() { return this.validFrom && this.validUntil && this.validUntil < this.validFrom },
                      fixFromDate() {},
                      fixUntilDate() {
                          if (this.untilBeforeFrom) {
                              this.validUntil = this.validFrom;
                              this.$nextTick(() => {
                                  const fp = this.$refs.validUntil?._flatpickr;
                                  if (fp) fp.setDate(this.validUntil, true);
                              });
                          }
                      }
                  }">
                @csrf
                @method('PATCH')

                <div class="space-y-4">

                    {{-- pw1 --}}
                    <div x-data="{ show: false, tooShort: false }">
                        <label for="pw1"
                               class="block text-sm font-medium text-gray-700">
                            Passwort Stufe 1
                        </label>
                        <div class="relative mt-1">
                            <input id="pw1" name="pw1" x-ref="pw"
                                   :type="show ? 'text' : 'password'"
                                   autocomplete="off"
                                   @blur="tooShort = $el.value.length > 0 && $el.value.length < 8"
                                   value="{{ old('pw1', $pwlist->pw1 ?? '') }}"
                                   class="block w-full rounded-md border-gray-300
                                          shadow-sm text-sm pr-16
                                          focus:border-indigo-500 focus:ring-indigo-500
                                          @error('pw1') border-red-400 @enderror">
                            <div class="absolute inset-y-0 right-0 flex items-center">
                                <button type="button" @click="show = !show"
                                        class="px-2 text-gray-400 hover:text-gray-600 transition-colors">
                                    <svg x-show="!show" class="w-4 h-4" xmlns="http://www.w3.org/2000/svg"
                                         fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                    </svg>
                                    <svg x-show="show" class="w-4 h-4" xmlns="http://www.w3.org/2000/svg"
                                         fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/>
                                    </svg>
                                </button>
                                <button type="button"
                                        @click="navigator.clipboard.writeText($refs.pw.value)"
                                        class="px-2 text-gray-400 hover:text-gray-600 transition-colors">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg"
                                         fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        @error('pw1')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <p x-show="tooShort" x-cloak
                           class="text-sm text-amber-600 mt-1">
                            ⚠️ Passwort zu kurz — mindestens 8 Zeichen erforderlich.
                        </p>
                    </div>

                    {{-- pw2 --}}
                    <div x-data="{ show: false, tooShort: false }">
                        <label for="pw2"
                               class="block text-sm font-medium text-gray-700">
                            Passwort Stufe 2
                        </label>
                        <div class="relative mt-1">
                            <input id="pw2" name="pw2" x-ref="pw"
                                   :type="show ? 'text' : 'password'"
                                   autocomplete="off"
                                   @blur="tooShort = $el.value.length > 0 && $el.value.length < 8"
                                   value="{{ old('pw2', $pwlist->pw2 ?? '') }}"
                                   class="block w-full rounded-md border-gray-300
                                          shadow-sm text-sm pr-16
                                          focus:border-indigo-500 focus:ring-indigo-500
                                          @error('pw2') border-red-400 @enderror">
                            <div class="absolute inset-y-0 right-0 flex items-center">
                                <button type="button" @click="show = !show"
                                        class="px-2 text-gray-400 hover:text-gray-600 transition-colors">
                                    <svg x-show="!show" class="w-4 h-4" xmlns="http://www.w3.org/2000/svg"
                                         fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                    </svg>
                                    <svg x-show="show" class="w-4 h-4" xmlns="http://www.w3.org/2000/svg"
                                         fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/>
                                    </svg>
                                </button>
                                <button type="button"
                                        @click="navigator.clipboard.writeText($refs.pw.value)"
                                        class="px-2 text-gray-400 hover:text-gray-600 transition-colors">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg"
                                         fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        @error('pw2')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <p x-show="tooShort" x-cloak
                           class="text-sm text-amber-600 mt-1">
                            ⚠️ Passwort zu kurz — mindestens 8 Zeichen erforderlich.
                        </p>
                    </div>

                    {{-- pw3 --}}
                    <div x-data="{ show: false, tooShort: false }">
                        <label for="pw3"
                               class="block text-sm font-medium text-gray-700">
                            Passwort Stufe 3
                        </label>
                        <div class="relative mt-1">
                            <input id="pw3" name="pw3" x-ref="pw"
                                   :type="show ? 'text' : 'password'"
                                   autocomplete="off"
                                   @blur="tooShort = $el.value.length > 0 && $el.value.length < 8"
                                   value="{{ old('pw3', $pwlist->pw3 ?? '') }}"
                                   class="block w-full rounded-md border-gray-300
                                          shadow-sm text-sm pr-16
                                          focus:border-indigo-500 focus:ring-indigo-500
                                          @error('pw3') border-red-400 @enderror">
                            <div class="absolute inset-y-0 right-0 flex items-center">
                                <button type="button" @click="show = !show"
                                        class="px-2 text-gray-400 hover:text-gray-600 transition-colors">
                                    <svg x-show="!show" class="w-4 h-4" xmlns="http://www.w3.org/2000/svg"
                                         fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                    </svg>
                                    <svg x-show="show" class="w-4 h-4" xmlns="http://www.w3.org/2000/svg"
                                         fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/>
                                    </svg>
                                </button>
                                <button type="button"
                                        @click="navigator.clipboard.writeText($refs.pw.value)"
                                        class="px-2 text-gray-400 hover:text-gray-600 transition-colors">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg"
                                         fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        @error('pw3')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <p x-show="tooShort" x-cloak
                           class="text-sm text-amber-600 mt-1">
                            ⚠️ Passwort zu kurz — mindestens 8 Zeichen erforderlich.
                        </p>
                    </div>

                    {{-- pw4 --}}
                    <div x-data="{ show: false, tooShort: false }">
                        <label for="pw4"
                               class="block text-sm font-medium text-gray-700">
                            Passwort Stufe 4
                        </label>
                        <div class="relative mt-1">
                            <input id="pw4" name="pw4" x-ref="pw"
                                   :type="show ? 'text' : 'password'"
                                   autocomplete="off"
                                   @blur="tooShort = $el.value.length > 0 && $el.value.length < 8"
                                   value="{{ old('pw4', $pwlist->pw4 ?? '') }}"
                                   class="block w-full rounded-md border-gray-300
                                          shadow-sm text-sm pr-16
                                          focus:border-indigo-500 focus:ring-indigo-500
                                          @error('pw4') border-red-400 @enderror">
                            <div class="absolute inset-y-0 right-0 flex items-center">
                                <button type="button" @click="show = !show"
                                        class="px-2 text-gray-400 hover:text-gray-600 transition-colors">
                                    <svg x-show="!show" class="w-4 h-4" xmlns="http://www.w3.org/2000/svg"
                                         fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                    </svg>
                                    <svg x-show="show" class="w-4 h-4" xmlns="http://www.w3.org/2000/svg"
                                         fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/>
                                    </svg>
                                </button>
                                <button type="button"
                                        @click="navigator.clipboard.writeText($refs.pw.value)"
                                        class="px-2 text-gray-400 hover:text-gray-600 transition-colors">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg"
                                         fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        @error('pw4')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <p x-show="tooShort" x-cloak
                           class="text-sm text-amber-600 mt-1">
                            ⚠️ Passwort zu kurz — mindestens 8 Zeichen erforderlich.
                        </p>
                    </div>

                    {{-- pw5 --}}
                    <div x-data="{ show: false, tooShort: false }">
                        <label for="pw5"
                               class="block text-sm font-medium text-gray-700">
                            Passwort Stufe 5
                        </label>
                        <div class="relative mt-1">
                            <input id="pw5" name="pw5" x-ref="pw"
                                   :type="show ? 'text' : 'password'"
                                   autocomplete="off"
                                   @blur="tooShort = $el.value.length > 0 && $el.value.length < 8"
                                   value="{{ old('pw5', $pwlist->pw5 ?? '') }}"
                                   class="block w-full rounded-md border-gray-300
                                          shadow-sm text-sm pr-16
                                          focus:border-indigo-500 focus:ring-indigo-500
                                          @error('pw5') border-red-400 @enderror">
                            <div class="absolute inset-y-0 right-0 flex items-center">
                                <button type="button" @click="show = !show"
                                        class="px-2 text-gray-400 hover:text-gray-600 transition-colors">
                                    <svg x-show="!show" class="w-4 h-4" xmlns="http://www.w3.org/2000/svg"
                                         fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                    </svg>
                                    <svg x-show="show" class="w-4 h-4" xmlns="http://www.w3.org/2000/svg"
                                         fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/>
                                    </svg>
                                </button>
                                <button type="button"
                                        @click="navigator.clipboard.writeText($refs.pw.value)"
                                        class="px-2 text-gray-400 hover:text-gray-600 transition-colors">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg"
                                         fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        @error('pw5')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <p x-show="tooShort" x-cloak
                           class="text-sm text-amber-600 mt-1">
                            ⚠️ Passwort zu kurz — mindestens 8 Zeichen erforderlich.
                        </p>
                    </div>

                    {{-- pw6 --}}
                    <div x-data="{ show: false, tooShort: false }">
                        <label for="pw6"
                               class="block text-sm font-medium text-gray-700">
                            Passwort Stufe 6
                        </label>
                        <div class="relative mt-1">
                            <input id="pw6" name="pw6" x-ref="pw"
                                   :type="show ? 'text' : 'password'"
                                   autocomplete="off"
                                   @blur="tooShort = $el.value.length > 0 && $el.value.length < 8"
                                   value="{{ old('pw6', $pwlist->pw6 ?? '') }}"
                                   class="block w-full rounded-md border-gray-300
                                          shadow-sm text-sm pr-16
                                          focus:border-indigo-500 focus:ring-indigo-500
                                          @error('pw6') border-red-400 @enderror">
                            <div class="absolute inset-y-0 right-0 flex items-center">
                                <button type="button" @click="show = !show"
                                        class="px-2 text-gray-400 hover:text-gray-600 transition-colors">
                                    <svg x-show="!show" class="w-4 h-4" xmlns="http://www.w3.org/2000/svg"
                                         fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                    </svg>
                                    <svg x-show="show" class="w-4 h-4" xmlns="http://www.w3.org/2000/svg"
                                         fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/>
                                    </svg>
                                </button>
                                <button type="button"
                                        @click="navigator.clipboard.writeText($refs.pw.value)"
                                        class="px-2 text-gray-400 hover:text-gray-600 transition-colors">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg"
                                         fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        @error('pw6')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <p x-show="tooShort" x-cloak
                           class="text-sm text-amber-600 mt-1">
                            ⚠️ Passwort zu kurz — mindestens 8 Zeichen erforderlich.
                        </p>
                    </div>

                    {{-- valid_from / valid_until --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">

                        <div>
                            <label for="valid_from"
                                   class="block text-sm font-medium text-gray-700">
                                Gültig ab
                            </label>
                            <input id="valid_from" name="valid_from" type="text"
                                   x-ref="validFrom"
                                   x-model="validFrom"
                                   x-init="flatpickr($el, {
                                       dateFormat: 'Y-m-d',
                                       allowInput: true,
                                       locale: 'de',
                                       onChange: (dates, dateStr) => { validFrom = dateStr; fixFromDate(); }
                                   })"
                                   value="{{ old('valid_from', $pwlist?->valid_from?->format('Y-m-d') ?? '') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300
                                          shadow-sm text-sm
                                          focus:border-indigo-500 focus:ring-indigo-500
                                          @error('valid_from') border-red-400 @enderror">
                            @error('valid_from')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                            <p x-show="fromInPast" x-cloak
                               class="text-sm text-amber-600 mt-1">
                                ⚠️ Gültigkeitsbeginn liegt in der Vergangenheit.
                            </p>
                        </div>

                        <div>
                            <label for="valid_until"
                                   class="block text-sm font-medium text-gray-700">
                                Gültig bis
                            </label>
                            <input id="valid_until" name="valid_until" type="text"
                                   x-ref="validUntil"
                                   x-model="validUntil"
                                   x-init="flatpickr($el, {
                                       dateFormat: 'Y-m-d',
                                       allowInput: true,
                                       locale: 'de',
                                       onChange: (dates, dateStr) => { validUntil = dateStr; fixUntilDate(); }
                                   })"
                                   value="{{ old('valid_until', $pwlist?->valid_until?->format('Y-m-d') ?? '') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300
                                          shadow-sm text-sm
                                          focus:border-indigo-500 focus:ring-indigo-500
                                          @error('valid_until') border-red-400 @enderror">
                            @error('valid_until')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                            <p x-show="untilInPast" x-cloak
                               class="text-sm text-amber-600 mt-1">
                                ⚠️ Ablaufdatum liegt in der Vergangenheit.
                            </p>
                            <p x-show="untilBeforeFrom" x-cloak
                               class="text-sm text-red-600 mt-1">
                                ⚠️ Ablaufdatum liegt vor dem Gültigkeitsbeginn — auf Beginndatum gesetzt.
                            </p>
                        </div>

                    </div>

                </div>

                <div class="mt-6">
                    <button type="submit"
                            class="w-full flex justify-center py-2 px-4 rounded-md
                                   text-sm font-medium text-white bg-indigo-600
                                   hover:bg-indigo-700 transition-colors
                                   focus:outline-none focus:ring-2
                                   focus:ring-indigo-500 focus:ring-offset-2">
                        Passwortliste speichern
                    </button>
                </div>

            </form>
        </div>

    </main>

    {{-- ══════════════════════════════════════════════════════
         FOOTER
    ══════════════════════════════════════════════════════ --}}
    <footer class="fixed bottom-0 inset-x-0 border-t border-gray-200 bg-white shadow-sm">
        <div class="mx-auto max-w-3xl px-6 h-9
                    flex items-center justify-between">
            <span class="text-[10px] font-mono tracking-widest
                         uppercase text-gray-400">
                Fotosite V8 · Mandanten-Bereich
            </span>
            <span class="text-[10px] text-gray-400">
                Session aktiv
            </span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/de.js"></script>
</body>
</html>
