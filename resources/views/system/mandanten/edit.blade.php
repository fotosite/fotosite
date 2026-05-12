{{--
    FILE:    resources/views/system/mandanten/edit.blade.php
    VERSION: 1.0.0

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
--}}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Einstellungen: {{ $mandant->mand_firstname }} {{ $mandant->mand_lastname }} · Fotosite V8</title>
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
            <a href="{{ route('system.mandanten.show', $mandant->mand_id) }}"
               class="text-xs text-gray-400 hover:text-gray-600
                      transition-colors duration-150 tracking-wide">
                ← Mandant ansehen
            </a>
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

                        {{-- active --}}
                        <div class="flex items-center gap-3">
                            <input type="hidden" name="active" value="0">
                            <input type="checkbox" id="active" name="active" value="1"
                                   {{ old('active', $mandant->active) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-amber-600
                                          focus:ring-amber-500">
                            <label for="active"
                                   class="text-sm font-medium text-gray-700">
                                Aktiv
                            </label>
                        </div>

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
                                class="w-full flex justify-center py-2 px-4 rounded-md
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
                Fotosite V8 · System-Bereich
            </span>
            <span class="text-[10px] text-gray-400">Session aktiv</span>
        </div>
    </footer>

</body>
</html>
