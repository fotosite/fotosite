{{--
    FILE:    resources/views/customer/galerien.blade.php
    VERSION: 2.0.0
    DATE:    2026-06-13

    DESCRIPTION:
      Customer Galerien-Verwaltung — E-Mail-Einstellungen (ein gemeinsames Formular),
      Reihenfolge per Up/Down (separate Formulare via HTML form-Attribut, da HTML
      keine verschachtelten <form>-Elemente erlaubt), Galerie entfernen.
      Standalone (kein Layout-Erbe). Accent-Farbe: indigo.

    DATA FROM CONTROLLER:
      $pcodes — Collection<CustPcode> mit mandUser (eager-loaded), sortiert ASC pcode_prefstat

    ROUTES USED:
      GET    customer.dashboard                — Zurück-Link (mit Dirty-Check)
      POST   customer.galerien.save-settings   — E-Mail-Einstellungen speichern
      PATCH  customer.galerien.reorder         — Reihenfolge ändern
      DELETE customer.galerien.remove          — Galerie / Konto löschen
      POST   customer.logout                   — Abmelden
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
         MAIN — x-data für Dirty-Tracking (Scope für settingsForm + Zurück-Link)
    ══════════════════════════════════════════════════════ --}}
    <main class="mx-auto max-w-3xl px-6 pt-10 pb-24"
          x-data="{ dirty: false }">

        {{-- Zurück-Link mit Dirty-Check --}}
        <div class="mt-4 mb-6">
            <a href="{{ route('customer.dashboard') }}"
               @click.prevent="
                   if (dirty) {
                       if (confirm('Einstellungen nicht gespeichert. Willst du diese jetzt speichern?')) {
                           $refs.settingsForm.submit();
                       } else {
                           window.location.href = '{{ route('customer.dashboard') }}';
                       }
                   } else {
                       window.location.href = '{{ route('customer.dashboard') }}';
                   }
               "
               class="inline-flex items-center gap-1.5 text-xs text-indigo-500
                      hover:text-indigo-700 transition-colors cursor-pointer">
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

        @if($pcodes->isEmpty())
            <div class="rounded-xl border border-dashed border-gray-300
                        bg-white px-6 py-10 text-center text-sm text-gray-400">
                Keine Galerien vorhanden.
            </div>
        @else

            {{-- ── Mini-Formulare für Reorder ──────────────────────────── --}}
            {{-- HTML erlaubt keine verschachtelten <form>-Elemente.        --}}
            {{-- Reorder-Buttons im Layout nutzen das form="..."-Attribut   --}}
            {{-- um diese Formulare zu referenzieren (HTML5-Standard).      --}}
            @foreach($pcodes as $pcode)
                <form id="reorder-up-{{ $pcode->pcode_id }}"
                      method="POST"
                      action="{{ route('customer.galerien.reorder', ['pcodeId' => $pcode->pcode_id, 'direction' => 'up']) }}">
                    @csrf
                    @method('PATCH')
                </form>
                <form id="reorder-down-{{ $pcode->pcode_id }}"
                      method="POST"
                      action="{{ route('customer.galerien.reorder', ['pcodeId' => $pcode->pcode_id, 'direction' => 'down']) }}">
                    @csrf
                    @method('PATCH')
                </form>
            @endforeach

            {{-- ── Einstellungs-Formular ───────────────────────────────── --}}
            <form method="POST"
                  action="{{ route('customer.galerien.save-settings') }}"
                  x-ref="settingsForm"
                  @change="dirty = true">
                @csrf

                <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-6
                            divide-y divide-gray-100">
                    @foreach($pcodes as $pcode)
                        <div class="flex flex-col md:flex-row md:items-center
                                    md:justify-between gap-2 md:gap-4 px-4 py-4">

                            {{-- Galerist-Name --}}
                            <span class="text-sm font-medium text-gray-800 shrink-0">
                                {{ $pcode->mandUser?->mand_uname ?? '—' }}
                            </span>

                            {{-- Checkbox E-Mail-Benachrichtigung --}}
                            <label class="flex items-center gap-2 text-sm
                                          text-gray-600 cursor-pointer select-none">
                                <input type="checkbox"
                                       name="mailrequest_{{ $pcode->pcode_id }}"
                                       value="1"
                                       @checked($pcode->cust_mailrequest)
                                       class="h-4 w-4 rounded border-gray-300
                                              text-indigo-600 focus:ring-indigo-400">
                                Neuigkeiten per Email erhalten
                            </label>

                            {{-- Up/Down via form-Attribut (kein Nesting in settingsForm) --}}
                            <div class="flex gap-1 md:ml-auto">
                                <button type="submit"
                                        form="reorder-up-{{ $pcode->pcode_id }}"
                                        {{ $loop->first ? 'disabled' : '' }}
                                        class="p-1.5 rounded-md border border-gray-200
                                               text-gray-500 hover:bg-gray-50
                                               transition-colors
                                               disabled:opacity-30 disabled:cursor-not-allowed">
                                    <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg"
                                         fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                                         stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="m4.5 15.75 7.5-7.5 7.5 7.5"/>
                                    </svg>
                                </button>
                                <button type="submit"
                                        form="reorder-down-{{ $pcode->pcode_id }}"
                                        {{ $loop->last ? 'disabled' : '' }}
                                        class="p-1.5 rounded-md border border-gray-200
                                               text-gray-500 hover:bg-gray-50
                                               transition-colors
                                               disabled:opacity-30 disabled:cursor-not-allowed">
                                    <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg"
                                         fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                                         stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                                    </svg>
                                </button>
                            </div>

                        </div>
                    @endforeach
                </div>

                <button type="submit"
                        class="w-full md:w-auto rounded-lg bg-indigo-600 px-5
                               py-3 md:py-2 text-sm font-semibold text-white
                               hover:bg-indigo-700 transition-colors
                               focus:outline-none focus:ring-2
                               focus:ring-indigo-500 focus:ring-offset-2">
                    Einstellungen speichern
                </button>

            </form>

            {{-- ── Galerie entfernen ───────────────────────────── --}}
            <div class="mt-8 bg-white rounded-xl border border-gray-200 shadow-sm p-6"
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
