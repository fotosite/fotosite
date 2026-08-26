{{--
    FILE:    resources/views/mandant/cust/show.blade.php
    VERSION: 1.0.0
    AUTHOR:  Martin Wagner
    DATE:    2026-08-26

    DESCRIPTION:
      Read-only Detailseite eines Mitglieds für den eingeloggten Mandanten —
      analog zu system/mandanten/show.blade.php (gleicher Aufbau/Stil,
      dt/dl-Karte), aber im Galerist:innen-Theme (indigo statt amber) und ohne
      Bearbeiten-Formular auf dieser Seite (Bearbeitung bleibt inline in
      mandant/cust/index.blade.php).

    DATA FROM CONTROLLER:
      $cust (CustPcode) — mit eager-geladenem custUser

    ROUTES USED:
      GET  mandant.kunden.index — Zurück-Link
      POST mandant.logout       — Abmelden

    CHANGES: 1.0.0 (2026-08-26) Erstversion.
--}}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $cust->custUser?->cust_firstname }} {{ $cust->custUser?->cust_lastname }} · Fotogalerie</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-50 text-gray-900 antialiased" x-data>

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

        {{-- Zurück-Link --}}
        <div class="mb-6">
            <button type="button"
                    @click="window.location='{{ route('mandant.kunden.index') }}'"
                    class="inline-flex items-center min-h-11 py-2 text-sm text-gray-400 hover:text-gray-600
                           transition-colors duration-150 tracking-wide select-none">
                ← Mitgliederliste
            </button>
        </div>

        <div class="mb-8">
            <h1 class="text-xl font-semibold tracking-tight text-gray-800">
                {{ $cust->custUser?->cust_firstname }} {{ $cust->custUser?->cust_lastname }}
            </h1>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <h2 class="text-sm font-semibold text-gray-800 tracking-wide mb-5">
                Profildaten
            </h2>
            <dl class="space-y-3">
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                        Alias
                    </dt>
                    <dd class="mt-1 text-sm text-gray-800">{{ $cust->cust_alias }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                        E-Mail
                    </dt>
                    <dd class="mt-1 text-sm text-gray-800">{{ $cust->custUser?->cust_email ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                        Vorname
                    </dt>
                    <dd class="mt-1 text-sm text-gray-800">{{ $cust->custUser?->cust_firstname ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                        Nachname
                    </dt>
                    <dd class="mt-1 text-sm text-gray-800">{{ $cust->custUser?->cust_lastname ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                        Straße und Hausnummer
                    </dt>
                    <dd class="mt-1 text-sm text-gray-800">{{ $cust->custUser?->{'cust_street+nr'} ?? 'nicht vorhanden' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                        PLZ und Ort
                    </dt>
                    <dd class="mt-1 text-sm text-gray-800">{{ $cust->custUser?->cust_postcode_city ?? 'nicht vorhanden' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                        Telefon
                    </dt>
                    <dd class="mt-1 text-sm text-gray-800">{{ $cust->custUser?->cust_tel ?? 'nicht vorhanden' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                        Firma
                    </dt>
                    <dd class="mt-1 text-sm text-gray-800">{{ $cust->custUser?->cust_company ?? 'nicht vorhanden' }}</dd>
                </div>
            </dl>
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
