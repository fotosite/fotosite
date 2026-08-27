{{--
    FILE:    resources/views/mandant/cust/einladen.blade.php
    VERSION: 1.5.1
    AUTHOR:  Martin Wagner
    DATE:    2026-06-25

    DESCRIPTION:
      Einladungsformular für neue Mitglieder.
      POST zu route('mandant.kunden.store').

    CHANGES: 1.5.1 (2026-06-25) Android-Touch-Targets vergroessert: Logout-
             Button, Submit-Button und Zurueck-Link auf min-h-11 py-2
             angehoben; betroffene text-xs auf text-sm.

    ROUTES USED:
      POST /mandant/kunden/einladen — Einladung senden (route('mandant.kunden.store'))
      GET  /mandant/kunden          — Mitgliederliste (route('mandant.kunden.index'))
      POST /mandant/logout          — Mandant-Logout (route('mandant.logout'))
--}}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Mitglieder einladen · Fotogalerie</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-50 text-gray-900 antialiased"
      x-data>

    @php $mandUname = \App\Models\UserDb\MandUser::find(session('_mand_id'))?->mand_uname ?? ''; @endphp

    {{-- ══════════════════════════════════════════════════════
         TOP BAR
    ══════════════════════════════════════════════════════ --}}
    <header class="sticky top-0 z-20 border-b border-gray-200 bg-white shadow-sm">
        <div class="mx-auto max-w-4xl px-6 h-14
                    flex items-center justify-between">

            {{-- Brand --}}
            <div class="flex items-center gap-3">
                <span class="text-[11px] font-mono tracking-widest
                             uppercase text-gray-400">
                    Fotogalerie
                </span>
                <span class="text-zinc-800 select-none">|</span>
                <span class="text-sm font-semibold tracking-widest
                             uppercase text-indigo-600">
                    Galerist:in
                </span>
                <span class="text-sm text-indigo-200">{{ $mandUname }}</span>
            </div>

            {{-- Logout --}}
            <x-logout-button user-type="mand" />

        </div>
    </header>

    {{-- ══════════════════════════════════════════════════════
         MAIN
    ══════════════════════════════════════════════════════ --}}
    <main class="mx-auto max-w-4xl px-6 pt-14 pb-24">

        {{-- Seitenüberschrift --}}
        <div class="mb-8">
            <h1 class="text-xl font-semibold tracking-tight text-gray-800">
                Mitglieder einladen
            </h1>
            <p class="mt-1.5 text-sm text-zinc-600">
                Einladungs-E-Mail an ein neues Mitglied senden.
            </p>
        </div>

        {{-- Flash-Meldung --}}
        @if(session('status'))
            <div class="mb-6 rounded-lg border border-indigo-200
                        bg-indigo-50 px-4 py-3 text-sm text-indigo-700">
                {{ session('status') }}
            </div>
        @endif

        {{-- Formular --}}
        <form method="POST" action="{{ route('mandant.kunden.store') }}"
              class="rounded-xl border border-gray-200 bg-white p-6 space-y-6
                     max-w-lg">
            @csrf

            {{-- E-Mail --}}
            <div x-data="{ dirty: false }">
                <label for="cust_email"
                       class="block text-sm font-medium text-gray-700 mb-1.5">
                    E-Mail
                </label>
                <input type="email"
                       id="cust_email"
                       name="cust_email"
                       value="{{ old('cust_email') }}"
                       placeholder="E-Mail Mitglied"
                       required
                       autocomplete="off"
                       @input="dirty = true"
                       class="w-full rounded-lg border px-3 py-2 text-sm
                              text-gray-800 shadow-sm
                              focus:outline-none focus:ring-2 focus:ring-indigo-400
                              @error('cust_email') border-red-400 bg-red-50 @else border-gray-300 @enderror">
                @error('cust_email')
                    <p class="mt-1.5 text-xs text-red-600" x-show="!dirty">{{ $message }}</p>
                @enderror
            </div>

            {{-- Interner Alias --}}
            <div>
                <label for="cust_alias"
                       class="block text-sm font-medium text-gray-700 mb-1.5">
                    Ihr interner Name für dieses Mitglied
                </label>
                <input type="text"
                       id="cust_alias"
                       name="cust_alias"
                       value="{{ old('cust_alias') }}"
                       required
                       placeholder="z.B. Anna M., Schwester, Kollege Max"
                       autocomplete="off"
                       class="w-full rounded-lg border px-3 py-2 text-sm
                              text-gray-800 shadow-sm
                              focus:outline-none focus:ring-2 focus:ring-indigo-400
                              @error('cust_alias') border-red-400 bg-red-50 @else border-gray-300 @enderror">
                <div class="text-xs text-gray-500 mt-1">
                    {!! uiText('mand', 'm_invite_alias_erklaerung') !!}
                </div>
                @error('cust_alias')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Security Level --}}
            <div>
                <label for="sec_level"
                       class="block text-sm font-medium text-gray-700 mb-1.5">
                    Sicherheitsstufe
                </label>
                <select id="sec_level"
                        name="sec_level"
                        required
                        class="w-full rounded-lg border px-3 py-2 text-sm
                               text-gray-800 shadow-sm bg-white
                               focus:outline-none focus:ring-2 focus:ring-indigo-400
                               @error('sec_level') border-red-400 bg-red-50 @else border-gray-300 @enderror">
                    <option value="" disabled {{ old('sec_level') === null ? 'selected' : '' }}>
                        — bitte wählen —
                    </option>
                    <option value="1" {{ old('sec_level') == '1' ? 'selected' : '' }}>
                        1 — Bekannte
                    </option>
                    <option value="2" {{ old('sec_level') == '2' ? 'selected' : '' }}>
                        2 — Großfamilie
                    </option>
                    <option value="3" {{ old('sec_level') == '3' ? 'selected' : '' }}>
                        3 — Freunde
                    </option>
                    <option value="4" {{ old('sec_level') == '4' ? 'selected' : '' }}>
                        4 — Enge Freunde &amp; Kernfamilie
                    </option>
                    <option value="5" {{ old('sec_level') == '5' ? 'selected' : '' }}>
                        5 — Vertraulich
                    </option>
                    <option value="6" {{ old('sec_level') == '6' ? 'selected' : '' }}>
                        6 — Streng vertraulich
                    </option>
                </select>
                @error('sec_level')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Submit --}}
            <div class="pt-2">
                <button type="submit"
                        class="inline-flex items-center rounded-lg min-h-11
                               bg-indigo-600 px-5 py-2 text-sm font-medium
                               text-white hover:bg-indigo-700
                               transition-colors duration-150">
                    Einladung senden
                </button>
            </div>

        </form>

        {{-- Zurück --}}
        <div class="mt-8">
            <button type="button"
                    @click="window.location='{{ route('mandant.kunden.index') }}'"
                    class="inline-flex items-center min-h-11 py-2 text-sm text-gray-400 hover:text-indigo-600
                           transition-colors duration-150 tracking-wide select-none">
                ← Zurück zur Mitgliederliste
            </button>
        </div>

    </main>

    {{-- ══════════════════════════════════════════════════════
         FOOTER
    ══════════════════════════════════════════════════════ --}}
    <footer class="fixed bottom-0 inset-x-0 border-t border-gray-200 bg-white shadow-sm">
        <div class="mx-auto max-w-4xl px-6 h-9
                    flex items-center justify-between">
            <span class="text-[10px] font-mono tracking-widest
                         uppercase text-gray-400">
                Fotogalerie · Galeristen-Bereich
            </span>
            <span class="text-[10px] text-gray-400">
                Session aktiv
            </span>
        </div>
    </footer>

</body>
</html>
