{{--
    FILE:    resources/views/mandant/cust/index.blade.php
    VERSION: 3.2.2
    AUTHOR:  Martin Wagner
    DATE:    2026-06-08

    DESCRIPTION:
      Mitgliederliste des eingeloggten Mandanten.
      Zeigt alle CustPcode-Einträge. Spalte "Mitglied" zeigt cust_alias + E-Mail (grau).
      Alias und Sicherheitsstufe gemeinsam editierbar (PATCH). Eintrag entfernbar (DELETE).

    DATA FROM CONTROLLER:
      $custs — Collection<CustPcode> mit eager-geladenem custUser

    ROUTES USED:
      POST   /mandant/logout                      — Abmelden (route('mandant.logout'))
      GET    /mandant/kunden/einladen             — Einladungsformular (route('mandant.kunden.invite'))
      PATCH  /mandant/kunden/{id}/passcode        — Alias + Stufe ändern (route('mandant.kunden.passcode'))
      DELETE /mandant/kunden/{id}                 — Entfernen (route('mandant.kunden.destroy'))
      GET    /mandant/dashboard                   — Dashboard (route('mandant.dashboard'))
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

    @php $mandUname = \App\Models\UserDb\MandUser::find(session('_mand_id'))?->mand_uname ?? ''; @endphp

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
                    Mandant
                </span>
                <span class="text-sm text-indigo-200">{{ $mandUname }}</span>
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

        {{-- Zurück-Link --}}
        <div class="mt-4 mb-6">
            <a href="{{ route('mandant.dashboard') }}"
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
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold tracking-tight text-gray-800">
                    Mitgliederliste
                </h1>
                <p class="mt-1.5 text-sm text-zinc-600">
                    Ihre eingeladenen Mitglieder — Alias und Sicherheitsstufe editierbar.
                </p>
            </div>
            <a href="{{ route('mandant.kunden.invite') }}"
               class="inline-flex items-center gap-2 rounded-lg
                      border border-indigo-300 bg-indigo-50 px-4 py-2
                      text-sm font-medium text-indigo-700
                      hover:bg-indigo-100 hover:border-indigo-400
                      transition-colors duration-150">
                Mitglieder einladen
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
                Noch keine Mitglieder eingeladen.
            </div>
        @else
            <div class="rounded-xl border border-gray-200 bg-white overflow-hidden">
                <table class="w-full text-sm">
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
                    <tbody class="divide-y divide-gray-100">
                        @foreach($custs as $cust)
                            <tr class="hover:bg-gray-50 transition-colors duration-100">

                                {{-- Mitglied: Alias + E-Mail --}}
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-800">
                                        {{ $cust->cust_alias ?: '—' }}
                                    </div>
                                    <div class="text-xs text-gray-400 mt-0.5">
                                        {{ $cust->custUser?->cust_email ?? '—' }}
                                    </div>
                                </td>

                                {{-- Bearbeiten: Alias-Textfeld + Stufen-Dropdown + Speichern --}}
                                <td class="px-4 py-3">
                                    <form method="POST"
                                          action="{{ route('mandant.kunden.passcode', $cust->pcode_id) }}"
                                          class="flex items-center gap-2 flex-wrap">
                                        @csrf
                                        @method('PATCH')

                                        <input type="text"
                                               name="cust_alias"
                                               value="{{ $cust->cust_alias }}"
                                               required
                                               placeholder="Alias"
                                               class="rounded-lg border border-gray-300 bg-white
                                                      px-2 py-1.5 text-xs text-gray-800 shadow-sm
                                                      w-36
                                                      focus:outline-none focus:ring-2 focus:ring-indigo-400">

                                        <select name="sec_level"
                                                class="rounded-lg border border-gray-300 bg-white
                                                       px-2 py-1.5 text-xs text-gray-800 shadow-sm
                                                       focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                            @php
                                                $levels = [
                                                    1 => 'Bekannte',
                                                    2 => 'Großfamilie',
                                                    3 => 'Freunde',
                                                    4 => 'Enge Freunde & Kernfamilie',
                                                    5 => 'Vertraulich',
                                                    6 => 'Streng vertraulich',
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
                                          @submit.prevent="
                                              if (confirm('Mitglied wirklich entfernen?'))
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

    </main>

    {{-- ══════════════════════════════════════════════════════
         FOOTER
    ══════════════════════════════════════════════════════ --}}
    <footer class="fixed bottom-0 inset-x-0 border-t border-gray-200 bg-white shadow-sm">
        <div class="mx-auto max-w-5xl px-6 h-9
                    flex items-center justify-between">
            <span class="text-[10px] font-mono tracking-widest
                         uppercase text-gray-400">
                Fotogalerie · Mandanten-Bereich
            </span>
            <span class="text-[10px] text-gray-400">
                Session aktiv
            </span>
        </div>
    </footer>

</body>
</html>
