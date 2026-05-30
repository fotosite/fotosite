{{--
    FILE:    resources/views/mandant/cust/index.blade.php
    VERSION: 2.0.0
    AUTHOR:  Martin Wagner
    DATE:    2026-05-30

    DESCRIPTION:
      Kundenliste des eingeloggten Mandanten.
      Zeigt alle CustPcode-Einträge mit zugehörigem CustUser.
      Sicherheitsstufe per Dropdown änderbar (PATCH), Eintrag entfernbar (DELETE).

    DATA FROM CONTROLLER:
      $custs — Collection<CustPcode> mit eager-geladenem custUser

    ROUTES USED:
      POST   /mandant/logout                      — Abmelden (route('mandant.logout'))
      GET    /mandant/kunden/einladen             — Einladungsformular (route('mandant.kunden.invite'))
      PATCH  /mandant/kunden/{id}/passcode        — Stufe ändern (route('mandant.kunden.passcode'))
      DELETE /mandant/kunden/{id}                 — Entfernen (route('mandant.kunden.destroy'))
      GET    /mandant/dashboard                   — Dashboard (route('mandant.dashboard'))
--}}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Kundenliste · Fotosite V8</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-50 text-gray-900 antialiased"
      x-data>

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
    <main class="mx-auto max-w-5xl px-6 pt-14 pb-24">

        {{-- Seitenüberschrift --}}
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold tracking-tight text-gray-800">
                    Kundenliste
                </h1>
                <p class="mt-1.5 text-sm text-zinc-600">
                    Ihre eingeladenen Kunden und deren Sicherheitsstufe.
                </p>
            </div>
            <a href="{{ route('mandant.kunden.invite') }}"
               class="inline-flex items-center gap-2 rounded-lg
                      border border-indigo-300 bg-indigo-50 px-4 py-2
                      text-sm font-medium text-indigo-700
                      hover:bg-indigo-100 hover:border-indigo-400
                      transition-colors duration-150">
                Kunden einladen
            </a>
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
                Noch keine Kunden eingeladen.
            </div>
        @else
            <div class="rounded-xl border border-gray-200 bg-white overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50 text-left">
                            <th class="px-4 py-3 font-medium text-gray-600 text-xs uppercase
                                       tracking-wide">
                                Name
                            </th>
                            <th class="px-4 py-3 font-medium text-gray-600 text-xs uppercase
                                       tracking-wide">
                                E-Mail
                            </th>
                            <th class="px-4 py-3 font-medium text-gray-600 text-xs uppercase
                                       tracking-wide w-64">
                                Sicherheitsstufe
                            </th>
                            <th class="px-4 py-3 font-medium text-gray-600 text-xs uppercase
                                       tracking-wide w-28 text-right">
                                Aktionen
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($custs as $cust)
                            <tr class="hover:bg-gray-50 transition-colors duration-100">

                                {{-- Name --}}
                                <td class="px-4 py-3 text-gray-800 font-medium">
                                    @if($cust->custUser)
                                        {{ $cust->custUser->cust_firstname }}
                                        {{ $cust->custUser->cust_lastname }}
                                    @else
                                        <span class="text-gray-400 italic">—</span>
                                    @endif
                                </td>

                                {{-- E-Mail --}}
                                <td class="px-4 py-3 text-gray-600">
                                    @if($cust->custUser)
                                        {{ $cust->custUser->cust_email }}
                                    @else
                                        <span class="text-gray-400 italic">—</span>
                                    @endif
                                </td>

                                {{-- Sicherheitsstufe — Dropdown + Speichern --}}
                                <td class="px-4 py-3">
                                    <form method="POST"
                                          action="{{ route('mandant.kunden.passcode', $cust->pcode_id) }}"
                                          class="flex items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <select name="sec_level"
                                                class="rounded-lg border border-gray-300 bg-white
                                                       px-2 py-1.5 text-xs text-gray-800 shadow-sm
                                                       focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                            @php
                                                $levels = [
                                                    1 => 'Bekannte / Kollegen',
                                                    2 => 'Freunde',
                                                    3 => 'Großfamilie',
                                                    4 => 'Kernfamilie + enge Freunde',
                                                    5 => 'Beziehung',
                                                    6 => 'Intim',
                                                ];
                                            @endphp
                                            @foreach($levels as $val => $label)
                                                <option value="{{ $val }}"
                                                    {{ (int)$cust->cust_passcode === $val ? 'selected' : '' }}>
                                                    {{ $val }} — {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit"
                                                class="rounded-lg border border-indigo-200
                                                       bg-indigo-50 px-2.5 py-1.5 text-xs
                                                       font-medium text-indigo-700
                                                       hover:bg-indigo-100
                                                       transition-colors duration-150">
                                            Speichern
                                        </button>
                                    </form>
                                </td>

                                {{-- Entfernen --}}
                                <td class="px-4 py-3 text-right"
                                    x-data>
                                    <form method="POST"
                                          action="{{ route('mandant.kunden.destroy', $cust->pcode_id) }}"
                                          x-on:submit.prevent="
                                              $event.target.submit()
                                          "
                                          @submit.prevent="
                                              if (confirm('Kunden wirklich entfernen?'))
                                                  $el.submit()
                                          ">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="rounded-lg border border-red-200
                                                       bg-red-50 px-2.5 py-1.5 text-xs
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
        @endif

        {{-- Zurück --}}
        <div class="mt-8">
            <a href="{{ route('mandant.dashboard') }}"
               class="text-xs text-gray-400 hover:text-indigo-600
                      transition-colors duration-150 tracking-wide">
                ← Zurück zum Dashboard
            </a>
        </div>

    </main>

    {{-- ══════════════════════════════════════════════════════
         FOOTER
    ══════════════════════════════════════════════════════ --}}
    <footer class="fixed bottom-0 inset-x-0 border-t border-gray-200 bg-white shadow-sm">
        <div class="mx-auto max-w-5xl px-6 h-9
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
