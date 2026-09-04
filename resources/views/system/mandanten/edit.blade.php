{{--
    FILE:    resources/views/system/mandanten/edit.blade.php
    VERSION: 1.3.0
    DATE:    2026-09-04

    DESCRIPTION:
      Mandant settings edit — read-only profile card + editable settings card.
      Light theme matching dashboard.blade.php.

    DATA FROM CONTROLLER:
      $mandant         (MandUser) — the mandant record
      $currentUserName (string)   — syst_uname of logged-in system user

    ROUTES USED:
      PATCH  system.mandanten.update  — save settings
      GET    system.mandanten.show    — back link
      POST   logout                   — Breeze logout

    CHANGES: 1.3.0 (2026-09-04) Lösch-Aktion von der Liste hierher verschoben:
             neuer 'Konto endgültig löschen'-Button in der Konto-Status-Karte (nur nach
             Ablauf der Karenzzeit sichtbar), inkl. Lösch-Bestätigungsmodal und
             deleteModal im body x-data (1:1 aus index.blade.php übernommen).
             1.2.0 (2026-08-26) Straße und Hausnummer / PLZ und Ort als neue
             dt/dd-Paare ergänzt (mand_street+nr, mand_postcode+city); Feld-
             Reihenfolge im Profildaten-Block auf Benutzername/E-Mail/Vorname/
             Nachname/Straße/PLZ+Ort/Telefon/Firma umgestellt; alle vier
             optionalen Felder (Telefon/Firma/Straße/PLZ+Ort) mit
             ?? 'nicht vorhanden'-Fallback versehen, da sie jetzt echtes NULL
             enthalten können.
             1.1.1 (2026-06-25) Android-Touch-Targets vergroessert: Logout-
             Button, Zurueck-Link und Speichern-Button auf min-h-11 angehoben.
--}}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Einstellungen: {{ $mandant->mand_firstname }} {{ $mandant->mand_lastname }} · Fotogalerie</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-50 text-gray-900 antialiased"
      x-data="{
          deleteModal: { open: false, name: '', formId: null }
      }">

    <header class="sticky top-0 z-20 border-b border-gray-200 bg-white shadow-sm">
        <div class="mx-auto max-w-4xl px-6 h-14
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

    <main class="mx-auto max-w-4xl px-6 pt-14 pb-24">

        <div class="mb-6" x-data="{}">
            <button type="button"
                    @click="window.location='{{ route('system.mandanten.show', $mandant->mand_id) }}'"
                    class="inline-flex items-center min-h-11 py-2 text-sm text-gray-400 hover:text-gray-600
                           transition-colors duration-150 tracking-wide select-none">
                ← Galerist:in ansehen
            </button>
        </div>

        <div class="mb-8">
            <h1 class="text-xl font-semibold tracking-tight text-gray-800">
                Einstellungen: {{ $mandant->mand_firstname }} {{ $mandant->mand_lastname }}
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

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- ── Profildaten (read-only) ── --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <h2 class="text-sm font-semibold text-gray-800 tracking-wide mb-5">
                    Profildaten
                </h2>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                            Benutzername
                        </dt>
                        <dd class="mt-1 text-sm text-gray-800">{{ $mandant->mand_uname }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                            E-Mail
                        </dt>
                        <dd class="mt-1 text-sm text-gray-800">{{ $mandant->mand_email }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                            Vorname
                        </dt>
                        <dd class="mt-1 text-sm text-gray-800">{{ $mandant->mand_firstname }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                            Nachname
                        </dt>
                        <dd class="mt-1 text-sm text-gray-800">{{ $mandant->mand_lastname }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                            Straße und Hausnummer
                        </dt>
                        <dd class="mt-1 text-sm text-gray-800">{{ $mandant->{'mand_street+nr'} ?? 'nicht vorhanden' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                            PLZ und Ort
                        </dt>
                        <dd class="mt-1 text-sm text-gray-800">{{ $mandant->{'mand_postcode+city'} ?? 'nicht vorhanden' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                            Telefon
                        </dt>
                        <dd class="mt-1 text-sm text-gray-800">{{ $mandant->mand_tel ?? 'nicht vorhanden' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                            Firma
                        </dt>
                        <dd class="mt-1 text-sm text-gray-800">{{ $mandant->mand_company ?? 'nicht vorhanden' }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Konto-Status: aktivieren/deaktivieren --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
                <h2 class="text-sm font-semibold text-gray-800 tracking-wide mb-3">
                    Konto-Status
                </h2>
                <p class="text-sm text-gray-600 mb-4">
                    @if($mandant->active)
                        Status: <span class="font-medium text-green-600">Aktiv</span>
                    @else
                        Status: <span class="font-medium text-red-500">Inaktiv</span>
                        @if($mandant->mand_deactivated_at)
                            <span class="text-gray-400">
                                (seit {{ $mandant->mand_deactivated_at->format('d.m.Y H:i') }})
                            </span>
                        @endif
                    @endif
                </p>
                <form method="POST"
                      action="{{ route('system.mandanten.toggle-active', $mandant->mand_id) }}">
                    @csrf
                    <button type="submit"
                            x-on:click="if(!confirm('{{ $mandant->active ? 'Willst du dieses Konto deaktivieren? Der Galerist erhält eine E-Mail, die ihn darüber informiert.' : 'Willst du dieses Konto wieder aktivieren? Der Galerist erhält eine E-Mail, die ihn darüber informiert.' }}')) $event.preventDefault()"
                            class="px-4 py-2 min-h-11 text-sm font-medium rounded-lg
                                   border transition-colors
                                   @if($mandant->active)
                                       text-red-700 bg-red-50 border-red-200 hover:bg-red-100
                                   @else
                                       text-green-700 bg-green-50 border-green-200 hover:bg-green-100
                                   @endif">
                        @if($mandant->active)
                            Konto deaktivieren
                        @else
                            Konto aktivieren
                        @endif
                    </button>
                </form>

                @if(!$mandant->active && $mandant->mand_deactivated_at && $mandant->mand_deactivated_at->diffInDays(now()) >= config('mand_deactivation.grace_days'))
                    <form method="POST"
                          id="delete-form-mandant"
                          action="{{ route('system.mandanten.destroy', $mandant->mand_id) }}"
                          class="mt-3">
                        @csrf
                        @method('DELETE')
                        <button type="button"
                                x-on:click="deleteModal = { open: true, name: {{ \Illuminate\Support\Js::from(trim($mandant->mand_firstname . ' ' . $mandant->mand_lastname)) }}, formId: 'delete-form-mandant' }"
                                class="px-4 py-2 min-h-11 text-sm font-medium rounded-lg
                                       border transition-colors
                                       text-red-700 bg-red-50 border-red-200 hover:bg-red-100">
                            Konto endgültig löschen
                        </button>
                    </form>
                @endif
            </div>

            {{-- ── Einstellungen bearbeiten ── --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <h2 class="text-sm font-semibold text-gray-800 tracking-wide mb-5">
                    Einstellungen bearbeiten
                </h2>

                <form method="POST"
                      action="{{ route('system.mandanten.update', $mandant->mand_id) }}">
                    @csrf
                    @method('PATCH')

                    <div class="space-y-4">

                        {{-- valid_to --}}
                        <div>
                            <label for="valid_to"
                                   class="block text-sm font-medium text-gray-700">
                                Gültig bis
                                <span class="text-gray-400 font-normal">(leer = unbegrenzt)</span>
                            </label>
                            <input id="valid_to" name="valid_to" type="date"
                                   value="{{ old('valid_to', $mandant->valid_to?->format('Y-m-d')) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300
                                          shadow-sm text-sm
                                          focus:border-gray-500 focus:ring-gray-500">
                        </div>

                        {{-- has_public_content --}}
                        <div class="flex items-center gap-3">
                            <input type="hidden" name="has_public_content" value="0">
                            <input type="checkbox" id="has_public_content"
                                   name="has_public_content" value="1"
                                   {{ old('has_public_content', $mandant->has_public_content) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-amber-600
                                          focus:ring-amber-500">
                            <label for="has_public_content"
                                   class="text-sm font-medium text-gray-700">
                                Hat öffentliche Inhalte (Stufe 0)
                            </label>
                        </div>

                        {{-- mand_cust_2fa --}}
                        <div class="flex items-center gap-3">
                            <input type="hidden" name="mand_cust_2fa" value="0">
                            <input type="checkbox" id="mand_cust_2fa"
                                   name="mand_cust_2fa" value="1"
                                   {{ old('mand_cust_2fa', $mandant->mand_cust_2fa) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-amber-600
                                          focus:ring-amber-500">
                            <label for="mand_cust_2fa"
                                   class="text-sm font-medium text-gray-700">
                                2FA für zugeordnete Customers
                            </label>
                        </div>

                    </div>

                    <div class="mt-6">
                        <button type="submit"
                                class="w-full flex justify-center py-2 px-4 min-h-11 rounded-md
                                       text-sm font-medium text-white bg-gray-800
                                       hover:bg-gray-700 transition-colors
                                       focus:outline-none focus:ring-2
                                       focus:ring-gray-500 focus:ring-offset-2">
                            Speichern
                        </button>
                    </div>

                </form>
            </div>

        </div>

    </main>

    <footer class="fixed bottom-0 inset-x-0 border-t border-gray-200 bg-white shadow-sm">
        <div class="mx-auto max-w-4xl px-6 h-9
                    flex items-center justify-between">
            <span class="text-[10px] font-mono tracking-widest
                         uppercase text-gray-400">
                Fotogalerie · System-Bereich
            </span>
            <span class="text-[10px] text-gray-400">Session aktiv</span>
        </div>
    </footer>

    {{-- Lösch-Bestätigung Galerist --}}
    <div x-show="deleteModal.open" x-cloak
         class="fixed inset-0 bg-black bg-opacity-50
                flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl p-6 max-w-2xl w-full shadow-xl"
             @click.outside="deleteModal.open = false">
            <p class="text-base font-semibold text-gray-800 mb-4">
                Galerist:in <span x-text="deleteModal.name" class="font-bold"></span> wirklich löschen?
            </p>
            <div class="text-sm text-gray-600 mb-6 prose prose-sm max-w-none">
                {!! uiText('syst', 's_mand_delete_warnung') !!}
            </div>
            <div class="flex gap-3 justify-end">
                <button type="button"
                        @click="deleteModal.open = false"
                        class="min-h-11 py-2 px-3 text-sm text-gray-500 hover:text-gray-700">
                    Abbrechen
                </button>
                <button type="button"
                        @click="document.getElementById(deleteModal.formId).submit(); deleteModal.open = false"
                        class="px-4 py-2 min-h-11 bg-red-600 text-white text-sm rounded-lg
                               hover:bg-red-700 transition-colors">
                    Endgültig löschen
                </button>
            </div>
        </div>
    </div>

</body>
</html>
