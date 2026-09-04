{{--
    FILE:    resources/views/system/mandanten/index.blade.php
    VERSION: 1.4.0
    DATE:    2026-09-04

    DESCRIPTION:
      Mandantenverwaltung overview — invite new mandants, list existing mandants,
      with status indicator, links to show/edit. Delete action moved to
      edit.blade.php (only available after grace period since deactivation).
      Light theme matching dashboard.blade.php.

    DATA FROM CONTROLLER:
      $mandanten       (Collection<MandUser>) — all mandants ordered by mand_lastname
      $currentUserName (string)               — syst_uname of logged-in system user

    ROUTES USED:
      POST   system.mandanten.invite   — send invite email
      GET    system.mandanten.show     — view mandant detail
      GET    system.mandanten.edit     — edit mandant settings
      GET    system.dashboard          — back link
      POST   logout                    — Breeze logout

    CHANGES: 1.4.0 (2026-09-04) Löschen-Aktion komplett aus dieser Seite entfernt
             (Desktop-/Mobil-Formular, Lösch-Modal, deleteModal aus body x-data,
             canDelete-Feld im items-Array); Löschen läuft jetzt ausschließlich
             über die Bearbeiten-Seite (edit.blade.php).
             1.3.0 (2026-09-04) Löschen-Button nur noch sichtbar nach Ablauf der
             Karenzzeit (config('mand_deactivation.grace_days')) seit Deaktivierung;
             '(inaktiv)'-Suffix beim Namen ergänzt (Desktop + Mobil).
             1.2.1 (2026-06-25) Android-Touch-Targets vergroessert: Logout-
             Button, Zurueck-Link, Einladung-senden-Button und Tabellen-
             Aktionen (Ansehen/Bearbeiten/Löschen) auf min-h-11 angehoben;
             betroffene text-xs auf text-sm.
--}}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Galeristen-Verwaltung · Fotogalerie</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-50 text-gray-900 antialiased"
      x-data>

    {{-- ══════════════════════════════════════════════════════
         TOP BAR
    ══════════════════════════════════════════════════════ --}}
    <header class="sticky top-0 z-20 border-b border-gray-200 bg-white shadow-sm">
        <div class="mx-auto max-w-6xl px-6 h-14
                    flex items-center justify-between">
            <div class="flex items-center gap-3">
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
            <div class="flex items-center gap-5">
                <span class="hidden sm:block text-xs text-gray-500
                             truncate max-w-[180px]">
                    {{ $currentUserName }}
                </span>
                <form method="POST" action="{{ route('system.logout') }}">
                    @csrf
                    <button type="submit"
                            class="min-h-11 py-2 px-3 text-sm text-gray-400 hover:text-red-500
                                   transition-colors duration-150 tracking-wide">
                        Abmelden
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-6 pt-14 pb-24">

        <div class="mt-4 mb-6">
            <a href="{{ route('system.dashboard') }}"
               class="inline-flex items-center gap-1.5 min-h-11 py-2 text-sm text-indigo-500
                      hover:text-indigo-700 transition-colors">
                <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg"
                     fill="none" viewBox="0 0 24 24" stroke-width="2"
                     stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M15.75 19.5 8.25 12l7.5-7.5"/>
                </svg>
                Dashboard
            </a>
        </div>

        <div class="mb-8">
            <h1 class="text-xl font-semibold tracking-tight text-gray-800">
                Galeristen-Verwaltung
            </h1>
        </div>

        @if(session('status'))
            <div class="mb-6 rounded-lg border border-amber-300
                        bg-amber-50 px-4 py-3 text-sm text-amber-700">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 rounded-lg border border-red-300
                        bg-red-50 px-4 py-3 text-sm text-red-700 space-y-1">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        {{-- ── Invite section ─────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-8">
            <h2 class="text-sm font-semibold text-gray-800 tracking-wide mb-4">
                Neuen Galeristen einladen
            </h2>
            <form method="POST"
                  action="{{ route('system.mandanten.invite') }}"
                  class="flex items-end gap-3">
                @csrf
                <div class="flex-1">
                    <label for="email"
                           class="block text-sm font-medium text-gray-700 mb-1">
                        E-Mail
                    </label>
                    <input id="email" name="email" type="email"
                           value="{{ old('email') }}"
                           placeholder="E-Mail Galerist:in"
                           required
                           class="block w-full rounded-md border-gray-300 shadow-sm
                                  text-sm focus:border-gray-500 focus:ring-gray-500">
                    @error('email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit"
                        class="flex-shrink-0 py-2 px-4 min-h-11 rounded-md text-sm font-medium
                               text-white bg-gray-800 hover:bg-gray-700 transition-colors
                               focus:outline-none focus:ring-2 focus:ring-gray-500
                               focus:ring-offset-2">
                    Einladung senden
                </button>
            </form>
        </div>

        {{-- ── Mandanten table ─────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-800 tracking-wide">
                    Galeristen
                </h2>
            </div>
            <div class="hidden md:block overflow-x-auto">
                <table class="hidden md:table w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50">
                            <th class="px-6 py-3 text-left text-xs font-medium
                                       text-gray-500 tracking-wide uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium
                                       text-gray-500 tracking-wide uppercase">Firma</th>
                            <th class="px-6 py-3 text-left text-xs font-medium
                                       text-gray-500 tracking-wide uppercase">E-Mail</th>
                            <th class="px-6 py-3 text-left text-xs font-medium
                                       text-gray-500 tracking-wide uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium
                                       text-gray-500 tracking-wide uppercase">Aktionen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($mandanten as $m)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-gray-800 whitespace-nowrap">
                                    {{ $m->mand_firstname }} {{ $m->mand_lastname }}
                                    @unless($m->active)
                                        <span class="text-gray-400 font-normal">(inaktiv)</span>
                                    @endunless
                                </td>
                                <td class="px-6 py-4 text-gray-600 whitespace-nowrap">
                                    {{ $m->mand_company }}
                                </td>
                                <td class="px-6 py-4 text-gray-600 whitespace-nowrap">
                                    {{ $m->mand_email }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($m->active && ($m->valid_to === null || $m->valid_to->gte(today())))
                                        <span class="text-xs font-medium text-green-600">Aktiv</span>
                                    @else
                                        <span class="text-xs font-medium text-red-500">Inaktiv</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <button type="button"
                                                @click="window.location='{{ route('system.mandanten.show', $m->mand_id) }}'"
                                                class="inline-flex items-center min-h-11 py-2 text-sm text-gray-500 hover:text-gray-800
                                                       transition-colors tracking-wide select-none">
                                            Ansehen
                                        </button>
                                        <button type="button"
                                                @click="window.location='{{ route('system.mandanten.edit', $m->mand_id) }}'"
                                                class="inline-flex items-center min-h-11 py-2 text-sm text-gray-500 hover:text-amber-600
                                                       transition-colors tracking-wide select-none">
                                            Bearbeiten
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile: Detailbereich + senkrechte Tab-Liste (< md) --}}
            <div class="md:hidden p-4" x-data="{
                    items: @js($mandanten->map(fn($m) => [
                        'mand_id'      => $m->mand_id,
                        'name'         => trim($m->mand_firstname . ' ' . $m->mand_lastname),
                        'company'      => $m->mand_company,
                        'email'        => $m->mand_email,
                        'statusLabel'  => ($m->active && ($m->valid_to === null || $m->valid_to->gte(today()))) ? 'Aktiv' : 'Inaktiv',
                        'statusActive' => (bool) ($m->active && ($m->valid_to === null || $m->valid_to->gte(today()))),
                        'inactive'     => ! $m->active,
                    ])->values()),
                    select(mid) {
                        const idx = this.items.findIndex(i => i.mand_id === mid);
                        if (idx > 0) {
                            const [chosen] = this.items.splice(idx, 1);
                            this.items.unshift(chosen);
                        }
                    }
                }">

                {{-- Detailbereich: ausgewählter Mandant (items[0]) --}}
                <div class="rounded-xl border border-gray-300 bg-white shadow-sm p-3 mb-3">
                    <div class="mb-2 text-center">
                        <span class="block font-medium text-gray-800 break-words">
                            <span x-text="items[0].name"></span>
                            <span x-show="items[0].inactive" class="text-gray-400 font-normal">(inaktiv)</span>
                        </span>
                        <span class="block text-sm text-gray-500 break-words" x-text="items[0].company"></span>
                        <span class="block text-sm text-gray-500 break-all" x-text="items[0].email"></span>
                        <span class="block text-sm mt-1 font-medium"
                              :class="items[0].statusActive ? 'text-green-600' : 'text-red-500'"
                              x-text="items[0].statusLabel"></span>
                    </div>

                    <div class="flex items-center justify-center gap-3 pt-1.5 border-t border-gray-100">
                        @foreach($mandanten as $m)
                            <template x-if="items[0] && items[0].mand_id === {{ $m->mand_id }}">
                                <div class="flex items-center gap-3">
                                    <button type="button"
                                            @click="window.location='{{ route('system.mandanten.show', $m->mand_id) }}'"
                                            class="inline-flex items-center min-h-11 py-2 text-sm text-indigo-500 hover:text-indigo-700
                                                   transition-colors tracking-wide">
                                        Ansehen
                                    </button>
                                    <button type="button"
                                            @click="window.location='{{ route('system.mandanten.edit', $m->mand_id) }}'"
                                            class="inline-flex items-center min-h-11 py-2 text-sm text-gray-500 hover:text-gray-700
                                                   transition-colors tracking-wide">
                                        Bearbeiten
                                    </button>
                                </div>
                            </template>
                        @endforeach
                    </div>
                </div>

                {{-- Tab-Liste: senkrecht, mind. 3 Zeilen sichtbar, Rest scrollbar --}}
                <div class="rounded-xl border border-gray-300 bg-white shadow-sm divide-y divide-gray-300
                            max-h-[132px] overflow-y-auto">
                    <template x-for="item in items" :key="item.mand_id">
                        <button type="button"
                                @click="select(item.mand_id)"
                                :class="item.mand_id === items[0].mand_id
                                        ? 'bg-indigo-50 text-indigo-700 font-medium'
                                        : 'text-gray-600'"
                                class="block w-full text-left px-4 min-h-11 py-2.5 text-sm truncate
                                       hover:bg-gray-50 transition-colors">
                            <span x-text="item.name"></span>
                        </button>
                    </template>
                </div>

            </div>

        </div>

    </main>

    <footer class="fixed bottom-0 inset-x-0 border-t border-gray-200 bg-white shadow-sm">
        <div class="mx-auto max-w-6xl px-6 h-9
                    flex items-center justify-between">
            <span class="text-[10px] font-mono tracking-widest
                         uppercase text-gray-400">
                Fotogalerie · System-Bereich
            </span>
            <span class="text-[10px] text-gray-400">Session aktiv</span>
        </div>
    </footer>

</body>
</html>
