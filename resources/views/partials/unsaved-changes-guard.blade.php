{{--
    FILE:    resources/views/partials/unsaved-changes-guard.blade.php
    VERSION: 1.3.0
    DATE:    2026-06-19

    CHANGES: 1.3.0 (2026-06-19) Strukturfix Runde 6: Modal-<div> bekommt ein
             eigenes x-data="{}". Bisher hing x-show/x-cloak auf dem Modal
             davon ab, dass IRGENDEIN Vorfahre der einbindenden Seite
             (typischerweise <body>) ein x-data hat — fehlte das (wie bei
             customer/konto.blade.php, customer/galerien.blade.php), wurde
             das Modal-<div> nie von Alpine durchlaufen: requestNav() setzte
             modalOpen zwar korrekt im Store, aber x-show reagierte nicht,
             x-cloak wurde nie entfernt. Mit eigenem x-data spannt das Modal
             seinen EIGENEN Komponenten-Baum auf und funktioniert garantiert
             in jeder einbindenden View, unabhängig von deren eigener
             Alpine-Struktur. Die @input/@change-Direktiven auf den
             jeweiligen <form>-Tags (Runde 4/5) bleiben davon unberührt und
             weiterhin nötig — das ist ein separater Scope für einen anderen
             Teil des DOM, den dieses Modal-x-data nicht abdeckt.
             1.2.0 (2026-06-19) Bugfix Runde 2 — eigentliche Ursache gefunden:
             das Modal-<div> trägt x-cloak, aber die zugehörige CSS-Regel
             [x-cloak]{display:none!important} war NIRGENDS definiert (weder
             im Partial noch im <head> von customer/konto.blade.php oder den
             anderen 6 Views — nur auth/login-modal.blade.php und
             welcome.blade.php hatten sie). Ohne diese Regel ist x-cloak ein
             wirkungsloses Attribut: das Modal-<div> (fixed inset-0 ... z-[100])
             ist ab dem ersten gerenderten Frame normal sichtbar, bis Alpine
             (als deferred type=module-Script erst NACH komplettem HTML-Parse
             ausgeführt) x-show auswertet und es per Inline-Style versteckt.
             In dieser Lücke blockierte die Seite — unabhängig vom dirty/armed-
             Zustand aus Runde 1. Der armed-Guard-Fix konnte das nicht lösen,
             weil er ein anderes Problem (verfrühtes markDirty()) adressierte,
             nicht dieses rein CSS-bedingte Rendering-Problem. Fix: Style-Regel
             jetzt direkt im Partial, VOR dem Modal-<div>, damit sie in jeder
             der 7 Views automatisch greift, ohne die einzelnen <head>-Blöcke
             anfassen zu müssen.
             1.1.0 (2026-06-19) Bugfix: Modal erschien sofort beim Laden,
             noch bevor der Nutzer etwas geändert hatte (z.B. auf
             customer/konto). Ursache: Browser-Autofill füllt Felder wie
             Vorname/Nachname/PLZ-Ort/Firma kurz nach dem Rendern automatisch
             aus und feuert dabei isTrusted-input/change-Events — genau die
             Events, an die markDirty() gebunden ist. autocomplete="off"
             verhindert das bei modernen Browsern nicht zuverlässig.
             Fix: Store bekommt ein 'armed'-Flag, das erst ~500ms nach
             Store-Init (Alpine.store init()) auf true gesetzt wird;
             markDirty() ignoriert Aufrufe davor. Da alle 7 Views denselben
             Store nutzen, behebt dieser eine zentrale Fix alle 7 Views ohne
             Änderungen an den einzelnen View-Dateien.

    DESCRIPTION:
      Wiederverwendbarer Unsaved-Changes-Guard für Einstellungs-Fenster.
      Fängt wegführende <a href>-Klicks ab, wenn ungespeicherte Änderungen
      vorliegen, und zeigt ein eigenes Bestätigungs-Modal (deutsch) statt der
      Ziel-Navigation direkt auszuführen. Zusätzlich beforeunload-Fangnetz für
      Tab-Schließen / Browser-Zurück (dort ist nur der Browser-Standardtext
      möglich, kein eigener Text).

    EINBINDUNG (pro Seite):
      1. @include('partials.unsaved-changes-guard') irgendwo im <body> einfügen
         (Position egal — Modal ist fixed/inset-0).
      2. Editierbare Felder mit @input="$store.unsavedGuard.markDirty()" bzw.
         @change="$store.unsavedGuard.markDirty()" versehen (oder beides auf
         dem umschließenden <form>-Element).
      3. Den jeweiligen Speichern-<form> mit
         @submit="$store.unsavedGuard.clearDirty()" versehen, damit der
         Submit selbst KEIN Modal auslöst und beforeunload beim Abschicken
         nicht mehr greift.
      4. Wegführende <a href>-Links (z.B. "Zurück zu Einstellungen") brauchen
         KEINE zusätzliche Anbindung — jeder Klick auf ein <a href> wird
         global abgefangen, sofern $store.unsavedGuard.dirty true ist.
         Links, die NICHT abgefangen werden sollen (z.B. externe Links,
         Logout-Buttons sind ohnehin <button> und damit nicht betroffen),
         können mit dem Attribut data-nav-guard-skip ausgenommen werden.
      5. Buttons, die nur ein Overlay/Modal öffnen (PW-/E-Mail-Änderung,
         Passkey-Gerätename-Dialog) sind <button type="button"> ohne href —
         sie lösen das Guard-Modal naturgemäß nicht aus.

    GLOBAL VERFÜGBAR (Alpine.store('unsavedGuard')):
      dirty                — bool, true sobald eine ungespeicherte Änderung vorliegt
      armed                — bool, erst ~500ms nach Store-Init true; davor
                              ignoriert markDirty() jeden Aufruf (Schutz vor
                              Browser-Autofill, das kurz nach dem Rendern
                              synthetische input/change-Events feuert)
      modalOpen            — bool, steuert Sichtbarkeit des Bestätigungs-Modals
      targetUrl            — string|null, abgefangene Ziel-URL
      markDirty()          — setzt dirty = true (no-op solange !armed)
      clearDirty()         — setzt dirty = false (nach erfolgreichem Submit)
      requestNav(url)       — wird vom globalen Klick-Handler aufgerufen;
                              navigiert direkt wenn !dirty, sonst Modal anzeigen
      confirmNav()          — Button "Weiter": navigiert zur gemerkten URL
      cancelNav()           — Button "Zurück": schließt Modal, bleibt auf Seite
--}}

