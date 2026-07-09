{{--
    FILE:    resources/views/faq/index_cust.blade.php
    VERSION: 1.0.0
    DATE:    2026-06-20

    DESCRIPTION:
      FAQ & Infos — Übersichtsseite für Mitglieder (cust). Liste der
      vorhandenen .md-Dateien aus storage/app/private/faq/cust/, vertikal
      gestapelt, scrollbar. Klick auf einen Eintrag lädt den gerenderten
      Markdown-Inhalt per fetch() in ein Alpine-Modal (kein Seitenwechsel,
      kein Vorrendern aller Einträge — robuster bei wachsender Dateizahl,
      da nur der angeklickte Eintrag tatsächlich geladen wird).
      Standalone (kein Dashboard-Nav), Stil wie policy/cust_update.blade.php.

    DATA FROM CONTROLLER:
      $items — array<int, array{slug: string, label: string}>, alphabetisch
               sortiert. Leer, falls Ordner fehlt/leer (kein Fehler).

    ROUTES USED:
      GET customer.faq.show/{slug} — JSON {title, html} für das Modal (fetch)
--}}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>FAQ und Infos · Fotogalerie</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        #faq-inhalt { font-size: 0.95rem; line-height: 1.7; color: #374151; }
        #faq-inhalt h1 { font-size: 1.3rem; font-weight: 700; color: #111827; margin: 0 0 0.75rem; line-height: 1.3; }
        #faq-inhalt h2 { font-size: 1.05rem; font-weight: 600; color: #1f2937; margin: 1.25rem 0 0.5rem; }
        #faq-inhalt h3 { font-size: 1rem; font-weight: 600; color: #1f2937; margin: 1rem 0 0.4rem; }
        #faq-inhalt p { margin: 0 0 1rem; }
        #faq-inhalt a { color: #4f46e5; text-decoration: underline; }
        #faq-inhalt a:hover { color: #3730a3; }
        #faq-inhalt ul, #faq-inhalt ol { margin: 0 0 1rem; padding-left: 1.5rem; }
        #faq-inhalt ul { list-style-type: disc; }
        #faq-inhalt ol { list-style-type: decimal; }
        #faq-inhalt li { margin-bottom: 0.35rem; }
        #faq-inhalt strong { font-weight: 600; color: #1f2937; }
    </style>
</head>

<body class="min-h-screen bg-gray-50 text-gray-900 antialiased"
      x-data="{ open: false, loading: false, title: '', html: '' }">

    <div class="min-h-screen flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-lg bg-white rounded-xl border border-gray-200
                    shadow-sm px-8 py-8">

            <div class="mb-6">
                <span class="text-[11px] font-mono tracking-widest uppercase text-gray-400">
                    Fotogalerie · Mitglied
                </span>
            </div>

            <button type="button"
                    @click="window.location='{{ route('customer.dashboard') }}'"
                    class="inline-flex items-center gap-1.5 text-xs text-indigo-500
                           hover:text-indigo-700 transition-colors mb-4 select-none">
                <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg"
                     fill="none" viewBox="0 0 24 24" stroke-width="2"
                     stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M15.75 19.5 8.25 12l7.5-7.5"/>
                </svg>
                Zurück zu den Einstellungen
            </button>

            <h1 class="text-xl font-semibold tracking-tight text-gray-800 mb-4">
                FAQ und Infos
            </h1>

            @if(empty($items))
                <p class="text-sm text-gray-400">
                    Aktuell sind keine FAQ-Einträge vorhanden.
                </p>
            @else
                <div class="max-h-[70vh] overflow-y-auto space-y-2 pr-1">
                    @foreach($items as $item)
                        <button type="button"
                                @click="
                                    open = true;
                                    loading = true;
                                    title = @js($item['label']);
                                    html = '';
                                    fetch('{{ route('customer.faq.show', $item['slug']) }}')
                                        .then(r => r.json())
                                        .then(d => { html = d.html; loading = false; })
                                        .catch(() => { html = '<p>Fehler beim Laden.</p>'; loading = false; })
                                "
                                class="w-full text-left px-4 py-3 text-sm font-medium text-gray-700
                                       bg-white border border-gray-200 rounded-lg
                                       hover:border-indigo-300 hover:bg-indigo-50/40
                                       transition-colors duration-150">
                            {{ $item['label'] }}
                        </button>
                    @endforeach
                </div>
            @endif

        </div>
    </div>

    {{-- Modal: FAQ-Inhalt --}}
    <div x-show="open" x-cloak
         class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 px-4">
        <div class="bg-white rounded-xl p-6 max-w-lg w-full max-h-[85vh] overflow-y-auto shadow-xl"
             @click.outside="open = false">
            <h3 class="font-semibold text-gray-800 mb-4" x-text="title"></h3>

            <p x-show="loading" class="text-sm text-gray-400">Wird geladen…</p>

            <div id="faq-inhalt" x-show="!loading" x-html="html"></div>

            <div class="mt-6 flex justify-end">
                <button type="button" @click="open = false"
                        class="px-4 py-2 text-sm font-medium text-white
                               bg-indigo-600 rounded-lg hover:bg-indigo-700
                               transition-colors">
                    Schließen
                </button>
            </div>
        </div>
    </div>

</body>
</html>
