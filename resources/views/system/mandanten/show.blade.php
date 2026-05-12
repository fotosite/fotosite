{{--
    FILE:    resources/views/system/mandanten/show.blade.php
    VERSION: 1.0.0

    DESCRIPTION:
      Mandant detail view — read-only display of profile and settings.
      Light theme matching dashboard.blade.php.

    DATA FROM CONTROLLER:
      $mandant         (MandUser) — the mandant record
      $currentUserName (string)   — syst_uname of logged-in system user

    ROUTES USED:
      GET    system.mandanten.edit   — edit button
      GET    system.mandanten.index  — back link
      POST   logout                  — Breeze logout
--}}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $mandant->mand_firstname }} {{ $mandant->mand_lastname }} · Fotosite V8</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-50 text-gray-900 antialiased">

    <header class="sticky top-0 z-20 border-b border-gray-200 bg-white shadow-sm">
        <div class="mx-auto max-w-4xl px-6 h-14
                    flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="text-[11px] font-mono tracking-widest
                             uppercase text-gray-400">
                    Fotosite&thinsp;V8
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
                <form method="POST" action="{{ route('logout') }}">
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

    <main class="mx-auto max-w-4xl px-6 pt-14 pb-24">

        <div class="mb-6">
            <a href="{{ route('system.mandanten.index') }}"
               class="text-xs text-gray-400 hover:text-gray-600
                      transition-colors duration-150 tracking-wide">
                ← Mandantenliste
            </a>
        </div>

        <div class="mb-8 flex items-center justify-between">
            <h1 class="text-xl font-semibold tracking-tight text-gray-800">
                {{ $mandant->mand_firstname }} {{ $mandant->mand_lastname }}
            </h1>
            <a href="{{ route('system.mandanten.edit', $mandant->mand_id) }}"
               class="py-2 px-4 rounded-md text-sm font-medium text-white
                      bg-gray-800 hover:bg-gray-700 transition-colors">
                Bearbeiten
            </a>
        </div>

        @if(session('status'))
            <div class="mb-6 rounded-lg border border-amber-300
                        bg-amber-50 px-4 py-3 text-sm text-amber-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- ── Profildaten ── --}}
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
                            Telefon
                        </dt>
                        <dd class="mt-1 text-sm text-gray-800">{{ $mandant->mand_tel }}</dd>
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
                            Firma
                        </dt>
                        <dd class="mt-1 text-sm text-gray-800">{{ $mandant->mand_company }}</dd>
                    </div>
                </dl>
            </div>

            {{-- ── Einstellungen ── --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <h2 class="text-sm font-semibold text-gray-800 tracking-wide mb-5">
                    Einstellungen
                </h2>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                            Aktiv
                        </dt>
                        <dd class="mt-1 text-sm {{ $mandant->active ? 'text-green-600' : 'text-red-500' }}">
                            {{ $mandant->active ? 'Ja' : 'Nein' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                            Gültig bis
                        </dt>
                        <dd class="mt-1 text-sm text-gray-800">
                            {{ $mandant->valid_to ? $mandant->valid_to->format('d.m.Y') : 'unbegrenzt' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                            Öffentliche Inhalte
                        </dt>
                        <dd class="mt-1 text-sm text-gray-800">
                            {{ $mandant->has_public_content ? 'Ja' : 'Nein' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                            2FA für Customers
                        </dt>
                        <dd class="mt-1 text-sm text-gray-800">
                            {{ $mandant->mand_cust_2fa ? 'Ja' : 'Nein' }}
                        </dd>
                    </div>
                </dl>
            </div>

        </div>

    </main>

    <footer class="fixed bottom-0 inset-x-0 border-t border-gray-200 bg-white shadow-sm">
        <div class="mx-auto max-w-4xl px-6 h-9
                    flex items-center justify-between">
            <span class="text-[10px] font-mono tracking-widest
                         uppercase text-gray-400">
                Fotosite V8 · System-Bereich
            </span>
            <span class="text-[10px] text-gray-400">Session aktiv</span>
        </div>
    </footer>

</body>
</html>
