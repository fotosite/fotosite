{{--
    FILE:    resources/views/customer/galerien.blade.php
    VERSION: 3.1.0
    DATE:    2026-06-19

    DESCRIPTION:
      Customer Galerien-Verwaltung — E-Mail-Einstellungen (Checkbox) und Reihenfolge
      (Up/Down) speichern JEWEILS SOFORT per AJAX (fetch), kein Formular, kein
      "Einstellungen speichern"-Button, kein Page-Reload. Nach jeder erfolgreichen
      Aktion erscheint ein Bestätigungs-Popup ("Einstellungen gespeichert" + OK,
      blendet zusätzlich nach 2,5s automatisch aus). Galerie entfernen weiterhin
      über natives Form-Submit mit window.confirm() (sofortige, bestätigte
      Löschaktion — kein "ungespeicherter" Zwischenzustand).
      Standalone (kein Layout-Erbe). Accent-Farbe: indigo.

    DATA FROM CONTROLLER:
      $pcodes — Collection<CustPcode> mit mandUser (eager-loaded), sortiert ASC pcode_prefstat

    ROUTES USED:
      GET    customer.dashboard                — Zurück-Link
      POST   customer.galerien.save-settings   — Mailrequest-Checkbox sofort speichern (AJAX/JSON)
      PATCH  customer.galerien.reorder         — Reihenfolge ändern (AJAX/JSON)
      DELETE customer.galerien.remove          — Galerie / Konto löschen
      POST   customer.logout                   — Abmelden

    CHANGES: 3.1.0 (2026-06-19) Bestätigungs-Popup angepasst: sichtbarer Rahmen
             (border-indigo-200, passend zum Erfolgs-Flash-Stil der Seite)
             ergänzt; OK-Button entfernt — reines Hinweis-Popup ohne
             Interaktion, blendet nur noch automatisch nach 2,5s aus.
             3.0.0 (2026-06-19) Unsaved-Changes-Guard auf dieser Seite komplett
             entfernt (kein @include('partials.unsaved-changes-guard') mehr) —
             es gibt nichts mehr, was "ungespeichert" bleiben kann, da Checkbox
             und Reihenfolge jetzt jeweils sofort per AJAX speichern statt über
             ein gemeinsames Formular mit "Einstellungen speichern"-Button.
             Mini-Reorder-Forms (form="..."-Attribut-Workaround) entfernt,
             ersetzt durch fetch() + DOM-Zeilentausch. Neues Bestätigungs-Popup
             (eigenes x-data, settings-saved-Event) nach jeder Sofortspeicherung.
             2.3.0 (2026-06-19) Bugfix Runde 4: @change/@submit auf dem
             Einstellungs-<form> wurden von Alpine nie gebunden, weil kein
             Vorfahre-Element (auch nicht <body>) ein x-data hatte. Fix:
             x-data="{}" direkt auf dem <form> ergänzt.
             2.2.0 (2026-06-19) Eigene Zurück-Link-Dirty-Logik (window.confirm)
             durch partials.unsaved-changes-guard ersetzt (Alpine.store
             'unsavedGuard'); Reihenfolge-/Entfernen-Aktionen sind sofort
             persistierte Server-Submits und setzen daher kein dirty — nur
             die E-Mail-Einstellungs-Checkboxen lösen dirty aus.
--}}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
        <div class="mt-4 mb-6" x-data="{}">
            <button type="button"
                    @click="window.location='{{ route('customer.dashboard') }}'"
                    class="inline-flex items-center gap-1.5 text-xs text-indigo-500
                           hover:text-indigo-700 transition-colors select-none">
                <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg"
                     fill="none" viewBox="0 0 24 24" stroke-width="2"
                     stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M15.75 19.5 8.25 12l7.5-7.5"/>
                </svg>
                Einstellungen
            </button>
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

            {{-- ── Galerien-Liste — Checkbox + Reihenfolge speichern sofort per AJAX ── --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-6
                        divide-y divide-gray-100"
                 id="galerien-list">
                @foreach($pcodes as $pcode)
                    <div class="flex flex-col md:flex-row md:items-center
                                md:justify-between gap-2 md:gap-4 px-4 py-4"
                         data-pcode-row
                         data-pcode-id="{{ $pcode->pcode_id }}">

                        {{-- Galerist-Name --}}
                        <span class="text-sm font-medium text-gray-800 shrink-0">
                            {{ $pcode->mandUser?->mand_uname ?? '—' }}
                        </span>

                        {{-- Checkbox E-Mail-Benachrichtigung — speichert sofort per AJAX --}}
                        <label class="flex items-center gap-2 text-sm
                                      text-gray-600 cursor-pointer select-none">
                            <input type="checkbox"
                                   data-mailrequest-checkbox
                                   data-pcode-id="{{ $pcode->pcode_id }}"
                                   @checked($pcode->cust_mailrequest)
                                   onchange="saveMailrequest(this)"
                                   class="h-4 w-4 rounded border-gray-300
                                          text-indigo-600 focus:ring-indigo-400">
                            Neuigkeiten per Email erhalten
                        </label>

                        {{-- Up/Down — speichert sofort per AJAX, kein Page-Reload --}}
                        <div class="flex gap-1 md:ml-auto">
                            <button type="button"
                                    data-reorder="up"
                                    onclick="reorderGalerie({{ $pcode->pcode_id }}, 'up', this)"
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
                            <button type="button"
                                    data-reorder="down"
                                    onclick="reorderGalerie({{ $pcode->pcode_id }}, 'down', this)"
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

    {{-- ══════════════════════════════════════════════════════
         BESTÄTIGUNGS-POPUP — nach jeder sofort gespeicherten Änderung
         (Checkbox-Toggle oder Reihenfolge). Reines Hinweis-Popup ohne
         Interaktion, blendet nach 2,5s automatisch aus. Eigenes x-data,
         unabhängig davon, ob <body> selbst ein x-data hat.
    ══════════════════════════════════════════════════════ --}}
    <div x-data="{ show: false }"
         x-show="show"
         x-cloak
         @settings-saved.window="show = true; setTimeout(() => show = false, 2500)"
         class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-[100]">
        <div class="bg-white rounded-xl border border-indigo-200 p-6 max-w-xs w-full mx-4 shadow-xl text-center">
            <p class="text-sm font-medium text-gray-800">
                Einstellungen gespeichert
            </p>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
         JAVASCRIPT — Sofortspeicherung Checkbox + Reihenfolge
    ══════════════════════════════════════════════════════ --}}
    <script>
        function csrfToken() {
            return document.querySelector('meta[name="csrf-token"]').content;
        }

        function notifySaved() {
            window.dispatchEvent(new CustomEvent('settings-saved'));
        }

        async function saveMailrequest(checkbox) {
            const pcodeId = checkbox.dataset.pcodeId;
            const checked = checkbox.checked;
            try {
                const res = await fetch('{{ route('customer.galerien.save-settings') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                    },
                    body: JSON.stringify({ pcode_id: pcodeId, mailrequest: checked }),
                });
                const data = await res.json();
                if (!res.ok || !data.success) throw new Error('save failed');
                notifySaved();
            } catch (e) {
                checkbox.checked = !checked;
                alert('Fehler beim Speichern. Bitte erneut versuchen.');
            }
        }

        function updateReorderButtonStates() {
            const rows = document.querySelectorAll('#galerien-list [data-pcode-row]');
            rows.forEach((row, i) => {
                row.querySelector('[data-reorder="up"]').disabled   = (i === 0);
                row.querySelector('[data-reorder="down"]').disabled = (i === rows.length - 1);
            });
        }

        async function reorderGalerie(pcodeId, direction, button) {
            try {
                const res = await fetch(`/customer/galerien/${pcodeId}/reorder/${direction}`, {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                    },
                });
                const data = await res.json();
                if (!res.ok || !data.success) throw new Error('reorder failed');

                const row     = button.closest('[data-pcode-row]');
                const sibling = direction === 'up' ? row.previousElementSibling : row.nextElementSibling;
                if (sibling) {
                    if (direction === 'up') {
                        row.parentNode.insertBefore(row, sibling);
                    } else {
                        row.parentNode.insertBefore(sibling, row);
                    }
                }
                updateReorderButtonStates();
                notifySaved();
            } catch (e) {
                alert('Fehler beim Verschieben. Bitte erneut versuchen.');
            }
        }
    </script>

</body>
</html>
