{{--
    FILE:    resources/views/mandant/pwlist.blade.php
    VERSION: 1.0.0

    DESCRIPTION:
      Mandant Passwortliste — pw1–pw6 und Gültigkeitszeitraum bearbeiten.
      Standalone (kein Layout-Erbe), gleiches Strukturmuster wie mandant/konto.
      Accent-Farbe: indigo.

    DATA FROM CONTROLLER:
      $pwlist (PwList|null) — vorhandene Passwortliste oder null bei Erstanlage

    ROUTES USED:
      GET   mandant.dashboard    — Zurück-Link
      PATCH mandant.pwlist.update — Passwortliste speichern
      POST  mandant.logout       — Abmelden
--}}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Passwortliste · Fotosite V8</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
                  autocomplete="off">
                @csrf
                @method('PATCH')

                <div class="space-y-4">

                    {{-- pw1 --}}
                    <div>
                        <label for="pw1"
                               class="block text-sm font-medium text-gray-700">
                            Passwort Stufe 1
                        </label>
                        <input id="pw1" name="pw1" type="password"
                               autocomplete="off"
                               value="{{ old('pw1', $pwlist->pw1 ?? '') }}"
                               class="mt-1 block w-full rounded-md border-gray-300
                                      shadow-sm text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500
                                      @error('pw1') border-red-400 @enderror">
                        @error('pw1')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- pw2 --}}
                    <div>
                        <label for="pw2"
                               class="block text-sm font-medium text-gray-700">
                            Passwort Stufe 2
                        </label>
                        <input id="pw2" name="pw2" type="password"
                               autocomplete="off"
                               value="{{ old('pw2', $pwlist->pw2 ?? '') }}"
                               class="mt-1 block w-full rounded-md border-gray-300
                                      shadow-sm text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500
                                      @error('pw2') border-red-400 @enderror">
                        @error('pw2')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- pw3 --}}
                    <div>
                        <label for="pw3"
                               class="block text-sm font-medium text-gray-700">
                            Passwort Stufe 3
                        </label>
                        <input id="pw3" name="pw3" type="password"
                               autocomplete="off"
                               value="{{ old('pw3', $pwlist->pw3 ?? '') }}"
                               class="mt-1 block w-full rounded-md border-gray-300
                                      shadow-sm text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500
                                      @error('pw3') border-red-400 @enderror">
                        @error('pw3')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- pw4 --}}
                    <div>
                        <label for="pw4"
                               class="block text-sm font-medium text-gray-700">
                            Passwort Stufe 4
                        </label>
                        <input id="pw4" name="pw4" type="password"
                               autocomplete="off"
                               value="{{ old('pw4', $pwlist->pw4 ?? '') }}"
                               class="mt-1 block w-full rounded-md border-gray-300
                                      shadow-sm text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500
                                      @error('pw4') border-red-400 @enderror">
                        @error('pw4')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- pw5 --}}
                    <div>
                        <label for="pw5"
                               class="block text-sm font-medium text-gray-700">
                            Passwort Stufe 5
                        </label>
                        <input id="pw5" name="pw5" type="password"
                               autocomplete="off"
                               value="{{ old('pw5', $pwlist->pw5 ?? '') }}"
                               class="mt-1 block w-full rounded-md border-gray-300
                                      shadow-sm text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500
                                      @error('pw5') border-red-400 @enderror">
                        @error('pw5')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- pw6 --}}
                    <div>
                        <label for="pw6"
                               class="block text-sm font-medium text-gray-700">
                            Passwort Stufe 6
                        </label>
                        <input id="pw6" name="pw6" type="password"
                               autocomplete="off"
                               value="{{ old('pw6', $pwlist->pw6 ?? '') }}"
                               class="mt-1 block w-full rounded-md border-gray-300
                                      shadow-sm text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500
                                      @error('pw6') border-red-400 @enderror">
                        @error('pw6')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- valid_from / valid_until --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">

                        <div>
                            <label for="valid_from"
                                   class="block text-sm font-medium text-gray-700">
                                Gültig ab
                            </label>
                            <input id="valid_from" name="valid_from" type="date"
                                   value="{{ old('valid_from', $pwlist->valid_from?->format('Y-m-d') ?? '') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300
                                          shadow-sm text-sm
                                          focus:border-indigo-500 focus:ring-indigo-500
                                          @error('valid_from') border-red-400 @enderror">
                            @error('valid_from')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="valid_until"
                                   class="block text-sm font-medium text-gray-700">
                                Gültig bis
                            </label>
                            <input id="valid_until" name="valid_until" type="date"
                                   value="{{ old('valid_until', $pwlist->valid_until?->format('Y-m-d') ?? '') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300
                                          shadow-sm text-sm
                                          focus:border-indigo-500 focus:ring-indigo-500
                                          @error('valid_until') border-red-400 @enderror">
                            @error('valid_until')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
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

</body>
</html>
