**Konzept Fotosite V8 #4 — aktualisierte Fassung**

*Ursprünglich: 2026-06-07 · Korrigiert: 2026-06-20 · Aktualisiert: 2026-07-07 · Aktualisiert: 2026-07-19*

**Diese Fassung synchronisiert das Konzept vollständig mit dem aktuellen Implementierungsstand (PROJECT_CONTEXT.md, Projektstatus #13, Notfall-Startdokument, Inkonsistenzen.md — Stand 19.07.2026). Die früheren Fassungen (#3, Stand 07.07.; #2, Stand 26.06.) sowie das Original (2026-06-07) bleiben als historische Dokumente unverändert in den Projektdateien. Maßgeblich bei jedem weiteren Widerspruch ist PROJECT_CONTEXT.md, nicht dieses Konzeptdokument.**

# Summary

Die Website dient der Anzeige von Fotos. Die Fotos werden von mehreren Mandanten bereitgestellt. Cust-User (UI: „Mitglieder") können die Fotos im Rahmen der zugewiesenen Rechte ansehen.

Coding wird wo immer möglich an Claude Code delegiert. Es gibt keine standardmäßigen Laravel-Migrationen für Domain-Tabellen — die DDL wird direkt auf dem Server verwaltet.

Das Benutzer- und Sicherheitsverwaltungssystem (Login, 2FA, Passkeys, Trusted-Device-Auto-Login, Einladungen, Datenschutz, Selbstverwaltung) ist implementiert (Meilenstein-Tag `user_management_complete_ok`, 20.06.2026, seither um mehrere Bugfix- und Erweiterungsrunden ergänzt, zuletzt 19.07.2026 — u.a. vollständiger passwortloser Auto-Login über vertrauenswürdige Geräte, siehe Abschnitt „Website Login"). **Einschränkung (Korrektur 19.07.):** Die Passkey-Funktionalität (Phase 6) ist technisch vollständig implementiert, wurde aber bisher nur punktuell und nicht systematisch getestet — ein gründlicher Gesamttest der Passkey-Funktionalität ist der **nächste anstehende Schritt** und soll im neuen Chat erfolgen, noch vor Fortsetzung von Phase 7 (Foto-Content: Upload, Verwaltung, Anzeige).

# Datenhaltung

Vier separate MariaDB-Datenbanken, jede mit eigener Laravel-Connection und eigenem DB-User: `userdb`, `sessiondb`, `fotodb`, `fotoblobdb`. Keine Cross-DB-Joins — Verknüpfung ausschließlich über `mand_id` / `cust_id` / `fo_id` in PHP.

Datenbank (`sessiondb`) dient der Session-Verwaltung, Kurzzeit-Kennwörtern (`pw_list`), 2FA-Codes (`twofa_code`) und der führenden Mitglieder-Einladungstabelle (`cust_invite`). Sicherheitsstufen werden nicht in einer eigenen Datenbank verwaltet, sondern als Feld `*_sec_level` (TINYINT UNSIGNED, 0–6) direkt in den Content-Tabellen (`fotodb`) gespeichert.

Datenbank (`fotoblobdb`) dient der Speicherung von Fotoobjekten der Sicherheitsstufe 6 („Streng vertraulich") als BLOB. Alle anderen Stufen (0–5) werden als Datei im Dateisystem abgelegt.

Datenbank (`userdb`) dient der Speicherung der Useraccounts (syst, mand, cust), der Sicherheitsstufen-Zuordnung je Mitglied (`cust_pcode`), Einladungen/Reset-Tokens (`invite`) und Passkeys (`passkey`).

Datenbank (`fotodb`) dient der Speicherung und Administration des Mandanten-Content: Aktivitätengruppen, Subaktivitätengruppen, Fotoobjekte, Mandanten-Profilseite. Bei Content der Sicherheitsstufe 6 werden nur die Binärdaten in `fotoblobdb` gespeichert, alle weiteren Eigenschaften bleiben in `fotodb`. Die vollständige Spezifikation des Zusammenspiels erfolgt schrittweise im Rahmen von Phase 7.

Der einzige echte Foreign Key innerhalb `fotodb` ist `activity_subgroup.ag_id → activity_group.ag_id`. Zusätzlich existiert `userdb.cust_pcode.mand_id → userdb.mand_user.mand_id` als echter FK innerhalb `userdb`. Alle übrigen Verknüpfungen zwischen den vier Datenbanken (`mand_id`, `cust_id`, `fo_id`) sind rein logisch, ohne FK-Constraint — bedingt durch die getrennten Datenbankverbindungen.

Datenbank-Zugangsdaten werden in der Datei `.env` auf dem Server gespeichert. Änderungen erfolgen direkt per SSH. Die `.env` wird nicht per FTP übertragen und ist nicht versioniert (`.gitignore`). Kein separater Änderungs-Mechanismus über die Weboberfläche vorgesehen.

# Fotoobjekte

Fotoobjekte sind der zentrale Datentyp bzw. die zentrale Objektstruktur (`fotodb.foto_obj`, PK `fo_id`). Fotoobjekte bestehen aus einer Binärdatei (Foto oder Video), einem Titel, einem Untertitel und einer Beschreibung.

Fotoobjekte werden Aktivitätengruppen und Subaktivitätengruppen über Pivot-Tabellen (`ag_fo_context`, `asg_fo_context`) zugeordnet. Fotoobjekten wird eine Sicherheitsstufe (`fo_sec_level`) zugeordnet.

Diese Struktur ist vollständig spezifiziert, aber noch nicht implementiert — Controller, Views und der Upload-Flow folgen in Phase 7.

# Sicherheitsstufen Customer-Scope

Es gibt sieben Sicherheitsstufen (0–6). Stufe 0 ist öffentlich (auch für anon). Die Stufen bedeuten: 0 = Öffentlich, 1 = Bekannte, 2 = Großfamilie, 3 = Freunde, 4 = Enge Freunde & Kernfamilie, 5 = Vertraulich, 6 = Streng vertraulich. Objekte der Stufe 6 werden als BLOB in `fotoblobdb.foto_obj` (Spalte `fod_obj`, verknüpft über `fo_id`) gespeichert, alle anderen Stufen als Datei im Dateisystem (`foto_obj.fo_filepath`).

Der Session-Key `_sec_level` wird bereits beim Login geschrieben (`CustLoginController`), steuert aber noch keine Content-Filterung — das ist zentraler Bestandteil von Phase 7.

# Customer

Zwei Typen:

**Anon** hat keinen Login, nur ein Kurzzeit-Kennwort. Die Logik hierzu wird im Abschnitt „Passcodes" erläutert.

**Registrierte User** bekommen vom Mand mit der Einladungsmail eine Sicherheitsstufe zugewiesen, diese ist für den cust aber unsichtbar. Registrierte User loggen sich mit E-Mail/Passwort (optional 2FA) oder über einen Passkey an.

Die UI-Bezeichnung für Customer ist „Mitglied / Mitglieder". Der Begriff „Customer" wird in Kommentaren im Code (Rolle `cust`) verwendet.

# Administration

**System-User (Rolle `syst`):** CRUD-Zugriff auf Mandanten. Systemuser laden neue Mandanten per E-Mail ein.

**Primäre und nicht-primäre System-User:** Das Feld `userdb.syst_user.is_primary` (TINYINT, Default 0) unterscheidet primäre von nicht-primären syst-Usern. Primäre syst-User können nicht gelöscht werden — weder von primären noch von nicht-primären. Ein primärer syst-User kann nicht-primäre syst-User löschen (nicht andere primäre, nicht sich selbst). Nicht-primäre syst-User können niemanden löschen. Der `is_primary`-Status wird bei der Einladung vergeben (Checkbox im Einladungsformular, nur für primäre syst-User sichtbar; serverseitig erzwungen) und ist nach Registrierung nur per direkter DB-Änderung modifizierbar. Dies schützt vor versehentlichem oder böswilligem Entfernen aller Administratoren.

**Moderationseingriff auf Foto-Objekte:** Bei Decency-Verstößen setzt syst die Sicherheitsstufe des betroffenen Fotos (`fo_sec_level`) auf 6 (höchste Stufe). Das Foto wird damit für cust mit `sec_level < 6` und für anon unsichtbar, bleibt aber für den mand und für cust mit `sec_level = 6` sichtbar. Die Aktion ist reversibel. Ein separates `fo_blocked`-Flag wird nicht implementiert. Der mand erhält automatisch eine Benachrichtigungs-E-Mail (`ModerationMail`, reiner Mailversand ohne DB-Tabelle — Umsetzung erst nach Content-Upload relevant, also im Rahmen von Phase 7).

**Mandant (Rolle `mand`, UI: „Galerist:in"):** CRUD-Zugriff auf eigene Inhalte (Aktivitätengruppen, Subgruppen, Fotoobjekte) und eigene Mitglieder. Sperrung eines Mandanten durch syst ist vorgesehen (`mand_user.active`, `valid_to`). Mand laden cust per E-Mail ein, die für den cust nicht sichtbar eine Sicherheitsstufe enthält.

Für jeden registrierten Usertyp (syst, mand, cust) gibt es eine Einstellungsseite für administrative Aufgaben. Für jede administrative Aufgabe gibt es einen Button auf der Einstellungsseite und eine separate Seite. Der sichtbare UI-Text lautet „Einstellungen" (Routen-Namen wie `mandant.dashboard` bleiben technisch unverändert).

Syst administriert Mand, Mand administriert Cust. Für jede User-Gruppe gibt es auch Seiten für die Selbst-Administration. Für den Mand gibt es Seiten für die Administration seines Content (Gruppen, Subgruppen und Fotoinhalte) — diese folgen in Phase 7.

# Datenstruktur

Die oberste Ebene der Datenstruktur ist der Mandant, darunter befinden sich die vom Mandant erstellten Aktivitätengruppen (`activity_group`, CRUD-Zugriff, PK `ag_id`). Aktivitätengruppen haben einen Titel, einen Subtitel, eine Sicherheitsstufe und eine Beschreibung. Einer Aktivitätengruppe können Fotoobjekte zugeordnet werden.

Unterhalb einer Aktivitätengruppe kann der Mandant Subaktivitätengruppen anlegen (`activity_subgroup`, CRUD-Zugriff, PK `asg_id`, FK `ag_id → activity_group.ag_id`). Deren Datenstruktur ist identisch mit der der Aktivitätengruppe.

Die Tabelle `fotodb.mand_profile` (PK `mp_id`) dient nicht der Eigenverwaltung, sondern gehört zum Foto-Content: Sie enthält Daten für eine Profilseite, auf der sich der Mandant seinen Besuchern (cust, anon) vorstellt. Schema: `mp_name`/`mp_title` (varchar 255), `mp_text` (text), `mp_title_start`/`mp_subtitle_start` (varchar 255). Die Tabelle ist aktuell leer, das Schema ist korrekt und bedarf keiner weiteren Korrektur.

# Benutzerregistrierung

Ein System-User kann andere SystemUser verwalten (CRUD), sowie Mandanten-User verwalten (CRUD). SystemUser können die Passworte der Mandanten-User ändern, Mandanten-User können die Passworte und die Zuweisung der Sicherheitsstufen ihrer nachgeordneten User ändern.

Alle Anwender können ihren eigenen Zugriff (Benutzername, Passwort, E-Mail) verwalten (CRUD). E-Mail- und Passwort-Änderung erfolgen per Modal, die E-Mail-Änderung mit Bestätigungslink-Flow.

Neue Anwender (System, Mandant, Customer) erhalten eine E-Mail (SMTP), mit der sie einen eigenen Account anlegen können.

2FA erfolgt ausschließlich per E-Mail (6-stelliger Code, 2 Minuten gültig, Tabelle `twofa_code` in `sessiondb`). Keine SMS. 2FA ist für syst immer aktiv. Für mand ist 2FA der Standard-Login-Weg, entfällt sobald ein Passkey registriert ist. Für cust ist 2FA optional (gesteuert durch `mand_cust_2fa`-Schwellenwert in `mand_user`, TINYINT UNSIGNED 0–7, kein Boolean). Passkeys (WebAuthn) sind für mand und cust implementiert (Bibliothek: `web-auth/webauthn-lib` 5.3.5, Tabelle `passkey` in `userdb`). Passkey-Nutzung wird aktiv gefördert (Prompt nach Login je nach Gerät/Browser), aber nicht technisch erzwungen.

Ein initialer Willkommens-Bildschirm (`show_welcome`-Gate) wird beim allerersten Login von mand und cust gezeigt (Markdown-Inhalt + „Gelesen"-Button, Middleware `CheckWelcome`). Ein darin enthaltener Passkey-Einrichtungslink für cust ist konzeptionell vorgesehen, aber noch nicht umgesetzt.

# Session-Timeouts für syst, mand, cust und anon

Session-Timeouts sind konfigurierbar per `.env`: anon = 900 s (Standard), cust = 900 s, mand = 1800 s, syst = 600 s. Die Dauer ist für mand höher, damit längere Uploads nicht durch das Timeout unterbrochen werden.

Jeder Besucher — auch anon — erhält einen Eintrag in `sessiondb.session` (eigener Session-Driver mit `sess_id` als PK statt Laravel-Standard-`id`, Abfrage über `sess_token`). Sessions werden beim Login (abgelaufene) bzw. beim Logout (eigene) aus der DB gelöscht — kein Cron/Scheduler. Bei jedem Login-/Session-Übergang (Passwort-Login, 2FA-Verifikation, Passkey-Login, anon-Kurzzeit-Kennwort-Login sowie Trusted-Device-Auto-Login — insgesamt 7 Stellen) wird die Session per `regenerate(true)` vollständig neu aufgebaut, damit keine verwaisten Zeilen in der Tabelle zurückbleiben.

# Passcodes

Der Mandant (mand) erfasst bis zu 6 Kurzzeit-Kennwörter (`pw1`–`pw6`) in der Tabelle `pw_list` (`sessiondb`), AES-verschlüsselt gespeichert (nicht gehasht, da der mand sie im Klartext einsehen können muss). Jedes Kurzzeit-Kennwort steht für eine Sicherheitsstufe (1–6): die Slot-Nummer entspricht dem sec_level. Die Kennwörter sind zeitlich begrenzt gültig (`valid_from` / `valid_until`).

Ein Passcode (`cust_passcode`) besteht aus einer Zuordnung zu einem Mandanten und einem numerischen Wert, der die erreichte Sicherheitsstufe repräsentiert. Er wird in der eigenständigen Tabelle `userdb.cust_pcode` (PK `pcode_id`) gespeichert — diese verweist per echtem FK auf `mand_user.mand_id` und logisch auf `cust_user.cust_id`.

„sec_code" ist ein rein konzeptioneller Begriff für einen Zugangscode zu einer Sicherheitsstufe und existiert nicht als eigenes DB-Feld. Real abgebildet wird er über `userdb.cust_pcode.cust_passcode`.

Sicherheitsstufen bei Einladung: Registrierte User bekommen bei der Einladung per E-Mail eine Sicherheitsstufe zugeordnet. Diese wird zunächst in der Tabelle `sessiondb.cust_invite` gespeichert (führende Einladungstabelle, Spalte `sec_level`) und nach Registrierung in `userdb.cust_pcode.cust_passcode` übernommen. `userdb.cust_invite` ist ein veraltetes Relikt (Struktur identisch zu `sessiondb.cust_invite`), wird nicht verwendet und aus Vorsicht nicht gelöscht.

Der Passcode dient beim Anon der Zuweisung einer Sicherheitsstufe, die in der Session temporär gespeichert wird. Für Cust-User wird die Sicherheitsstufe permanent in `userdb.cust_pcode.cust_passcode` gespeichert. Cust und Anon verwenden in einer Session die gleiche Logik: die Sicherheitsstufe wird als Session-Key `_sec_level` übernommen.

Tabellennamen und Spaltennamen sind historisch gewachsen, ihre Bezeichnungen sind nicht immer vollständig konsistent mit ihrem Inhalt. Bei Unklarheit gilt PROJECT_CONTEXT.md als maßgebliche Referenz.

# Website Login

Der Login für System erfolgt über die Adresse „/backstage". Diese Adresse ist nirgends verlinkt. Immer mit 2FA über E-Mail. Kein Passkey. Die aktive View ist `resources/views/system/login.blade.php` (gerendert von `SystemLoginController@login`); Login und 2FA laufen über dieselbe Datei, gesteuert per `show_2fa`-Flash-Variable.

Login-Modal mit Tabs für anonymen und registrierten Customer: „Kurzzeit-Passwort" (Anon-Passcode-Eingabe) und „Mitglied" (Login für registrierte cust).

Anon: ohne Kurzzeit-Passwort kein Zugriff auf die Site. Eingabe des Kurzzeit-Passworts erlaubt Zugriff auf eine Sicherheitsstufe und den Content eines Mand. Kein regulärer Login.

Cust-Login: Passwort + optionale 2FA, alternativ Passkey (Priorität).

Auf dem Cust-Login-Modal befindet sich ein unauffälliger Link zum Mandanten-Login-Modal: Mand-Login: Passwort + 2FA per E-Mail, alternativ Passkey (Priorität).

Alle Login-Buttons, die eine Sicherheits-/2FA-Mail auslösen, besitzen einen Doppel-Submit-Schutz (`type="button"` + `@click="$el.closest('form').submit(); submitted = true"` + `:disabled="submitted"`), damit mehrfaches Antippen nicht mehrere Mails verschickt.

**Trusted-Device / vollständiger Auto-Login (NEU, 10.–18.07.2026):** Im Login-Modal (cust + mand) steht eine Checkbox „Dieses Gerät als sicher merken" zur Verfügung. Bei Aktivierung wird ein Trusted-Device-Cookie ausgestellt (Token gehasht in `sessiondb.trusted_device` gespeichert, bewusst getrennt von `userdb`). Ruft ein Besucher ohne bestehende Session die Startseite auf, prüft die Middleware `AutoLoginTrustedDevice`, ob ein gültiges Trusted-Device-Cookie für cust und/oder mand vorliegt, und baut bei Treffer die Session automatisch auf — **ohne erneute Passwort- oder 2FA-Eingabe**. Sind gleichzeitig gültige Cookies für cust und mand vorhanden (z. B. gleiche Person mit beiden Rollen), wird **immer cust bevorzugt**, mand wird in diesem Fall nicht automatisch eingeloggt. Die Gültigkeitsdauer ist über `TRUSTED_DEVICE_DAYS` konfigurierbar (konzeptionell 7 Tage vorgesehen, aktuell auf 1 Tag für den Testbetrieb reduziert). Beim Logout wird — sofern ein Trusted-Device-Eintrag für den aktuellen Nutzer existiert — ein Bestätigungsdialog eingeblendet, der die Löschung des Eintrags anbietet; unabhängig davon werden bei jedem Logout global alle abgelaufenen Trusted-Device-Einträge aller Nutzer bereinigt.

# User-Frontend: Mandantenseiten

Mandantenseiten sind keine Administrationsseiten (siehe Abschnitt Administration). Nach dem Login bzw. der Eingabe eines Kurzzeit-Passworts wird die erste Mandantenseite angezeigt. Mandantenseiten dienen der Anzeige des Contents jeweils eines Mandanten.

Beim allerersten Login wird vor der Mandantenseite zusätzlich die Willkommensseite gezeigt (siehe Abschnitt Benutzerregistrierung).

Dort können Cust und Anon im Rahmen ihrer Sicherheitsstufe auf Aktivitätengruppen und deren Content eines Mandanten sowie auf dessen Untergruppen und deren Content zugreifen. Anon sehen dabei nur den Content „ihres" Mandanten (Aktivitätengruppen, Subgruppen, Fotos/Videos, Profilseite des Mandanten).

Öffentlicher Content: „Öffentlich" (publik) ist hier als systemweit öffentlicher Content zu verstehen, sichtbar nur für angemeldete User (cust). Angemeldete Cust können auf als „public" gekennzeichneten Content zugreifen, insofern Mandanten ihren Content grundsätzlich dafür freigegeben haben (Feld `has_public_content` gesetzt) und jedes freigegebene Content-Objekt mit der Sicherheitsstufe 0 gekennzeichnet ist.

Das Layout der Mandantenseiten (horizontale Balken mit Thumbnails senkrecht angeordnet, seitliche Navigation zwischen Mandanten) bleibt gültig als Zielzustand. Noch nicht implementiert — zentraler Bestandteil von Phase 7.

Die Einstellungsseiten, die Mandantenseiten und alle weiteren Seiten werden für drei Geräteklassen optimiert — Desktop (Win10), Smartphone und Tablet. Das Tablet ist das primäre Gerät für cust (Fotoalbum-Erlebnis, Querformat, Wischgesten). Das Smartphone ist für mand das wichtigste Frontend für den Foto-Batch-Upload. Dennoch werden alle Funktionen (Admin, Upload, Content-Administration) auf allen Geräten verfügbar sein. Umsetzung: responsive Tailwind-Breakpoints (`md:` = Tablet+Desktop, darunter Smartphone), mit eigener Komponente für den Smartphone-Batch-Upload.

Sämtliche Buttons, Links und Labels erhalten (global über `app.css`) eine iOS/Android-taugliche Tap-Rückmeldung (`:active`-Skalierung) sowie `user-select: none` außer auf `input`/`textarea`. Button-artige `<a href>`-Elemente wurden auf `<button type="button" @click="window.location='...'">` umgebaut, da iOS bei langem Tap sonst das Kontextmenü auslöst; Seiten mit Unsaved-Changes-Guard verwenden stattdessen `@click="$store.unsavedGuard.requestNav('...')"`. Bekannte, akzeptierte Einschränkung: iOS Apple Mail ignoriert `user-select: none` vollständig, Button-Text in Einladungsmails bleibt dort markierbar.

# Sicherheitsstruktur der Website

Die Website erfüllt höchste Sicherheitsanforderungen. Die Website wird nicht in Suchmaschinen gespeichert (Header `X-Robots-Tag: noindex, nofollow`, Middleware `NoIndexHeader`, global aktiv).

Die Website folgt einer strengen MVC-Struktur, wobei die öffentlichen Dateien nicht im Wurzelverzeichnis stehen, sondern im Pfad `/public`.

Implementiert: Session-Hijack-Schutz (Middleware `SessionHijackProtection` — IP-Hash + UA-Hash-Vergleich bei jedem Request), Session-Idle-Timeout (Middleware `SessionIdleTimeout`, konfigurierbar je Rolle), Rollenprüfung (Middleware `RequireRole`), Session-Integrität (`SessionIntegrityService`), eigener Session-Driver (`sessiondb`) mit `sess_id` als PK. Zusätzlich Middleware `CheckPolicyVersion` (Datenschutz-Versions-Vergleich, blockiert bei veralteter Zustimmung) und `CheckWelcome` (Willkommensseiten-Gate). Beide Gate-Middlewares schließen jeweils alle Bestätigungsrouten der anderen sowie die Datenschutz-Routen aus (`routeIs('*.policy.*') || routeIs('*.welcome*') || routeIs('*.datenschutz.*')`), um Redirect-Loops zu vermeiden.

Die Website kennt (soweit technisch möglich) nur ein Cookie für die Session-Steuerung. Jede Datenbank wird mit einer eigenen Username/Passwort-Kombination gesichert.

# Code-Dateien

Jede Codedatei erhält einen Docblock-Header mit: Dateiname mit Pfad, fortlaufende Versionsnummer, codierte Funktionen und Prozesse mit Kurzbeschreibung, Funktionsaufrufe zu anderen Dateien, Datenbankzugriffe mit Tabelle.Spalte.

Alle UI-Texte und Code-Kommentare sind auf Deutsch. UI-Terminologie: „Mitglied/Mitglieder" statt Kunde/Kunden, „Galerist:in" statt Mandant. Terminologie für Code-Kommentare bleibt „syst", „mand" und „cust".

Kein standardmäßiges Laravel-`id`-PK-Schema; alle PKs sind custom (`mand_id`, `cust_id`, `fo_id`, `sess_id` usw.), `$primaryKey` wird explizit gesetzt. `public $timestamps = false` gilt auf allen Domain-Models. Base-Model-Pattern je Datenbank-Connection (`UserDbModel`, `SessionDbModel`, `FotoDbModel`, `FotoBlobDbModel`).

# Zusätzliche, im laufenden Betrieb umgesetzte Features

Folgende Features wurden nach dem ursprünglichen Konzept (07.06.) ergänzt und sind Teil des aktuellen, fertiggestellten Standes:

- Datenschutz & Einwilligung (Erläuterungsseite, PDF-Auslieferung, Einwilligungs-Checkboxen, Policy-Versions-Popup mit syst-Verwaltung)
- Adressfelder für mand + cust (Pflicht-/Optionalfelder, im „Mein Konto"-Bereich editierbar)
- E-Mail- und Passwort-Änderung per Modal (E-Mail mit Bestätigungslink-Flow)
- Unsaved-Changes-Guard (eigenes Verwerfen-Modal in 7 Einstellungs-Fenstern)
- Mitgliederliste: Custom-Dropdown für Sicherheitsstufe, Sortierung, client-seitige Live-Suche
- Willkommensseite beim ersten Login (`show_welcome`-Gate)
- FAQ & Infos (dynamisches, dateibasiertes Hilfesystem, Markdown-Dateien pro Rolle)
- cust-Account-Löschung mit E-Mail-Benachrichtigung bei Verwaisung; analoge Kaskade bei syst-seitiger Mandanten-Löschung (`MandAccountDeletedMail`)
- Globale iOS/Android-Button-Rückmeldung, `<a>`→`<button>`-Umbauten, Doppel-Submit-Schutz auf Login-Buttons (siehe Abschnitt „User-Frontend: Mandantenseiten"); Long-Tap-Fix am 09.–10.07. mit den verbliebenen ~34 button-artigen Links abgeschlossen
- Deutsche Fehlermeldungen für sämtliche passwortverarbeitenden Formulare
- Upload-Bedingungen-Popup für cust entfernt (10.07.) — für cust nicht relevanter Inhalt, ersetzt durch statischen Hinweis + FAQ-Eintrag; DS-Popup bleibt für cust aktiv, mand unverändert mit beiden Popups
- Trusted-Device / vollständiger Auto-Login ohne Passwort (10.–18.07., siehe Abschnitt „Website Login")
- Session-Bugfixes (18.–19.07.): „Sitzung abgelaufen"-Meldungen entfernt, defekter `/system/login`-Redirect auf `/backstage` korrigiert, verwaiste `sessiondb.session`-Zeilen sowie dauerhaft falsch befüllter `user_type` behoben

# Ausblick: Phase 7 (nächster Entwicklungsschritt)

Phase 7 umfasst den Foto-Content: Upload, Verwaltung und Anzeige. Priorität: mand-seitiger Content (Upload & Verwaltung) vor der Cust-UI.

Offen für Phase 7:
- Controller/Views für `ActivityGroup`, `ActivitySubgroup`, `FotoObj` (Anzeige + CRUD)
- Smartphone-Batch-Upload-Flow für mand (`/mandant/upload/*`): Galerie-Picker mit Mehrfachauswahl, Datumserkennung (Fallback-Kette EXIF → Dateiname-Parsing → `filemtime()` → manuell), Zuordnung zu AG/ASG, Sicherheitsstufe, Upload mit Fortschritt/Retry
- Mandanten-Content-Seite (horizontale Balken, Thumbnails, Navigation zwischen Mandanten) für cust + anon
- Anzeige der `mand_profile`-Profilseite für cust/anon
- Einbindung des `_sec_level`-Session-Keys in die Content-Filterung (aktuell gesetzt, aber ungenutzt)
- Implementierung von `ModerationMail`
- Passkey-Einrichtungslink in der Willkommensseite für cust

Bereits getroffene technische Entscheidungen für Phase 7 (Details siehe PROJECT_CONTEXT.md): Bild-Standardisierung (Master 2880×1620 px + Thumbnail 400×225 px, Resizing serverseitig via Intervention Image/GD), Video-Standard 720p H.264/AAC MP4 (~2,5–3 Mbit/s, ffmpeg-Re-Encoding), Speech-to-Text-Eingabe (Web Speech API + Alpine.js) für Content-Textfelder, geschätzte Speicherkapazität ~74,8 GB nutzbar (~4.897 speicherbare Foto-Einheiten).

# Offene Punkte außerhalb Phase 7

**Nächster Schritt (oberste Priorität, vor allem Übrigen):** Gründlicher, systematischer Test der gesamten Passkey-Funktionalität (Phase 6) — Registrierung, Login, Umbenennen, Löschen, Prompt-/Dismiss-Logik, jeweils für mand UND cust, über Windows/Android/iOS und die relevanten Browser hinweg. Bisher wurde nur punktuell getestet (Windows Hello, Android Chrome/Firefox, cust-Banner, ein Grenzfall). **Ausdrücklich kein reiner iOS-Test** — auch wenn zusätzlich speziell für iOS kein abgeschlossener WebAuthn-Passkey-Test dokumentiert ist (die bisherigen iOS-Tests betrafen Button-Feedback/Long-Tap-Fix und Auto-Login, nicht den Passkey-Flow).

Danach:
- `dirty`-Ausblendung bei zwei verbliebenen Views nachziehen (`system/mandanten/index.blade.php`, `customer/auth/register.blade.php`)
- Regressionstest der globalen Button-Animation auf Android/Windows (iOS ist getestet und abgeschlossen)
- Abnahmetest cust-Bereich (Testplan liegt vor, pausiert; Ziel-Tag `cust_complete_ok`)
- SPF-/DKIM-Setup auf dem Mailserver prüfen (E-Mail-Änderungsmails landen derzeit teils im Spam, aktuell per UI-Hinweis adressiert)
- Doppelter `.env`-Schlüssel `TRUSTED_DEVICE_DAYS` (Zeile 17 `=1`, Zeile 97 `=7`) bereinigen, bevor die Gültigkeitsdauer produktiv von 1 auf 7 Tage umgestellt wird
- Logout-Bestätigungsdialog: „Zurück"-Button ggf. zu „Verlassen ohne Löschen" umbenennen (besprochen, nicht umgesetzt)
- E-Mail-Footer-Inkonsistenz (Sie-Form trotz Du-Form-Mailtext) in drei Templates vereinheitlichen (niedrige Priorität)

Fotosite V08 — Konzept (aktualisierte Fassung) | Stand 19.07.2026
