{{--
    FILE:    resources/views/system/users/index.blade.php
    VERSION: 1.0.0

    DESCRIPTION:
      System-User management — invite new users, list existing users,
      send password reset, delete. Light theme matching dashboard.blade.php.

    DATA FROM CONTROLLER:
      $users         (Collection<SystUser>) — all system users ordered by syst_lastname
      $currentSystId (int)                  — session _syst_id of logged-in user

    ROUTES USED:
      POST   system.users.invite            — send invite email
      POST   system.users.password-reset    — send password reset email
      DELETE system.users.destroy           — delete user
      GET    system.dashboard               — back link
      POST   logout                         — Breeze logout
--}}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>System-User · Fotosite V8</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-50 text-gray-900 antialiased"
      x-data>

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
                    Fotosite&thinsp;V8
                </span>
                <span class="text-zinc-800 select-none">|</span>
                <span class="text-sm font-semibold tracking-widest
                             uppercase text-amber-600">
                    System
                </span>
            </div>

            {{-- User + Logout --}}
            <div class="flex items-center gap-5">
                <span class="hidden sm:block text-xs text-gray-500
                             truncate max-w-[180px]">
                    {{ $users->find($currentSystId)?->syst_uname ?? 'System' }}
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

    {{-- ══════════════════════════════════════════════════════
         MAIN
    ══════════════════════════════════════════════════════ --}}
    <main class="mx-auto max-w-4xl px-6 pt-14 pb-24">

        {{-- Back link --}}
        <div class="mb-6">
            <a href="{{ route('system.dashboard') }}"
               class="text-xs text-gray-400 hover:text-gray-600
                      transition-colors duration-150 tracking-wide">
                ← Dashboard
            </a>
        </div>

        {{-- Page title --}}
        <div class="mb-8">
            <h1 class="text-xl font-semibold tracking-tight text-gray-800">
                System-User
            </h1>
        </div>

        {{-- Status notice --}}
        @if(session('status'))
            <div class="mb-6 rounded-lg border border-amber-300
                        bg-amber-50 px-4 py-3 text-sm text-amber-700">
                {{ session('status') }}
            </div>
        @endif

        {{-- Error notice --}}
        @if($errors->any())
            <div class="mb-6 rounded-lg border border-red-300
                        bg-red-50 px-4 py-3 text-sm text-red-700 space-y-1">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        {{-- ── Invite section ─────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-8">

            <h2 class="text-sm font-semibold text-gray-800 tracking-wide mb-4">
                Neuen System-User einladen
            </h2>

            <form method="POST"
                  action="{{ route('system.users.invite') }}"
                  class="flex items-end gap-3">
                @csrf

                <div class="flex-1">
                    <label for="email"
                           class="block text-sm font-medium text-gray-700 mb-1">
                        E-Mail-Adresse
                    </label>
                    <input id="email" name="email" type="email"
                           value="{{ old('email') }}"
                           placeholder="name@beispiel.de"
                           required
                           class="block w-full rounded-md border-gray-300 shadow-sm
                                  text-sm focus:border-gray-500 focus:ring-gray-500">
                </div>

                <button type="submit"
                        class="flex-shrink-0 py-2 px-4 rounded-md text-sm font-medium
                               text-white bg-gray-800 hover:bg-gray-700 transition-colors
                               focus:outline-none focus:ring-2 focus:ring-gray-500
                               focus:ring-offset-2">
                    Einladung senden
                </button>
            </form>
        </div>

        {{-- ── Users table ─────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">

            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-800 tracking-wide">
                    Vorhandene System-User
                </h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50">
                            <th class="px-6 py-3 text-left text-xs font-medium
                                       text-gray-500 tracking-wide uppercase">
                                Name
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium
                                       text-gray-500 tracking-wide uppercase">
                                Benutzername
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium
                                       text-gray-500 tracking-wide uppercase">
                                E-Mail
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium
                                       text-gray-500 tracking-wide uppercase">
                                Aktionen
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($users as $user)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-gray-800 whitespace-nowrap">
                                    {{ $user->syst_firstname }} {{ $user->syst_lastname }}
                                </td>
                                <td class="px-6 py-4 text-gray-600 whitespace-nowrap">
                                    {{ $user->syst_uname }}
                                </td>
                                <td class="px-6 py-4 text-gray-600 whitespace-nowrap">
                                    {{ $user->syst_email }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">

                                        {{-- Password reset --}}
                                        <form method="POST"
                                              action="{{ route('system.users.password-reset', $user->syst_id) }}">
                                            @csrf
                                            <button type="submit"
                                                    x-on:click="if(!confirm('Reset-Mail senden?')) $event.preventDefault()"
                                                    class="text-xs text-gray-500 hover:text-amber-600
                                                           transition-colors tracking-wide">
                                                Passwort-Reset senden
                                            </button>
                                        </form>

                                        {{-- Delete (not self) --}}
                                        @if($user->syst_id !== $currentSystId)
                                            <form method="POST"
                                                  action="{{ route('system.users.destroy', $user->syst_id) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        x-on:click="if(!confirm('System-User wirklich löschen?')) $event.preventDefault()"
                                                        class="text-xs text-red-400 hover:text-red-600
                                                               transition-colors tracking-wide">
                                                    Löschen
                                                </button>
                                            </form>
                                        @endif

                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

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
                Fotosite V8 · System-Bereich
            </span>
            <span class="text-[10px] text-gray-400">
                Session aktiv
            </span>
        </div>
    </footer>

</body>
</html>
