# Fotosite V08 — Chat-Konventionen

*Stand: 29.06.2026*

Dieses Dokument hält die Arbeits- und Kommunikationsregeln für die Zusammenarbeit zwischen Willy (Martin Wagner) und Claude (Claude.ai-Chat + Claude Code) fest. Es ergänzt PROJECT_CONTEXT.md (technischer Stand) und das Notfall-Startdokument (Schnelleinstieg).

---

## 1. Anrede & Sprache

- **Interaktion mit Menschen durchgängig per „Sie".**
- Alle Dokumentation, Code-Kommentare, UI-Texte und Prompts auf **Deutsch**.
- UI-Anrede im Produkt: cust per „du", mand/syst per „Sie".

## 2. Rollenverteilung der Werkzeuge

- **Claude.ai-Chat:** Architektur, Planung, Diagnose, Prompt-Erstellung, Dokumentation, Konsistenzprüfung. **Erzeugt keinen Code direkt im Chat.**
- **Claude Code (lokale CLI):** Führt alle Code-Änderungen aus. Erhält fertige Prompts aus dem Chat.
- **Strikte Trennung:** Für Coding ist Claude Code zuständig. Keine Diskussionen über Code-Inhalte im Chat — der Chat liefert Prompts, Claude Code setzt um.

## 3. Prompt-Format (Chat → Claude Code)

- Prompts werden **im Fließtext des Chats als Codeblock** dargestellt (nicht als Download-Datei, außer bei sehr langen Prompts zur Vermeidung von Truncation).
- Jeder Prompt enthält: Repo-Pfad, Regeln (Docblock-Version hochzählen, KEIN artisan / KEIN git commit sofern nicht gewünscht, KEIN npm run build sofern keine neuen Tailwind-Klassen), klare Aufgabe, betroffene Dateien mit vollständigem Pfad, Abschluss-Anweisung (Vorher/Nachher-Zusammenfassung).
- Bei reinen Diagnosen: „Keine Änderungen, nur lesen."

## 4. Nach jeder Code-Änderung (Chat-Ausgabe)

1. **FTP-Upload-Liste:** alle geänderten Dateien mit vollständigem Pfad (verifiziert gegen die aktuelle dateiliste.txt, nicht aus dem Gedächtnis).
2. **Artisan-Befehle** nur wenn nötig (Controller/Route/Config/View-Cache): `php artisan route:clear ; config:clear ; cache:clear ; view:clear`. Bei reinen Blade-Änderungen ohne neue Tailwind-Klassen: kein Artisan.
3. **npm run build** nur bei neuen Tailwind-Klassen, danach `public/build/` hochladen.
4. **Testschritte im Klartext:** user/mand/syst-Aktionen in einfacher Sprache, was zu prüfen ist.

## 5. Pfad-Verifikation

- **Controller-Pfade immer aus der aktuellen dateiliste.txt prüfen, nie aus dem Gedächtnis.** Die meisten Controller liegen unter `app/Http/Controllers/UserDb/`, aber nicht alle — verifizieren statt annehmen.
- Vor jedem Änderungs-Prompt Routenzuordnung prüfen (welcher Controller rendert welche View — gegen die tatsächliche `return view()`-Anweisung).
- **KORREKTUR 29.06.:** `system/login.blade.php` ist **NICHT** tot, sondern die aktive syst-Login-View. Einzige echte tote Datei: `welcome.blade.php` (Breeze-Default). Die frühere Liste „welcome.blade.php, system/login.blade.php" ist überholt.

## 6. FTP-Workflow (WinSCP)

- Batch-Uploads als WinSCP-Skript (`.txt`), aufgerufen via `WinSCP.com /script=...`.
- Skript-Kopf (Standard):
  ```
  option batch abort
  option confirm off
  option transfer ascii
  open ftp://u14bc1w8:[credentials]@host159.alfahosting-server.de
  cd fotos.martinwagner.de
  ```
- Pro Datei: `put "LOKALER_VOLLPFAD" "SERVER_RELATIVPFAD"`, am Ende `exit`.
- Aufruf in PowerShell: `& "C:\Program Files (x86)\WinSCP\WinSCP.com" /script="PFAD\skript.txt"`
- Bei gehashten Build-Dateinamen (z.B. `app-Jw6eDKDd.css`) den aktuellen Namen per `dir` prüfen.
- PowerShell: Befehlsverkettung mit `;`, nicht `&&`.

## 7. Git-Workflow

