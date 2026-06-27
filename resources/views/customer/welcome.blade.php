{{--
    FILE:    resources/views/customer/welcome.blade.php
    VERSION: 1.0.1
    DATE:    2026-06-20

    DESCRIPTION:
      Blockierende Willkommensseite für Mitglieder (cust) — wird beim ersten
      Login angezeigt (cust_user.show_welcome = 1). Standalone (kein
      Dashboard-Nav), Stil wie policy/cust_update.blade.php. Markdown-Inhalt
      aus storage/app/private/willkommen_cust.md, von WelcomeScreenController
      via CommonMarkConverter zu HTML gerendert.

    DATA FROM CONTROLLER:
      $html — string, gerendertes Markdown-HTML

    ROUTES USED:
      POST customer.welcome.confirm — Bestätigung absenden ("Gelesen")

    STYLING:
      Markdown-Inhalt via <style>-Block (#welcome-inhalt) statt prose-Klassen —
      @tailwindcss/typography ist nicht installiert (gleiches Muster wie
      datenschutz/erlaeuterung.blade.php).

    NAMING-HINWEIS:
      Eigener View-Pfad customer.welcome (resources/views/customer/welcome.blade.php),
      kollidiert NICHT mit dem Breeze-Default resources/views/welcome.blade.php
      (View-Name "welcome", anderer Namespace-Pfad) — bewusst geprüft, keine
      Überschreibung.
--}}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Willkommen · Fotogalerie</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        #welcome-inhalt { font-size: 0.95rem; line-height: 1.7; color: #374151; }
        #welcome-inhalt h1 { font-size: 1.4rem; font-weight: 700; color: #111827; margin: 0 0 0.75rem; line-height: 1.3; }
        #welcome-inhalt h2 { font-size: 1.1rem; font-weight: 600; color: #1f2937; margin: 1.5rem 0 0.5rem; }
        #welcome-inhalt h3 { font-size: 1rem; font-weight: 600; color: #1f2937; margin: 1.25rem 0 0.4rem; }
        #welcome-inhalt p { margin: 0 0 1rem; }
        #welcome-inhalt a { color: #4f46e5; text-decoration: underline; }
        #welcome-inhalt a:hover { color: #3730a3; }
        #welcome-inhalt ul, #welcome-inhalt ol { margin: 0 0 1rem; padding-left: 1.5rem; }
        #welcome-inhalt ul { list-style-type: disc; }
        #welcome-inhalt ol { list-style-type: decimal; }
        #welcome-inhalt li { margin-bottom: 0.35rem; }
        #welcome-inhalt strong { font-weight: 600; color: #1f2937; }
    </style>
</head>

<body class="min-h-screen bg-gray-50 text-gray-900 antialiased">

    <div class="min-h-screen flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-2xl bg-white rounded-xl border border-gray-200
                    shadow-sm px-8 py-8 max-h-[85vh] overflow-y-auto">

            <div class="mb-6">
                <span class="text-[11px] font-mono tracking-widest uppercase text-gray-400">
                    Fotogalerie · Mitglied
                </span>
            </div>

            <div id="welcome-inhalt">
                {!! $html !!}
            </div>

            <form method="POST" action="{{ route('customer.welcome.confirm') }}" class="border-t border-gray-100 pt-6 mt-2">
                @csrf
                <button type="submit"
                        class="w-full flex justify-center rounded-lg bg-indigo-600
                               px-4 py-3 md:py-2 text-sm font-semibold text-white
                               hover:bg-indigo-700 transition-colors focus:outline-none
                               focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                               active:opacity-75 active:scale-95 transition-all duration-75">
                    Gelesen
                </button>
            </form>

        </div>
    </div>

</body>
</html>
