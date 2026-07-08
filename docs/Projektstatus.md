**Fotosite V08**

**Projektstatus #12**

*Stand: 29. Juni 2026*

**Tag: ios_button_feedback_ok (+ ungetaggte Bugfixes 29.06.) — globale Button-Animation iOS/Android, Login-Doppel-Submit-Schutz, syst-Loeschlogik, MandAccountDeletedMail, deutsche PW-Meldungen abgeschlossen.**

# 1. Phasen-Übersicht

| **Phase** | **Inhalt** | **Status** | **Git-Tag** |
| --- | --- | --- | --- |
| Phase 1–4 | Fundament, Mand-Login, Eigenverwaltung, Einladungen, pw_list | **✓ Fertig** | p4_complete_ok |
| Phase 5 | Cust-Login (anon + registriert + 2FA + Passkey) | **✓ Fertig** | phase5_cust_login_ok |
| Phase 6 | Passkey-Infrastruktur (mand + cust) | **✓ Fertig*** | p6_passkey_ui_ok |
| Admin/Auth 16.–20.06. | Datenschutz, Adressfelder, Modals, Policy-Popup, Lösch-Mail, Welcome, FAQ | **✓ Fertig** | user_management_complete_ok |
| Bugfixes 21.–23.06. | Redirect-Loops, syst is_primary, 2FA-Fix, Dropdown, trim(), Touch, PW-Auge | **✓ Fertig** | pw_eye_ok |
| iOS/Android-Button-Animation 26.–29.06. | Globale app.css-Animation, user-select, a→button, Doppel-Submit-Schutz | **✓ Fertig** | ios_button_feedback_ok |
| Bugfixes 29.06. | syst-Loeschlogik, MandAccountDeletedMail, deutsche PW-Meldungen, PW-Hinweistexte | **✓ Fertig** | *(ungetaggt)* |
| Phase 7 | Foto-Content (Upload, Anzeige, Filter) | ⏳ Nächster Schritt | — |

** Phase 6: iOS-Passkey-Test noch ausstehend (Gerät bestellt).*

# 2. Implementierungen 26.–29.06.2026

## 2.1 Globale Button-Animation iOS/Android (ios_button_feedback_ok)

**Ausgangslage:** Auf iOS fehlte jede visuelle Tap-Rückmeldung; pro-Button gesetzte `active:`-Klassen wirkten unzuverlässig. Zusätzlich war Button-/Linktext auf iOS markierbar (Kontextmenü). Der frühere Ansatz (einzelne `active:`-Klassen + `submitted`-Pattern je Button) führte zu inkonsistentem Verhalten über ~106 Buttons in 32 Views.

**Lösung — global in `resources/css/app.css`:**

```css
* { user-select: none; -webkit-user-select: none; }
input, textarea { user-select: text; -webkit-user-select: text; }
button, input[type="submit"] {
    transition: opacity 75ms ease, transform 75ms ease;
}
button:active, input[type="submit"]:active {
    opacity: 0.75; transform: scale(0.95);
}
```

Plus `touchstart`-Listener auf `document` in `app.js` (iOS feuert `:active` sonst nicht). Erforderte `npm run build`.

**Bereinigung:** Die pro-Button gesetzten `active:opacity-75 active:scale-95 transition-all duration-75 select-none`-Klassen und das `submitted`-Pattern wurden aus allen Views entfernt — Animation kommt jetzt zentral aus dem CSS.

## 2.2 `<a>`→`<button>`-Umbauten (~30 Views)

Button-artige `<a href>`-Tags lösen auf iOS bei langem Tap das Kontextmenü aus. Alle button-gestylten Links (Zurück-Links „Einstellungen", Aktions-Links „Ansehen"/„Bearbeiten"/„Einrichten" etc.) auf `<button type="button" @click="window.location='...'">` umgebaut.

**Guard-Seiten-Sonderfall:** 7 Views mit `unsaved-changes-guard` (der nur `a[href]` abfängt) erhielten stattdessen `@click="$store.unsavedGuard.requestNav('...')"`, damit der Dirty-Check erhalten bleibt. Reine Navigations-Links (ohne Button-Styling) blieben `<a>`.

## 2.3 Doppel-Submit-Schutz Login-Buttons

Mehrfaches Antippen der Login-Buttons (cust/mand/syst) verschickte mehrere Sicherheitscode-Mails. Lösung auf den mailauslösenden Login-Buttons: `type="button"` + `@click="$el.closest('form').submit(); submitted = true"` + `:disabled="submitted"`. Reihenfolge entscheidend — erst nativ submitten, dann deaktivieren. `x-data="{ submitted: false }"` am jeweiligen `<form>`. Nur auf Login-Buttons, nicht global.

## 2.4 E-Mail-Button-Texte

Inline `user-select:none` an den `<a class="btn">`-Tags in `invite.blade.php`, `cust-invite.blade.php`, `email_change.blade.php`. **Einschränkung:** iOS Apple Mail ignoriert `user-select:none` komplett — Button-Text bleibt dort markierbar. Akzeptiert.

# 3. Bugfixes 29.06.2026 (ungetaggt)

## 3.1 syst-Löschlogik korrigiert

Die View-Bedingung für den Löschen-Button in `system/users/index.blade.php` war fehlerhaft und zeigte ihn auch nicht-primären Usern (führte zu Fehler bei Klick). Korrigiert:

```blade
@if(session('_is_primary') && ! $user->is_primary && $user->syst_id !== session('_syst_id'))
```

Damit: **primary löscht non-primaries** (nicht andere primaries, nicht sich selbst); **non-primary löscht niemanden**. Entspricht jetzt der Spezifikation.