- Git-Befehle als **BAT-taugliche** Befehlsfolge ausgeben (eine Zeile pro Befehl, kein `;`-Chaining).
- Stabile Checkpoints nach jedem abgeschlossenen Feature-Block taggen.
- Bekannter Windows-Stolperstein: `Unlink of file '.git/objects/pack/...idx' failed` beim Push — der Push ist dennoch erfolgreich (Dateisperre durch Explorer/AV/IDE). Mit `n` bestätigen, Tag ggf. separat per `git push origin <tag>` nachschieben.
- Tag wird lokal nur gesetzt, wenn `git tag <name>` ausgeführt wurde — bei Abbruch vor dem Tag-Befehl fehlt er und muss nachgeholt werden.
- Rollback: `git reset --hard <tag>`. **Achtung:** Setzt Datei-Timestamps auf „jetzt" → anschließendes `synchronize remote` würde alle Dateien als „neuer" hochladen. Nach Rollback gezielte `put`-Liste der betroffenen Dateien verwenden.

## 8. WinSCP synchronize remote

- `synchronize remote "LOKAL" "SERVER"`: lokal = Quelle, Server = Ziel. Nur lokal neuere Dateien werden hochgeladen, nichts auf dem Server gelöscht. Einseitiges Update.
- Nur sinnvoll ohne vorherigen Hard-Reset (siehe Timestamp-Problem oben).

## 9. Projektdateien & Aktualität

- Alle Projektdateien (dateiliste.txt, DB-Dumps, Doku-MDs) sind **aktuell** — unterschiedliche Daten oder Nummern im Dateinamen bedeuten NICHT unterschiedliche Aktualität.
- **Ausnahme:** Die Konzeptdatei ist bewusst etwas veraltet (konzeptionell-stabil, nicht jede Tagesänderung wird nachgeführt).
- **Implementierter Code ist maßgeblich** über die Dokumentation bei Widersprüchen. Innerhalb der Doku: PROJECT_CONTEXT.md ist maßgeblich über die Konzeptdatei.
- Die Farbschemata-Datei ist derzeit irrelevant (überspringen).

## 10. Fachliche Festlegungen (wiederkehrend)

- **mand_profile** ist Content-Bestandteil (fotodb), keine Verwaltungstabelle. Schema im DB-Dump korrekt, Tabelle leer.
- **sec_code** beschreibt eine Sicherheitsstufe; mand vergeben eigene Passcodes je sec_level für anons. Real abgebildet über `userdb.cust_pcode.cust_passcode`. `sec_code` ≠ `sec_level`. pw1–pw6 (`sessiondb.pw_list`): Slot-Nummer = sec_level (pw1=Stufe 1 … pw6=Stufe 6). DB-Bezeichner sind historisch teils inkonsistent.
- **cust_invite/invite:** stabil, keine Änderung. Führend ist `sessiondb.cust_invite`; `userdb.cust_invite` ist obsoletes Relikt.
- **Passwort-Policy (Stand 29.06., implementiert):** syst = min:12 (nur Länge); mand = min:12 + mixedCase + numbers + symbols + uncompromised; cust = min:10 + mixedCase + numbers. View-Hinweistexte controller-konform.
- **system/login.blade.php ist die AKTIVE syst-Login-View** (nicht tot). Einzige tote Datei: `welcome.blade.php` (Breeze).
- DB.Tabelle.Spalte-Notation durchgängig eindeutig (z.B. `userdb.cust_pcode.cust_passcode`).

## 11. Output-Formatierung im Chat

- Prompts und Code in einem hervorgehobenen Codeblock, ggf. mit System-Indikator (`Claude Code`, `bash`, `PowerShell`).
- Minimale Erläuterungen, keine unnötigen Rückfragen, direkte Ausführung, Entscheidungen in Klartext.
- Bei Unklarheit über betroffene Dateien/Umfang: erst lesen (dateiliste/Code), dann Prompt — nicht raten.

## 12. Dokumentenpflege

- Bei jedem stabilen Stand synchron halten: **PROJECT_CONTEXT.md, Notfall-Startdokument, Projektstatus** (fortlaufend nummeriert), bei konzeptionellen Erweiterungen auch die **Konzeptdatei**.
- Diese Dokumente werden im Chat aus den hochgeladenen Projektdateien aktualisiert (Claude Code kennt deren Ablageort im Dateisystem nicht).
- Auf Anforderung als Download-Dateien bereitstellen.
