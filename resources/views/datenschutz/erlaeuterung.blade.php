{{--
    FILE:    resources/views/datenschutz/erlaeuterung.blade.php
    VERSION: 1.1.0

    DESCRIPTION:
      Datenschutz-Erläuterung — zeigt das gerenderte HTML aus erlaeuterung.md.
      Öffentlich erreichbar (kein Auth-Check), für alle User-Typen.
      Styling via <style>-Block (kein @tailwindcss/typography installiert).
      Layout konsistent mit customer-Views: bg-gray-50, weiße Karte, indigo-Akzent.

    DATA FROM CONTROLLER:
      $html — string, gerendertes HTML aus erlaeuterung.md

    CHANGES:
      1.1.0 (2026-06-16) prose-Klassen ersetzt durch <style>-Block — typography-Plugin
             nicht installiert; lesbare Schrift/Abstände/Breite/Links explizit definiert.
--}}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Datenschutz-Erläuterung · Fotogalerie</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        #ds-inhalt {
            font-size:   1.0625rem;   /* ~17px */
            line-height: 1.75;
            color:       #374151;     /* gray-700 */
        }

        #ds-inhalt h1 {
            font-size:     1.6rem;
            font-weight:   700;
            color:         #111827;   /* gray-900 */
            margin-top:    0;
            margin-bottom: 0.75rem;
            line-height:   1.3;
        }

        #ds-inhalt h2 {
            font-size:     1.25rem;
            font-weight:   600;
            color:         #1f2937;   /* gray-800 */
            margin-top:    2rem;
            margin-bottom: 0.5rem;
            line-height:   1.35;
        }

        #ds-inhalt h3 {
            font-size:     1.05rem;
            font-weight:   600;
            color:         #1f2937;
            margin-top:    1.5rem;
            margin-bottom: 0.4rem;
        }

        #ds-inhalt p {
            margin-top:    0;
            margin-bottom: 1rem;
        }

        #ds-inhalt a {
            color:           #4f46e5;   /* indigo-600 */
            text-decoration: underline;
        }
        #ds-inhalt a:hover {
            color: #3730a3;             /* indigo-800 */
        }

        #ds-inhalt ul,
        #ds-inhalt ol {
            margin-top:    0;
            margin-bottom: 1rem;
            padding-left:  1.5rem;
        }

        #ds-inhalt ul { list-style-type: disc; }
        #ds-inhalt ol { list-style-type: decimal; }

        #ds-inhalt li {
            margin-bottom: 0.35rem;
        }

        #ds-inhalt strong { font-weight: 600; color: #1f2937; }

        #ds-inhalt hr {
            border: none;
            border-top: 1px solid #e5e7eb;  /* gray-200 */
            margin: 1.75rem 0;
        }

        #ds-inhalt blockquote {
            border-left:   3px solid #c7d2fe;  /* indigo-200 */
            padding-left:  1rem;
            margin:        0 0 1rem 0;
            color:         #6b7280;            /* gray-500 */
            font-style:    italic;
        }

        #ds-inhalt code {
            background:    #f3f4f6;
            padding:       0.1em 0.3em;
            border-radius: 3px;
            font-size:     0.9em;
        }
    </style>
</head>

<body class="min-h-screen bg-gray-50 text-gray-900 antialiased">

    {{-- ── Header ── --}}
    <header class="sticky top-0 z-20 border-b border-gray-200 bg-white shadow-sm">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 h-14 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="text-[11px] font-mono tracking-widest uppercase text-gray-400">
                    Fotogalerie
                </span>
                <span class="text-zinc-800 select-none">|</span>
                <span class="text-sm font-semibold tracking-widest uppercase text-indigo-600">
                    Datenschutz
                </span>
            </div>
            <button onclick="window.close()"
                    class="text-xs text-gray-400 hover:text-gray-600
                           transition-colors duration-150">
                Schließen ✕
            </button>
        </div>
    </header>

    {{-- ── Inhalt ── --}}
    <main class="mx-auto max-w-3xl px-4 sm:px-6 pt-8 pb-20">

        <div class="rounded-xl border border-gray-200 bg-white
                    px-6 py-8 sm:px-10 sm:py-10 shadow-sm">
            <div id="ds-inhalt">
                {!! $html !!}
            </div>
        </div>

        <p class="mt-6 text-center text-xs text-gray-400">
            <button onclick="window.close()"
                    class="hover:text-indigo-600 transition-colors duration-150">
                ← Seite schließen
            </button>
        </p>

    </main>

</body>
</html>
