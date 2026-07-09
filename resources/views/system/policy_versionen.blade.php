{{--
    FILE:    resources/views/system/policy_versionen.blade.php
    VERSION: 1.0.0
    DATE:    2026-06-18

    DESCRIPTION:
      Syst-Verwaltung der globalen Policy-Versionen (Datenschutz, Upload-
      Bedingungen). Standalone (kein Layout-Erbe), gleiches Strukturmuster wie
      system/dashboard.blade.php. Accent-Farbe: amber (System-Bereich).

    DATA FROM CONTROLLER:
      $dsVersion     — string, aktuelle ds_version
      $uploadVersion — string, aktuelle upload_version

    ROUTES USED:
      GET  system.dashboard               — Zurück-Link
      POST system.policy.increment-ds     — DS-Version erhöhen
      POST system.policy.increment-upload — Upload-Version erhöhen
--}}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Policy-Versionen · Fotogalerie</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-50 text-gray-900 antialiased">

    {{-- ══════════════════════════════════════════════════════
         TOP BAR
    ══════════════════════════════════════════════════════ --}}
    <header class="sticky top-0 z-20 border-b border-gray-200 bg-white shadow-sm">
        <div class="mx-auto max-w-3xl px-6 h-14
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
        </div>
    </header>

    <main class="mx-auto max-w-3xl px-6 pt-10 pb-24">

        {{-- Zurück-Link --}}
        <div class="mt-4 mb-6" x-data="{}">
            <button type="button"
                    @click="window.location='{{ route('system.dashboard') }}'"
                    class="inline-flex items-center gap-1.5 text-xs text-amber-600
                           hover:text-amber-700 transition-colors select-none">
                <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg"
                     fill="none" viewBox="0 0 24 24" stroke-width="2"
                     stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M15.75 19.5 8.25 12l7.5-7.5"/>
                </svg>
                Dashboard
            </a>
        </div>

        <div class="mb-8">
            <h1 class="text-xl font-semibold tracking-tight text-gray-800">
                Policy-Versionen
            </h1>
            <p class="mt-1.5 text-sm text-zinc-600">
                Erhöht eine Version, sehen alle betroffenen User beim nächsten
                Login das Bestätigungs-Popup.
            </p>
        </div>

        @if(session('status'))
            <div class="mb-6 rounded-lg border border-amber-200
                        bg-amber-50 px-4 py-3 text-sm text-amber-800">
                {{ session('status') }}
            </div>
        @endif

        {{-- DS-Version --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6
                    flex items-center justify-between gap-4">
            <div>
                <h2 class="text-sm font-semibold text-gray-800 tracking-wide mb-1">
                    Datenschutzerklärung
                </h2>
                <p class="text-sm text-gray-500">
                    Aktuelle Version: <span class="font-mono font-medium text-gray-800">{{ $dsVersion }}</span>
                </p>
            </div>
            <form method="POST" action="{{ route('system.policy.increment-ds') }}">
                @csrf
                <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-amber-700
                               bg-amber-50 border border-amber-200 rounded-lg
                               hover:bg-amber-100 transition-colors whitespace-nowrap">
                    DS-Version erhöhen
                </button>
            </form>
        </div>

        {{-- Upload-Version --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6
                    flex items-center justify-between gap-4">
            <div>
                <h2 class="text-sm font-semibold text-gray-800 tracking-wide mb-1">
                    Upload-Bedingungen
                </h2>
                <p class="text-sm text-gray-500">
                    Aktuelle Version: <span class="font-mono font-medium text-gray-800">{{ $uploadVersion }}</span>
                </p>
            </div>
            <form method="POST" action="{{ route('system.policy.increment-upload') }}">
                @csrf
                <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-amber-700
                               bg-amber-50 border border-amber-200 rounded-lg
                               hover:bg-amber-100 transition-colors whitespace-nowrap">
                    Upload-Version erhöhen
                </button>
            </form>
        </div>

    </main>

</body>
</html>