## 3.2 MandAccountDeletedMail (neu)

Bei syst-seitiger Mandanten-Löschung erhielt der mand bisher KEINE Mail (nur verwaiste cust bekamen `CustAccountDeletedMail`). Neu:

- `app/Mail/MandAccountDeletedMail.php` — Sie-Form, Variable `$mandName`
- `resources/views/emails/mand-account-deleted.blade.php`
- `SystemMandantController@destroy`: `Mail::to($mandant->mand_email)->send(new MandAccountDeletedMail($mandant->mand_firstname))` vor der Löschung

Text: „Hallo [Vorname], Ihr Galerist:innen-Konto bei Fotogalerie wurde von der Systemadministration gelöscht."

## 3.3 Deutsche Passwort-Fehlermeldungen (7 Controller)

Alle PW-verarbeitenden Controller lieferten Laravels englische Standard-Meldungen (kein `lang/`-Verzeichnis). `messages`-Arrays ergänzt in: `CustPasswordResetController`, `MandPasswordResetController`, `CustSelfController`, `MandantSelfController`, `SystemUserController` (2 Stellen), `SystemProfileController`, `SystemMandantController`.

| Schlüssel | Deutscher Text |
| --- | --- |
| password.confirmed | Die eingegebenen Passwörter stimmen nicht überein. |
| password.min/mixed_case/numbers/symbols/uncompromised | Das Passwort erfüllt nicht die Mindestanforderungen. |
| current_password | Das eingegebene Passwort ist nicht korrekt. |

## 3.4 PW-Hinweistexte syst korrigiert

Die View-Hinweistexte (`system/users/password_reset`, `system/profile`, `system/mandanten/register`, `system/users/register`) behaupteten „14 Zeichen + Regeln" bzw. „12 Zeichen + Regeln". Die Controller erzwingen für syst aber nur `min:12` ohne weitere Regeln. Texte auf „Mindestens 12 Zeichen." korrigiert. PROJECT_CONTEXT Abschnitt 8 (Passwort-Policy-Tabelle) entsprechend richtiggestellt.

# 4. Wichtige Korrektur: system/login.blade.php

**`resources/views/system/login.blade.php` ist NICHT tot.** Die frühere Dokumentation (Altlasten 2b, Lerneffekt 17) stufte sie fälschlich als tote Datei ein. Tatsächlich ist sie die **aktive syst-Login-View**: `SystemLoginController@login` rendert `view('system.login')`, Login UND 2FA laufen über diese Datei (2FA-Block via `show_2fa`-Flash-Variable). Der `/backstage`-Ausfall am 21.06. entstand durch eine fehlerhafte Änderung, nicht weil die Datei tot wäre.

**Einzige echte tote Datei:** `resources/views/welcome.blade.php` (Breeze-Default).

# 5. Datenbankstand (29.06.2026)

| **DB** | **Änderungen seit #11** |
| --- | --- |
| userdb | Keine Schema-Änderungen |
| sessiondb | Unverändert |
| fotodb | Unverändert |
| fotoblobdb | Unverändert |

Alle Änderungen 26.–29.06. waren View-, Controller-, CSS- und Mail-Änderungen — keine DDL.

# 6. Offene Punkte

| **Priorität** | **Punkt** | **Detail** |
| --- | --- | --- |
| **Hoch** | Bugfixes 29.06. taggen | syst-Loeschlogik, MandAccountDeletedMail, deutsche PW-Meldungen — committet, noch kein Tag |
| **Hoch** | dirty-Ausblendung nachziehen | system/mandanten/index.blade.php + customer/auth/register.blade.php |
| **Hoch** | Regressionstest Android/Windows | Globale Button-Animation auf Desktop/Android prüfen |
| **Hoch** | Abnahmetest cust-Bereich | Blöcke 1–6; Tag: cust_complete_ok. Testplan liegt vor (docx + xlsx), pausiert |
| **Hoch** | Phase 7: mand-Content | ActivityGroup/Subgroup-Controller, Upload-Flow (/mandant/upload/*), AG/ASG-CRUD |
| **Mittel** | Phase 7: Cust-UI | Mandanten-Content-Seite, sec_level-Filter, mand_profile-Anzeige — NACH mand-Content |
| **Mittel** | SPF/DKIM Mailserver | E-Mail-Änderungsmails landen im Spam; aktuell nur UI-Hinweis |
| **Mittel** | Passkey-Link in Willkommensseite | Noch nicht umgesetzt |
| **Niedrig** | iOS-Passkey-Test | iPhone SE 2020 bestellt |
| **Niedrig** | iOS Apple Mail Button-Text markierbar | Akzeptierte Einschränkung, nicht per CSS lösbar |
| **Niedrig** | ModerationMail | Erst nach Content-Upload relevant |
| **Niedrig** | Tote Datei löschen | nur welcome.blade.php (Breeze-Default) |
| **Plan** | Newsletter | Eigene DB-Tabelle, kein Code derzeit |

# 7. Git-Tags (neu seit #11)

| **Tag** | **Inhalt** |
| --- | --- |
| **ios_button_feedback_ok** | **Globale iOS/Android-Button-Animation (app.css), user-select, a→button, Doppel-Submit-Schutz (29.06.)** |
| *(ungetaggt)* | Bugfixes 29.06.: syst-Loeschlogik, MandAccountDeletedMail, deutsche PW-Meldungen, PW-Hinweistexte syst min:12 |

Alle früheren Tags siehe Projektstatus #11 / PROJECT_CONTEXT Abschnitt 13.

Fotosite V08 — Projektstatus #12  |  Stand 29.06.2026
