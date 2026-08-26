{{--
    FILE:    resources/views/mandant/cust/index.blade.php
    VERSION: 3.9.1
    AUTHOR:  Martin Wagner
    DATE:    2026-08-26

    DESCRIPTION:
      Mitgliederliste des eingeloggten Mandanten.
      Zeigt alle CustPcode-Einträge. Spalte "Mitglied" zeigt E-Mail (Link zur
      Detailseite mandant.kunden.show) + cust_uname (grau) — Alias wird separat
      im Bearbeiten-Bereich angezeigt/editiert. Alias und Sicherheitsstufe
      gemeinsam editierbar (PATCH). Eintrag entfernbar (DELETE).
      Alias ist standardmäßig reiner Text mit "Bearbeiten"-Button (type="button");
      Klick schaltet auf ein Eingabefeld um und der Button wird zu "Speichern"
      (type="submit", löst das bestehende PATCH-Formular aus) — lokaler
      x-data="{ editing: false }"-Scope pro Zeile, umschließt Alias-Anzeige/
      -Eingabe + Button (nicht das ganze <form>; @input/@change/@submit für
      $store.unsavedGuard bleiben unverändert auf dem <form>).
      sec_level wird über ein kompaktes Custom-Dropdown (Alpine.js) statt eines
      nativen <select> gewählt; Formular-Submit bleibt klassisch (kein AJAX).
      Mobile-Card (<768px): E-Mail (Link), Uname, Alias (Anzeige/Bearbeiten),
      sec_level-Dropdown gestapelt, Bearbeiten/Speichern- + Entfernen-Buttons
      nebeneinander (Bearbeiten/Speichern-Button per HTML5 form-Attribut an das
      PATCH-Formular gebunden, liegt daher außerhalb des <form> — der
      editing-x-data-Scope sitzt deshalb auf der Karte selbst, als gemeinsamer
      Vorfahre von Formular-Inhalt und Button; Entfernen bleibt eigenes
      DELETE-Formular).
      Steuerleiste oberhalb der Liste: client-seitige Sortierung (E-Mail/
      Alias/Sicherheitsstufe, togglende Richtung, Default Alias aufsteigend)
      und Live-Suche (Alias ODER E-Mail, startsWith, case-insensitive) via
      Alpine-Komponente custListState() — siehe <script> am Dateiende. Die
      Blade-Zeilen (Formulare, CSRF, Dropdowns) bleiben unverändert im DOM;
      Sortierung verschiebt nur vorhandene Knoten (appendChild), Filter
      blendet per x-show aus.

    DATA FROM CONTROLLER:
      $custs — Collection<CustPcode> mit eager-geladenem custUser

    ROUTES USED:
      POST   /mandant/logout                      — Abmelden (route('mandant.logout'))
      GET    /mandant/kunden/einladen             — Einladungsformular (route('mandant.kunden.invite'))
      PATCH  /mandant/kunden/{id}/passcode        — Alias + Stufe ändern (route('mandant.kunden.passcode'))
      DELETE /mandant/kunden/{id}                 — Entfernen (route('mandant.kunden.destroy'))
      GET    /mandant/dashboard                   — Dashboard (route('mandant.dashboard'))

    CHANGES: 3.9.1 (2026-08-26) E-Mail-Link (Desktop-Spalte + Mobile Zeile 1)
             ohne Hover als Link erkennbar gemacht: text-indigo-600 underline
             statt text-gray-800 (Farbe/Unterstrich nur bei :hover). Desktop:
             redundantes font-medium text-gray-800 vom umschließenden <div> auf
             das <a> verschoben.
    CHANGES: 3.9.0 (2026-08-26) Spalte/Karte "Mitglied" umgebaut: E-Mail (statt
             Alias) ist jetzt der Link zur Detailseite (mandant.kunden.show),
             darunter cust_uname statt E-Mail; der separate Mobile-"Details"-Link
             aus 3.8.0 entfällt dadurch wieder. Alias-Bearbeitung im
             "Bearbeiten"-Bereich von reinem Input auf Anzeige-Text +
             Bearbeiten/Speichern-Toggle-Button umgestellt (lokaler
             x-data="{ editing: false }"-Scope pro Zeile, siehe DESCRIPTION);
             sec_level-Dropdown und Formular-Handler (CSRF, unsavedGuard)
             unverändert.
    CHANGES: 3.8.0 (2026-08-26) Link zur neuen Read-only Detailseite
             (mandant.kunden.show) ergänzt: Desktop macht den Alias in der
             "Mitglied"-Spalte klickbar, Mobile ergänzt einen "Details"-Link
             neben der E-Mail-Anzeige. Bestehende Formulare/CSRF/
             Sortier-Suchfunktion unverändert.
    CHANGES: 3.7.7 (2026-06-28) iOS Feedback: Mitglieder-einladen-Link zu Button
             umgebaut.
    CHANGES: 3.7.6 (2026-06-25) Android-Touch-Targets vergroessert: Logout-,
             Zurueck- und Einladen-Link, Sortier-Buttons, Desktop-
             sec_level-Trigger sowie Speichern/Entfernen-Buttons (Desktop)
             auf min-h-11 py-2 angehoben; betroffene text-xs auf text-sm.
    CHANGES: 3.7.5 (2026-06-23) Desktop-Dropdown-Trigger sec_level: w-9 -> w-14,
             da die schmale Breite fuer Ziffer + Chevron-Icon (3.7.4) zu eng war.
    CHANGES: 3.7.4 (2026-06-23) sec_level-Dropdown-Trigger (Mobile + Desktop)
             optisch als Combobox gekennzeichnet: Button-Inhalt von x-text
             (ueberschreibt innerHTML komplett) auf ein <span x-text="val">
             umgestellt, daneben Chevron-Down-SVG als Geschwisterelement.
             Button-Klassen um inline-flex items-center justify-between
             gap-1 px-2 py-1 ergaenzt, damit Zahl und Pfeil nebeneinander
             sitzen (border/bg/rounded waren bereits vorhanden). Nur
             bestehende Tailwind-Klassen, kein Rebuild noetig.
    CHANGES: 3.7.3 (2026-06-23) overflow-hidden auf dem Listen-Rahmen
             (rounded-xl border bg-white) entfernte das ausklappende
             sec_level-Custom-Dropdown, da dessen absolut positioniertes
             Optionsmenue ueber den Rahmenrand hinausragt und vom
             Eltern-Container abgeschnitten/unbedienbar wurde. Fix:
             overflow-hidden -> overflow-visible; abgerundete Ecken bleiben
             ueber border-radius (rounded-xl) erhalten, overflow-hidden war
             dafuer nicht erforderlich.
    CHANGES: 3.7.2 (2026-06-20) UI-Korrekturen Steuerleiste: Label "Sortieren:"
             vor den drei Sortier-Buttons ergänzt; "Suche in:"-Combobox war zu
             schmal (Optionstext von Pfeil/Rand verdeckt) — Breite auf
             min-w-[9rem] mit pr-7 (Platz für Dropdown-Pfeil) vergrößert.
    CHANGES: 3.7.1 (2026-06-20) Bugfix: x-data="custListState(@json($memberData))"
             auf dem Listen-Container war in doppelte Anführungszeichen
             gefasst, @json() erzeugt aber selbst doppelt-quotetes JSON
             ("id":1,...) — der HTML-Parser schloss das x-data-Attribut beim
             ersten " aus dem JSON vorzeitig, wodurch Alpine den Ausdruck nie
             gültig auswerten konnte (komplette Liste blieb leer, da matches()
             im kaputten Scope nicht existierte). Fix: Attribut auf einfache
             Anführungszeichen umgestellt — offizielle Laravel-Empfehlung für
             @json() in HTML-Attributen.
    CHANGES: 3.7.0 (2026-06-20) Sortier-Buttons (E-Mail/Alias/Sicherheitsstufe,
             Default Alias asc, Klick auf aktives Feld togglet Richtung,
             ↑/↓-Anzeige) + Live-Suche (Feldwahl Alias/E-Mail, startsWith,
             case-insensitive) ergänzt. $custs als $memberData (id/alias/
             email/sec_level) per @json in custListState(...) x-data
             initialisiert; localeCompare(..., 'de', {sensitivity:'base'})
             für Alias/E-Mail-Sortierung (Umlaute korrekt), numerischer
             Vergleich für sec_level. Reihenfolge wird per DOM-appendChild
             auf den bestehenden, unveränderten Blade-Zeilen umgesetzt (kein
             x-for/Neu-Rendering), damit CSRF-Tokens, route()-URLs,
             Custom-Dropdowns und form="..."-Bindung unberührt bleiben.
    CHANGES: 3.6.0 (2026-06-20) Mobile-Card-Ansicht (<768px) neu geordnet:
             Zeile 1 Alias, Zeile 2 E-Mail, Zeile 3 sec_level-Dropdown,
             Zeile 4 Speichern+Entfernen nebeneinander (flex). Stufen-Badge
             entfernt (redundant zum Dropdown). Desktop/Tablet-Ansicht
             unverändert.
    CHANGES: 3.5.0 (2026-06-20) Natives sec_level-<select> durch kompaktes
             Custom-Dropdown (Alpine x-data, Hidden-Input) ersetzt, Trigger
             zeigt nur die Stufenzahl, Liste zeigt Zahl + Bezeichnung.
             $levels einmalig vor die Mitgliederliste gehoben statt pro Zeile
             doppelt deklariert.
    CHANGES: 3.4.0 (2026-06-19) partials.unsaved-changes-guard eingebunden;
             Alias-/Sicherheitsstufe-Bearbeitungsformulare (mobile + desktop)
             markieren dirty, eigener Submit löscht dirty. Entfernen-Formulare
             bleiben ohne dirty (sofortige, bestätigte Lösch-Aktion, keine
             ungespeicherten Daten).
--}}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Mitgliederliste · Fotogalerie</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-50 text-gray-900 antialiased"
      x-data>

    @php
        $mandUname = \App\Models\UserDb\MandUser::find(session('_mand_id'))?->mand_uname ?? '';
        $levels = [
            1 => 'Bekannte',
            2 => 'Großfamilie',
            3 => 'Freunde',
            4 => 'Enge Freunde & Kernfamilie',
            5 => 'Vertraulich',
            6 => 'Streng vertraulich',
        ];
    @endphp

    {{-- ══════════════════════════════════════════════════════
         TOP BAR
    ══════════════════════════════════════════════════════ --}}
    <header class="sticky top-0 z-20 border-b border-gray-200 bg-white shadow-sm">
        <div class="mx-auto max-w-5xl px-6 h-14
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
    <main class="mx-auto max-w-5xl px-6 pt-14 pb-24">

        {{-- Zurück-Link --}}
        <div class="mt-4 mb-6">
            <button type="button"
                    @click="$store.unsavedGuard.requestNav('{{ route('mandant.dashboard') }}')"
                    class="inline-flex items-center gap-1.5 min-h-11 py-2 text-sm text-indigo-500
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
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold tracking-tight text-gray-800">
                    Mitgliederliste
                </h1>
                <p class="mt-1.5 text-sm text-zinc-600">
                    Ihre eingeladenen Mitglieder — Alias und Sicherheitsstufe editierbar.
                </p>
            </div>
            <button type="button"
                    @click="window.location='{{ route('mandant.kunden.invite') }}'"
                    class="inline-flex items-center gap-2 rounded-lg min-h-11
                           border border-indigo-300 bg-indigo-50 px-4 py-2
                           text-sm font-medium text-indigo-700
                           hover:bg-indigo-100 hover:border-indigo-400
                           transition-colors duration-150">
                Mitglieder einladen
            </button>
        </div>

        {{-- Flash-Meldung --}}
        @if(session('status'))
            <div class="mb-6 rounded-lg border border-indigo-200
                        bg-indigo-50 px-4 py-3 text-sm text-indigo-700">
                {{ session('status') }}
            </div>
        @endif

        {{-- Tabelle --}}
        @if($custs->isEmpty())
            <div class="rounded-xl border border-dashed border-gray-300
                        bg-white px-6 py-12 text-center text-sm text-gray-400">
                Noch keine Mitglieder eingeladen.
            </div>
        @else
            @php
                $memberData = $custs->map(fn($c) => [
                    'id'        => $c->pcode_id,
                    'alias'     => $c->cust_alias ?? '',
                    'email'     => $c->custUser?->cust_email ?? '',
                    'sec_level' => (int) $c->cust_passcode,
                ])->values();
            @endphp
            <div x-data='custListState(@json($memberData))'>

                {{-- Steuerleiste: Sortierung + Suche (gilt für Desktop und Mobile) --}}
                <div class="mb-3 flex flex-wrap items-center gap-3">

                    {{-- Sortier-Buttons: genau ein Feld aktiv, Klick auf aktives Feld togglet Richtung --}}
                    <div class="flex items-center gap-1.5">
                        <span class="text-sm text-gray-500 shrink-0">Sortieren:</span>
                        <button type="button"
                                @click="setSort('email')"
                                class="inline-flex items-center gap-1 rounded-lg border px-3 min-h-11 py-2 text-sm font-medium
                                       transition-colors duration-150"
                                :class="sortField === 'email'
                                    ? 'border-indigo-300 bg-indigo-50 text-indigo-700'
                                    : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300'">
                            E-Mail
                            <span x-show="sortField === 'email'" x-text="sortDir === 'asc' ? '↑' : '↓'"></span>
                        </button>
                        <button type="button"
                                @click="setSort('alias')"
                                class="inline-flex items-center gap-1 rounded-lg border px-3 min-h-11 py-2 text-sm font-medium
                                       transition-colors duration-150"
                                :class="sortField === 'alias'
                                    ? 'border-indigo-300 bg-indigo-50 text-indigo-700'
                                    : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300'">
                            Alias
                            <span x-show="sortField === 'alias'" x-text="sortDir === 'asc' ? '↑' : '↓'"></span>
                        </button>
                        <button type="button"
                                @click="setSort('sec_level')"
                                class="inline-flex items-center gap-1 rounded-lg border px-3 min-h-11 py-2 text-sm font-medium
                                       transition-colors duration-150"
                                :class="sortField === 'sec_level'
                                    ? 'border-indigo-300 bg-indigo-50 text-indigo-700'
                                    : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300'">
                            Sicherheitsstufe
                            <span x-show="sortField === 'sec_level'" x-text="sortDir === 'asc' ? '↑' : '↓'"></span>
                        </button>
                    </div>

                    {{-- Live-Suche: Feldwahl + Suchbegriff (startsWith, case-insensitive) --}}
                    <div class="flex items-center gap-2 sm:ml-auto">
                        <select x-model="searchField"
                                class="min-w-[9rem] rounded-lg border border-gray-300 bg-white pl-2 pr-7 h-8 text-xs text-gray-700
                                       focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="alias">Suche in: Alias</option>
                            <option value="email">Suche in: E-Mail</option>
                        </select>
                        <input type="text"
                               x-model="searchTerm"
                               placeholder="Suchbegriff…"
                               class="rounded-lg border border-gray-300 bg-white px-3 h-8 text-xs text-gray-700 shadow-sm
                                      focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>
                </div>

            <div class="rounded-xl border border-gray-200 bg-white overflow-visible">

                {{-- Mobile: Card-Liste --}}
                <div class="md:hidden divide-y divide-gray-100" data-member-list="mobile">
                    @foreach($custs as $cust)
                    @php $custFormId = 'cust-form-'.$cust->pcode_id; @endphp
                    <div class="p-4 space-y-2"
                         data-member-id="{{ $cust->pcode_id }}"
                         x-show="matches({{ $cust->pcode_id }})"
                         x-data="{ editing: false }">

                        {{-- Bearbeiten-Formular: Zeile 1 E-Mail, Zeile 2 Uname, Zeile 3 Alias, Zeile 4 sec_level --}}
                        <form id="{{ $custFormId }}"
                              method="POST"
                              action="{{ route('mandant.kunden.passcode', $cust->pcode_id) }}"
                              class="space-y-2"
                              @input="$store.unsavedGuard.markDirty()"
                              @change="$store.unsavedGuard.markDirty()"
                              @submit="$store.unsavedGuard.clearDirty()">
                            @csrf
                            @method('PATCH')

                            {{-- Zeile 1: E-Mail (Link zur Detailseite) --}}
                            <div class="text-sm">
                                <a href="{{ route('mandant.kunden.show', $cust->pcode_id) }}"
                                   class="font-medium text-indigo-600 underline hover:text-indigo-800">
                                    {{ $cust->custUser?->cust_email ?? '—' }}
                                </a>
                            </div>

                            {{-- Zeile 2: Uname --}}
                            <div class="text-xs text-gray-400 px-1">
                                {{ $cust->custUser?->cust_uname ?? '—' }}
                            </div>

                            {{-- Zeile 3: Alias (Anzeige/Bearbeiten-Toggle) --}}
                            <div class="flex items-center gap-2">
                                <span x-show="!editing"
                                      class="flex-1 text-sm text-gray-800">{{ $cust->cust_alias }}</span>
                                <input type="text"
                                       name="cust_alias"
                                       value="{{ $cust->cust_alias }}"
                                       required
                                       placeholder="Alias"
                                       x-show="editing"
                                       class="flex-1 rounded-lg border border-gray-300
                                              bg-white px-3 h-11 text-sm text-gray-800 shadow-sm
                                              focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            </div>

                            {{-- Zeile 4: sec_level Custom-Dropdown --}}
                            <div class="relative"
                                 x-data="{ open: false, val: {{ (int)$cust->cust_passcode }} }"
                                 @click.outside="open = false"
                                 @keydown.escape="open = false">
                                <button type="button"
                                        @click="open = !open"
                                        :aria-expanded="open"
                                        aria-haspopup="listbox"
                                        class="w-14 h-11 inline-flex items-center justify-between gap-1
                                               rounded-lg border border-gray-300 bg-white px-2 py-1
                                               text-sm font-medium text-gray-800 shadow-sm
                                               hover:border-indigo-400 focus:outline-none
                                               focus:ring-2 focus:ring-indigo-400">
                                    <span x-text="val"></span>
                                    <svg class="inline-block ml-1 w-3 h-3 text-gray-500" fill="none"
                                         stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                                <div x-show="open"
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     style="display: none;"
                                     role="listbox"
                                     class="absolute z-30 mt-1 w-56 rounded-lg border border-gray-200
                                            bg-white shadow-lg py-1">
                                    @foreach($levels as $val => $label)
                                        <button type="button"
                                                role="option"
                                                @click="val = {{ $val }}; open = false; $store.unsavedGuard.markDirty()"
                                                :class="val === {{ $val }} ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700'"
                                                class="block w-full text-left px-3 py-1.5 text-xs hover:bg-indigo-50 hover:text-indigo-700">
                                            {{ $val }} — {{ $label }}
                                        </button>
                                    @endforeach
                                </div>
                                <input type="hidden" name="sec_level" :value="val">
                            </div>
                        </form>

                        {{-- Zeile 5: Bearbeiten/Speichern + Entfernen nebeneinander --}}
                        <div class="flex gap-2">
                            <button :type="editing ? 'submit' : 'button'"
                                    form="{{ $custFormId }}"
                                    @click="if (!editing) { editing = true; $event.preventDefault(); }"
                                    x-text="editing ? 'Speichern' : 'Bearbeiten'"
                                    class="flex-1 h-11 rounded-lg border border-indigo-200
                                           bg-indigo-50 px-4 text-sm font-medium text-indigo-700
                                           hover:bg-indigo-100 transition-colors duration-150">
                            </button>
                            <form method="POST"
                                  action="{{ route('mandant.kunden.destroy', $cust->pcode_id) }}"
                                  class="flex-1"
                                  x-data
                                  @submit.prevent="
                                      if (confirm('Mitglied wirklich entfernen?'))
                                          $el.submit()
                                  ">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="w-full h-11 rounded-lg border border-red-200
                                               bg-red-50 px-4 text-sm font-medium text-red-600
                                               hover:bg-red-100 transition-colors duration-150">
                                    Entfernen
                                </button>
                            </form>
                        </div>

                    </div>
                    @endforeach
                </div>

                {{-- Desktop: Tabelle --}}
                <table class="hidden md:table w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50 text-left">
                            <th class="px-4 py-3 font-medium text-gray-600 text-xs uppercase
                                       tracking-wide w-44">
                                Mitglied
                            </th>
                            <th class="px-4 py-3 font-medium text-gray-600 text-xs uppercase
                                       tracking-wide">
                                Alias &amp; Sicherheitsstufe bearbeiten
                            </th>
                            <th class="px-4 py-3 font-medium text-gray-600 text-xs uppercase
                                       tracking-wide w-28 text-right">
                                Aktionen
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100" data-member-list="desktop">
                        @foreach($custs as $cust)
                            <tr class="hover:bg-gray-50 transition-colors duration-100"
                                data-member-id="{{ $cust->pcode_id }}"
                                x-show="matches({{ $cust->pcode_id }})">

                                {{-- Mitglied: E-Mail + Uname --}}
                                <td class="px-4 py-3">
                                    <div>
                                        <a href="{{ route('mandant.kunden.show', $cust->pcode_id) }}"
                                           class="font-medium text-indigo-600 underline hover:text-indigo-800">
                                            {{ $cust->custUser?->cust_email ?? '—' }}
                                        </a>
                                    </div>
                                    <div class="text-xs text-gray-400 mt-0.5">
                                        {{ $cust->custUser?->cust_uname ?? '—' }}
                                    </div>
                                </td>

                                {{-- Bearbeiten: Alias-Anzeige/Toggle + Stufen-Dropdown + Bearbeiten/Speichern --}}
                                <td class="px-4 py-3">
                                    <form method="POST"
                                          action="{{ route('mandant.kunden.passcode', $cust->pcode_id) }}"
                                          class="flex flex-wrap items-center gap-2"
                                          @input="$store.unsavedGuard.markDirty()"
                                          @change="$store.unsavedGuard.markDirty()"
                                          @submit="$store.unsavedGuard.clearDirty()">
                                        @csrf
                                        @method('PATCH')

                                        <div x-data="{ editing: false }" class="flex flex-wrap items-center gap-2 flex-1">
                                            <span x-show="!editing"
                                                  class="flex-1 min-w-[6rem] text-xs text-gray-800">{{ $cust->cust_alias }}</span>
                                            <input type="text"
                                                   name="cust_alias"
                                                   value="{{ $cust->cust_alias }}"
                                                   required
                                                   placeholder="Alias"
                                                   x-show="editing"
                                                   class="flex-1 min-w-[6rem] rounded-lg border border-gray-300 bg-white
                                                          px-2 py-1.5 text-xs text-gray-800 shadow-sm
                                                          focus:outline-none focus:ring-2 focus:ring-indigo-400">

                                            {{-- Custom-Dropdown sec_level: Trigger zeigt nur die Stufenzahl --}}
                                            <div class="relative shrink-0"
                                                 x-data="{ open: false, val: {{ (int)$cust->cust_passcode }} }"
                                                 @click.outside="open = false"
                                                 @keydown.escape="open = false">
                                                <button type="button"
                                                        @click="open = !open"
                                                        :aria-expanded="open"
                                                        aria-haspopup="listbox"
                                                        class="w-14 min-h-11 inline-flex items-center justify-between gap-1
                                                               rounded-lg border border-gray-300 bg-white px-2 py-2
                                                               text-sm font-medium text-gray-800 shadow-sm
                                                               hover:border-indigo-400 focus:outline-none
                                                               focus:ring-2 focus:ring-indigo-400">
                                                    <span x-text="val"></span>
                                                    <svg class="inline-block ml-1 w-3 h-3 text-gray-500" fill="none"
                                                         stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                              d="M19 9l-7 7-7-7"/>
                                                    </svg>
                                                </button>
                                                <div x-show="open"
                                                     x-transition:enter="transition ease-out duration-150"
                                                     x-transition:enter-start="opacity-0 scale-95"
                                                     x-transition:enter-end="opacity-100 scale-100"
                                                     x-transition:leave="transition ease-in duration-75"
                                                     x-transition:leave-start="opacity-100 scale-100"
                                                     x-transition:leave-end="opacity-0 scale-95"
                                                     style="display: none;"
                                                     role="listbox"
                                                     class="absolute z-30 mt-1 w-56 rounded-lg border border-gray-200
                                                            bg-white shadow-lg py-1">
                                                    @foreach($levels as $val => $label)
                                                        <button type="button"
                                                                role="option"
                                                                @click="val = {{ $val }}; open = false; $store.unsavedGuard.markDirty()"
                                                                :class="val === {{ $val }} ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700'"
                                                                class="block w-full text-left px-3 py-1.5 text-xs hover:bg-indigo-50 hover:text-indigo-700">
                                                            {{ $val }} — {{ $label }}
                                                        </button>
                                                    @endforeach
                                                </div>
                                                <input type="hidden" name="sec_level" :value="val">
                                            </div>

                                            <button :type="editing ? 'submit' : 'button'"
                                                    @click="if (!editing) { editing = true; $event.preventDefault(); }"
                                                    x-text="editing ? 'Speichern' : 'Bearbeiten'"
                                                    class="rounded-lg border border-indigo-200
                                                           bg-indigo-50 px-2.5 min-h-11 py-2 text-sm
                                                           font-medium text-indigo-700
                                                           hover:bg-indigo-100
                                                           transition-colors duration-150">
                                            </button>
                                        </div>
                                    </form>
                                </td>

                                {{-- Entfernen --}}
                                <td class="px-4 py-3 text-right"
                                    x-data>
                                    <form method="POST"
                                          action="{{ route('mandant.kunden.destroy', $cust->pcode_id) }}"
                                          @submit.prevent="
                                              if (confirm('Mitglied wirklich entfernen?'))
                                                  $el.submit()
                                          ">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="rounded-lg border border-red-200
                                                       bg-red-50 px-2.5 min-h-11 py-2 text-sm
                                                       font-medium text-red-600
                                                       hover:bg-red-100
                                                       transition-colors duration-150">
                                            Entfernen
                                        </button>
                                    </form>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
            </div>
        @endif

    </main>

    {{-- ══════════════════════════════════════════════════════
         FOOTER
    ══════════════════════════════════════════════════════ --}}
    <footer class="fixed bottom-0 inset-x-0 border-t border-gray-200 bg-white shadow-sm">
        <div class="mx-auto max-w-5xl px-6 h-9
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

    @include('partials.unsaved-changes-guard')

    {{-- Sortierung + Live-Suche der Mitgliederliste (Desktop + Mobile teilen
         sich diesen Zustand über das gemeinsame x-data="custListState(...)"
         auf dem umschließenden Listen-Container). Zeilen bleiben normale,
         serverseitig gerenderte Blade-Zeilen mit allen Formularen/CSRF-Tokens
         unverändert — Sortierung verschiebt nur vorhandene DOM-Knoten
         (appendChild), Filter blendet nur per x-show aus/ein. --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('custListState', (members) => ({
                members: members,
                sortField: 'alias',
                sortDir: 'asc',
                searchField: 'alias',
                searchTerm: '',

                setSort(field) {
                    if (this.sortField === field) {
                        this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
                    } else {
                        this.sortField = field;
                        this.sortDir = 'asc';
                    }
                    this.reorderDom();
                },

                matches(id) {
                    const term = this.searchTerm.trim().toLocaleLowerCase('de');
                    if (term === '') return true;
                    const member = this.members.find((m) => m.id === id);
                    if (!member) return false;
                    const value = (this.searchField === 'email' ? member.email : member.alias)
                        .toLocaleLowerCase('de');
                    return value.startsWith(term);
                },

                sortedIds() {
                    const list = [...this.members];
                    list.sort((a, b) => {
                        let res;
                        if (this.sortField === 'sec_level') {
                            res = a.sec_level - b.sec_level;
                        } else {
                            res = a[this.sortField].localeCompare(b[this.sortField], 'de', { sensitivity: 'base' });
                        }
                        return this.sortDir === 'asc' ? res : -res;
                    });
                    return list.map((m) => m.id);
                },

                reorderDom() {
                    this.$nextTick(() => {
                        const ids = this.sortedIds();
                        this.$root.querySelectorAll('[data-member-list]').forEach((container) => {
                            ids.forEach((id) => {
                                const row = container.querySelector(`[data-member-id="${id}"]`);
                                if (row) container.appendChild(row);
                            });
                        });
                    });
                },

                init() {
                    this.reorderDom();
                },
            }));
        });
    </script>

</body>
</html>