{{-- x-cloak braucht diese Regel, sonst tut das Attribut nichts und das
     Modal ist sichtbar, bis Alpine geladen hat (siehe CHANGES 1.2.0). --}}
<style>[x-cloak]{display:none!important}</style>

{{-- Bestätigungs-Modal — eigenes x-data, damit dieses <div> unabhängig davon,
     ob die einbindende Seite irgendwo ein x-data hat, von Alpine als
     Komponenten-Baum durchlaufen wird (sonst bleiben x-show/x-cloak wirkungslos,
     siehe CHANGES 1.3.0). --}}
<div x-data="{}"
     x-show="$store.unsavedGuard.modalOpen" x-cloak
     class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[100]">
    <div class="bg-white rounded-xl p-6 max-w-sm w-full mx-4 shadow-xl">
        <h3 class="font-semibold text-gray-800 mb-3">
            Willst du deine Änderungen verwerfen?
        </h3>
        <div class="flex gap-3 justify-end mt-5">
            <button type="button"
                    @click="$store.unsavedGuard.cancelNav()"
                    class="px-4 py-2 text-sm font-medium text-gray-600
                           hover:text-gray-800 transition-colors">
                Zurück
            </button>
            <button type="button"
                    @click="$store.unsavedGuard.confirmNav()"
                    class="px-4 py-2 rounded-lg bg-indigo-600 text-white
                           text-sm font-semibold hover:bg-indigo-700
                           transition-colors">
                Weiter
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('unsavedGuard', {
            dirty: false,
            armed: false,
            modalOpen: false,
            targetUrl: null,

            // Schutzfenster nach Seitenaufbau: Browser-Autofill füllt
            // Felder (Vorname, Nachname, PLZ/Ort, Firma ...) oft erst kurz
            // nach dem Rendern aus und feuert dabei echte (isTrusted)
            // input/change-Events. Ohne dieses Fenster würde das fälschlich
            // als Nutzeränderung gewertet und das Modal sofort beim Laden
            // erscheinen. Echte Tastatureingaben dauern länger als 500ms.
            init() {
                setTimeout(() => {
                    this.armed = true;
                }, 500);
            },

            markDirty() {
                if (!this.armed) return;
                this.dirty = true;
            },

            clearDirty() {
                this.dirty = false;
            },

            requestNav(url) {
                if (this.dirty) {
                    this.targetUrl = url;
                    this.modalOpen = true;
                } else {
                    window.location.href = url;
                }
            },

            confirmNav() {
                this.modalOpen = false;
                this.dirty = false;
                window.location.href = this.targetUrl;
            },

            cancelNav() {
                this.modalOpen = false;
                this.targetUrl = null;
            },
        });
    });

    // Globaler Klick-Abfang für wegführende <a href>-Links.
    document.addEventListener('click', (e) => {
        const link = e.target.closest('a[href]');
        if (!link) return;
        if (link.hasAttribute('data-nav-guard-skip')) return;
        if (link.target === '_blank') return;
        const href = link.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;
        if (!window.Alpine || !Alpine.store('unsavedGuard').dirty) return;

        e.preventDefault();
        Alpine.store('unsavedGuard').requestNav(link.href);
    });

    // beforeunload-Fangnetz für Tab-Schließen / Browser-Zurück.
    window.addEventListener('beforeunload', (e) => {
        if (window.Alpine && Alpine.store('unsavedGuard').dirty) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
</script>
