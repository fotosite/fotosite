{{--
    FILE:    resources/views/customer/galerien.blade.php
    VERSION: 1.0.0
    DATE:    2026-06-12

    DESCRIPTION:
      Customer Galerien-Verwaltung — Reihenfolge, E-Mail-Benachrichtigung,
      Galerie entfernen (mit Kaskaden-Konto-Löschung beim letzten Eintrag).
      Standalone (kein Layout-Erbe). Accent-Farbe: indigo.

    DATA FROM CONTROLLER:
      $pcodes — Collection<CustPcode> mit mandUser (eager-loaded), sortiert ASC pcode_prefstat

    ROUTES USED:
      GET    customer.dashboard               — Zurück-Link
      PATCH  customer.galerien.reorder        — Reihenfolge ändern
      PATCH  customer.galerien.mailrequest    — E-Mail-Benachrichtigung umschalten
      DELETE customer.galerien.remove         — Galerie / Konto löschen
      POST   customer.logout                  — Abmelden
--}}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Meine Galerien · Fotogalerie</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-50 text-gray-900 antialiased">

    @php $custName = \App\Models\UserDb\CustUser::find(session('_cust_id'))?->cust_firstname ?? ''; @endphp

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
                    Fotogalerie
                </span>
                <span class="text-zinc-800 select-none">|</span>
                <span class="text-sm font-semibold tracking-widest
                             uppercase text-indigo-600">
                    Mitglied
                </span>
                <span class="text-sm text-indigo-200">{{ $custName }}</span>
            </div>

            {{-- Logout --}}
            <div class="flex items-center">
                <form method="POST" action="{{ route('customer.logout') }}">
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
        <div class="mt-4 mb-6">
            <a href="{{ route('customer.dashboard') }}"
               class="inline-flex items-center gap-1.5 text-xs text-indigo-500
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

        {{-- Seitenüberschrift --}}
        <div class="mb-8">
            <h1 class="text-xl font-semibold tracking-tight text-gray-800">
                Meine Galerien
            </h1>
            <p class="mt-1.5 text-sm text-zinc-600">
                Reihenfolge anpassen und E-Mail-Benachrichtigungen verwalten.
            </p>
        </div>

        {{-- Flash --}}
        @if(session('status'))
            <div class="mb-6 rounded-lg border border-indigo-200
                        bg-indigo-50 px-4 py-3 text-sm text-indigo-700">
                {{ session('status') }}
            </div>
        @endif

        {{-- ── Galerie-Liste ───────────────────────────────── --}}
        @if($pcodes->isEmpty())
            <div class="rounded-xl border border-dashed border-gray-300
                        bg-white px-6 py-10 text-center text-sm text-gray-400">
                Keine Galerien vorhanden.
            </div>
        @else
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-6 divide-y divide-gray-100">
                @foreach($pcodes as $pcode)
                    <div class="flex flex-col sm:flex-row sm:items-center
                                justify-between gap-3 px-4 py-4">

                        {{-- Galerist-Name --}}
                        <span class="text-sm font-medium text-gray-800 min-w-0 truncate">
                            {{ $pcode->mandUser?->mand_uname ?? '—' }}
                        </span>

                        <div class="flex items-center gap-3 flex-shrink-0">

                            {{-- Up-Button --}}
                            <form method="POST"
                                  action="{{ route('customer.galerien.reorder', ['pcodeId' => $pcode->pcode_id, 'direction' => 'up']) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        {{ $loop->first ? 'disabled' : '' }}
                                        class="p-1.5 rounded-md border border-gray-200 text-gray-500
                                               hover:bg-gray-50 transition-colors
                                               disabled:opacity-30 disabled:cursor-not-allowed">
                                    <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg"
                                         fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                                         stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="m4.5 15.75 7.5-7.5 7.5 7.5"/>
                                    </svg>
                                </button>
                            </form>

                            {{-- Down-Button --}}
                            <form method="POST"
                                  action="{{ route('customer.galerien.reorder', ['pcodeId' => $pcode->pcode_id, 'direction' => 'down']) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        {{ $loop->last ? 'disabled' : '' }}
                                        class="p-1.5 rounded-md border border-gray-200 text-gray-500
                                               hover:bg-gray-50 transition-colors
                                               disabled:opacity-30 disabled:cursor-not-allowed">
                                    <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg"
                                         fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                                         stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                                    </svg>
                                </button>
                            </form>

                            {{-- Mailrequest-Checkbox --}}
                            <form method="POST"
                                  action="{{ route('customer.galerien.mailrequest', ['pcodeId' => $pcode->pcode_id]) }}">
                                @csrf
                                @method('PATCH')
                                <label class="inline-flex items-center gap-2 cursor-pointer
                                              text-xs text-gray-600 select-none">
                                    <input type="checkbox"
                                           name="cust_mailrequest"
                                           value="1"
                                           {{ $pcode->cust_mailrequest ? 'checked' : '' }}
                                           onchange="this.form.submit()"
                                           class="h-4 w-4 rounded border-gray-300
                                                  text-indigo-600 focus:ring-indigo-500">
                                    <span class="hidden sm:inline">Neuigkeiten per E-Mail</span>
                                    <span class="sm:hidden">E-Mail</span>
                                </label>
                            </form>

                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- ── Galerie entfernen ───────────────────────────── --}}
        @if($pcodes->isNotEmpty())
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6"
             x-data="{
                 pcodeId: {{ $pcodes->first()->pcode_id }},
                 galerienCount: {{ $pcodes->count() }},
                 confirmAndRemove() {
                     const msg = this.galerienCount === 1
                         ? 'Achtung, du bist dabei, dein Konto zu löschen. Wenn dein Mitgliedskonto keinen Galeristen mehr hat, wird dein Fotogalerie-Konto gelöscht, und du hast keinen Zugang mehr. Bekommst du danach eine neue Einladung von einem Galeristen, musst du dann ein neues Benutzerkonto anlegen. Fortfahren?'
                         : 'Achtung, du bist dabei, einen Galeristen aus deiner Liste zu löschen. Damit sperrst du deinen Zugang zu dessen Fotos. Fortfahren?';
                     if (!window.confirm(msg)) return;
                     this.$refs.removeForm.action = '/customer/galerien/' + this.pcodeId;
                     this.$refs.removeForm.submit();
                 }
             }">

            <h2 class="text-sm font-semibold text-gray-800 tracking-wide mb-4">
                Galerie entfernen
            </h2>

            <div class="space-y-4">

                <div>
                    <label for="remove_select"
                           class="block text-sm font-medium text-gray-700 mb-1">
                        Galerist:in auswählen
                    </label>
                    <select id="remove_select"
                            x-model="pcodeId"
                            class="block w-full rounded-md border-gray-300 shadow-sm
                                   text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach($pcodes as $pcode)
                            <option value="{{ $pcode->pcode_id }}">
                                {{ $pcode->mandUser?->mand_uname ?? '—' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Hidden form — action wird per JS gesetzt --}}
                <form x-ref="removeForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                </form>

                <button type="button"
                        @click="confirmAndRemove()"
                        class="w-full flex justify-center py-3 md:py-2 px-4
                               rounded-md text-sm font-medium text-white
                               bg-red-600 hover:bg-red-700 transition-colors
                               focus:outline-none focus:ring-2
                               focus:ring-red-500 focus:ring-offset-2">
                    Galerie entfernen
                </button>

            </div>
        </div>
        @endif

    </main>

    {{-- ══════════════════════════════════════════════════════
         FOOTER
    ══════════════════════════════════════════════════════ --}}
    <footer class="fixed bottom-0 inset-x-0 border-t border-gray-200 bg-white shadow-sm">
        <div class="mx-auto max-w-3xl px-6 h-9
                    flex items-center justify-between">
            <span class="text-[10px] font-mono tracking-widest
                         uppercase text-gray-400">
                Fotogalerie · Mitglieder-Bereich
            </span>
            <span class="text-[10px] text-gray-400">
                Session aktiv
            </span>
        </div>
    </footer>

</body>
</html>
