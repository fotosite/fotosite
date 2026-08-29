# Passkey-Gesamttest — Testanweisungen (Phase 6)

*Stand: 19.07.2026 · Bezug: PROJECT_CONTEXT.md Abschnitt 9/16a-0, Notfall_Start.md Abschnitt 5.2/7, Inkonsistenzen.md #11*

**Ziel:** Gründlicher, systematischer Test der gesamten Passkey-Funktionalität (Registrierung, Login, Umbenennen, Löschen, Prompt-/Dismiss-Logik) über alle Rollen × Geräte × Browser × Grenzfälle — kein reiner iOS-Test.

---

## 0. Testmatrix (Überblick)

| Dimension | Werte |
|---|---|
| Rollen | mand, cust |
| Windows-Browser | Chrome, Firefox, Edge |
| Android-Browser | Chrome (mit Google-Sync), Firefox (lokal), ggf. Samsung Internet |
| iOS-Browser | Safari, Chrome, Firefox (alle nutzen denselben iCloud-Keychain) |
| Funktionen | Prompt-Logik, Registrierung, Login, Umbenennen, Löschen, Dismiss-Logik |
| Grenzfälle | mehrere Rollen auf einem Gerät, mehrere Passkeys pro User, Widerruf nach Geräteverlust, iOS-Keychain-Sharing |

---

## 1. Vorbereitung (einmalig vor Testbeginn)

1. **Testaccounts festlegen:** je ein dedizierter mand- und cust-Testaccount (nicht produktiv genutzt), falls möglich. Laut aktuellem DB-Stand existiert bereits `mand_id = 28` mit Alt-Resten (siehe Schritt 2) — kann als Testaccount weiterverwendet werden, wenn kein anderer vorgesehen ist.
2. **IDs ermitteln:**

```sql
SELECT mand_id, mand_email FROM userdb.mand_user WHERE mand_email = '...';
SELECT cust_id, cust_email FROM userdb.cust_user WHERE cust_email = '...';
```

3. **Sicherung empfohlen:** Da im Test aktiv aus der DB gelöscht wird (Schritt 2 der Rücksetz-Routine), vor Testbeginn einmal einen aktuellen Export der drei betroffenen Tabellen ziehen (phpMyAdmin-Export oder `mysqldump`), falls kein aktueller Dump vorliegt.
4. **Testprotokoll vorbereiten:** Tabelle gemäß Abschnitt 6 anlegen (Excel/Word), parallel zum Testen führen.
5. **Bekannter Altzustand (Stand aktueller DB-Dump), vor Testbeginn bereinigen:**
   - `userdb.passkey_dismissed`: ein Eintrag `mand_id=28, os='ios'`
   - `sessiondb.trusted_device`: ein Eintrag `mand_id=28, device_label='Firefox auf Windows'`
   - `userdb.passkey`: aktuell leer

---

## 2. Rücksetz-Routine

**Vor jedem neuen Geräte-/Browser-/Rollen-Testblock vollständig durchführen.** Ohne diese Routine verfälschen Altlasten (alte Passkeys, Dismiss-Flags, Trusted-Device-Cookies) das Ergebnis — insbesondere ein aktives Trusted-Device-Cookie überspringt via Auto-Login den gesamten Login-/Prompt-Vorgang.

### R1 — DB: Passkey-Einträge des Testaccounts löschen

```sql
DELETE FROM userdb.passkey
WHERE user_type = 'mand' AND user_id = {mand_id};

DELETE FROM userdb.passkey
WHERE user_type = 'cust' AND user_id = {cust_id};
```

### R2 — DB: Dismiss-Einträge löschen

```sql
DELETE FROM userdb.passkey_dismissed
WHERE user_type = 'mand' AND user_id = {mand_id};

DELETE FROM userdb.passkey_dismissed
WHERE user_type = 'cust' AND user_id = {cust_id};
```

### R3 — DB: Trusted-Device-Einträge löschen

```sql
DELETE FROM sessiondb.trusted_device
WHERE user_type = 'mand' AND user_id = {mand_id};

DELETE FROM sessiondb.trusted_device
WHERE user_type = 'cust' AND user_id = {cust_id};
```

### R4 — DB: Session-Zeile des Testaccounts löschen (falls noch vorhanden)

```sql
DELETE FROM sessiondb.session
WHERE user_type = 'mand' AND mand_id = {mand_id};

DELETE FROM sessiondb.session
WHERE user_type = 'cust' AND cust_id = {cust_id};
```

Regulärer Logout über die UI erledigt dies normalerweise automatisch — R4 dient als Kontrolle/Fallback.

### R5 — Browser-/OS-seitig: gespeicherten Credential löschen

Server-seitiges Löschen (R1) entfernt den Credential **nicht** aus dem OS-/Browser-Speicher. Ohne diesen Schritt kann bei einer erneuten Registrierung der alte, dem Server jetzt unbekannte Credential angeboten werden oder Verwirrung beim Login entstehen.

| Plattform/Browser | Fundort des Passkeys |
|---|---|
| Windows, Chrome/Edge | `chrome://settings/passkeys` bzw. `edge://settings/passkeys` (oder Windows-Einstellungen → Konten → Passkeys) |
| Windows, Firefox | Firefox speichert Passkeys über den Windows-Hello-Systemdialog → Windows-Einstellungen → Konten → Passkeys/Sicherheitsschlüssel |
| Windows Hello direkt | Windows-Einstellungen → Konten → Anmeldeoptionen → Sicherheitsschlüssel/Passkeys |
| Android, Chrome (Google-Sync) | Google-Konto → Passwortmanager → Passkeys (`passwords.google.com`), **zusätzlich** lokal unter Android-Einstellungen → Passwörter & Konten prüfen |
| Android, Firefox (lokal) | Android-Einstellungen → System → Passkeys, oder Geräte-Credential-Manager |
| Android, Samsung Internet | Samsung Pass / Android-Credential-Manager (Einstellungen → Sicherheit → Passkeys) |
| iOS, alle Browser (iCloud Keychain) | Einstellungen → Passwörter → (Passkey suchen und löschen) — gilt für Safari **und** Chrome **und** Firefox gemeinsam, da gemeinsamer Speicher |

### R6 — Website-Daten/Cookies der Domain löschen

Browser-Einstellungen → Website-Daten/Cookies löschen für `fotos.martinwagner.de` (nicht nur Cache) — entfernt insbesondere das Trusted-Device-Cookie clientseitig.

### R7 — Kontrolle vor Testbeginn

```sql
SELECT * FROM userdb.passkey WHERE user_id IN ({mand_id}, {cust_id});
SELECT * FROM userdb.passkey_dismissed WHERE user_id IN ({mand_id}, {cust_id});
SELECT * FROM sessiondb.trusted_device WHERE user_id IN ({mand_id}, {cust_id});
```
Erwartung: alle drei Abfragen liefern keine Zeilen für den aktuell zu testenden Account.

---

## 3. Empfohlene Testreihenfolge

1. **Windows** — Chrome → Firefox → Edge, je Rolle mand dann cust
2. **Android** — Chrome → Firefox → Samsung Internet, je Rolle mand dann cust
3. **iOS** — Safari → Chrome → Firefox, je Rolle mand dann cust
4. **iOS-Keychain-Sharing-Test** (Abschnitt 5.4)
5. **Grenzfälle mehrere Rollen/mehrere Geräte** (Abschnitt 5.1–5.3)

Innerhalb jeder Geräte × Browser × Rolle-Kombination: **zuerst Rücksetz-Routine (Abschnitt 2), danach Funktions-Testfälle 4.1–4.6 in dieser Reihenfolge.**

---

## 4. Funktions-Testfälle (pro Kombination in dieser Reihenfolge)

### 4.1 Prompt-Logik (Ersteinrichtung)
1. Rücksetz-Routine durchgeführt (Abschnitt 2, inkl. Kontrolle R7).
2. Login mit Passwort (+ 2FA, falls für diesen mand-Kontext aktiv).
3. **Erwartung:** Modal (mand) bzw. Banner (cust) erscheint einmalig — kein Passkey für dieses OS, kein Dismiss-Eintrag.
4. Hinweistext prüfen: inhaltlich passend zu OS+Browser-Kombination (siehe Abschnitt 9 PROJECT_CONTEXT.md).
5. Ergebnis im Protokoll notieren.

### 4.2 Registrierung
1. Im Prompt/Banner „Einrichten" klicken.
2. OS-Dialog durchlaufen (Windows Hello / Android Biometrie-PIN / iOS Face-ID-Touch-ID).
3. **Erwartung:** Passkey wird angelegt, Erfolgsrückmeldung in der UI.
4. DB prüfen:
```sql
SELECT pk_id, user_type, user_id, device_name, sign_count, created_at, last_used_at
FROM userdb.passkey
WHERE user_type = '{mand|cust}' AND user_id = {id};
```
Erwartung: neue Zeile, `sign_count = 0`, `last_used_at = NULL`.
5. UI prüfen: Verwaltungsliste (`/mandant/passkeys` bzw. `/customer/passkeys`) zeigt neuen Eintrag mit sinnvollem `device_name`.

### 4.3 Login mit Passkey
1. Logout.
2. Login-Seite: Passkey-Login antriggern.
3. OS-Dialog bestätigen.
4. **Erwartung:** Login erfolgreich ohne Passwort-Eingabe, korrekte Weiterleitung (mand → Dashboard, cust → Content).
5. DB prüfen: `last_used_at` aktualisiert; `sign_count` ggf. erhöht (nicht jeder Authenticator liefert das zuverlässig — kein Fehler, wenn `sign_count` unverändert bleibt, solange Login funktioniert).
6. Prüfen: Prompt/Banner erscheint **nicht** erneut (Passkey jetzt vorhanden).

### 4.4 Umbenennen
1. In der Verwaltungsliste Eintrag umbenennen.
2. Seite neu laden / erneut aufrufen.
3. **Erwartung:** neuer Name bleibt bestehen.
4. DB prüfen: `device_name` aktualisiert.

### 4.5 Löschen
1. In der Verwaltungsliste Passkey löschen.
2. **Erwartung:** Eintrag verschwindet aus der Liste.
3. DB prüfen: Zeile in `userdb.passkey` tatsächlich gelöscht (kein „soft delete").
4. Logout, erneuter Login-Versuch: Passkey-Login-Option nicht mehr verfügbar bzw. schlägt fehl; Passwort-Login funktioniert weiterhin.

### 4.6 Dismiss-Logik

**Teil A — „Später":**
1. Rücksetz-Routine erneut durchführen (frischer Zustand: kein Passkey, kein Dismiss).
2. Login → Prompt/Banner erscheint → „Später" klicken.
3. **Erwartung:** Banner/Modal verschwindet nur für diese Sitzung, **kein** Eintrag in `passkey_dismissed`.
4. Logout + erneuter Login: Prompt/Banner erscheint wieder.

**Teil B — „Nie wieder fragen":**
5. Prompt erscheint → „Nie wieder fragen" klicken.
6. DB prüfen:
```sql
SELECT * FROM userdb.passkey_dismissed
WHERE user_type = '{mand|cust}' AND user_id = {id};
```
Erwartung: neuer Eintrag mit korrektem `os` und gesetztem `ua_hash`.
7. Logout + erneuter Login: Prompt/Banner erscheint **nicht** mehr (auf diesem Gerät+Browser+OS).
8. **Kontrolltest:** gleicher Account, **anderer Browser** auf demselben Gerät (z. B. Firefox statt Chrome unter Windows) einloggen → Prompt/Banner erscheint dort **wieder** (unabhängiger `ua_hash`, granularer Dismiss-Status pro Geräte+Browser+OS-Kombination).

---

## 5. Grenzfälle

### 5.1 Mehrere Rollen auf einem Gerät
1. Auf demselben Windows-Konto/Browser sowohl für den mand- als auch für den cust-Testaccount je einen Passkey registrieren (Abschnitt 4.2 zweimal durchlaufen).
2. Abwechselnd als mand und als cust per Passkey einloggen.
3. **Erwartung:** Browser bietet beim Passkey-Login den passenden Credential an (`userHandle = base64url('mand:{id}')` bzw. `base64url('cust:{id}')`), keine Verwechslung der Rollen.

### 5.2 Mehrere Passkeys für einen User (verschiedene Geräte)
1. Gleicher Account: Passkey auf Windows **und** auf Android registrieren.
2. Verwaltungsliste zeigt beide Einträge mit unterscheidbarem `device_name`.
3. Login von jedem Gerät mit dem jeweils eigenen Passkey funktioniert unabhängig; Löschen eines Eintrags beeinflusst den anderen nicht.

### 5.3 Passkey-Widerruf nach (simuliertem) Geräteverlust
1. Auf einem Gerät (z. B. Android) Passkey registrieren — dieses Gerät gilt im Test als „verloren".
2. Von einem **anderen** Gerät (z. B. Windows, Login per Passwort) in die Passkey-Verwaltung wechseln und den Android-Passkey aus der Ferne löschen.
3. **Erwartung:** Auf dem simuliert verlorenen Gerät funktioniert der Passkey-Login danach nicht mehr; DB-Eintrag ist entfernt; Windows-Passkey (falls vorhanden) bleibt unberührt.

### 5.4 iOS-Keychain-Sharing über Browser hinweg
1. Auf dem iPhone in **Safari** Passkey registrieren (Abschnitt 4.2).
2. **Ohne** erneute Registrierung in **Chrome** (gleiches iPhone) einen Passkey-Login versuchen.
3. **Erwartung:** Login funktioniert, da alle iOS-Browser denselben iCloud-Keychain-Speicher nutzen.
4. Schritt 2 zusätzlich mit **Firefox** auf iOS wiederholen.

### 5.5 Beobachtungspunkt: Trusted Device + Passkey
Nicht zwingend eigener Testfall, aber während der Tests im Auge behalten: Ein aktives Trusted-Device-Cookie löst Auto-Login aus und überspringt damit den regulären Login-Vorgang samt Prompt-Logik. Solange in Abschnitt 2 (R3/R6) konsequent zurückgesetzt wird, sollte das die Passkey-Tests nicht verfälschen — bei unerwartetem Verhalten zuerst prüfen, ob ein Trusted-Device-Cookie/-Eintrag übersehen wurde.

---

## 6. Ergebnis-Dokumentation

Vorschlag für die Protokoll-Tabelle:

| Datum | Rolle | Gerät | Browser | Testfall | Ergebnis (OK/Fehler) | Bemerkung |
|---|---|---|---|---|---|---|
| | mand/cust | Windows/Android/iOS | Chrome/Firefox/Edge/Safari/Samsung | 4.1–4.6 / 5.1–5.4 | | |

---

## 7. Nach Testabschluss

1. Bei vollständigem Erfolg: Git-Tag vorschlagen, z. B. `passkey_full_test_ok`.
2. Testreste bereinigen: Rücksetz-Routine (Abschnitt 2) ein letztes Mal ausführen, falls die Testaccounts auch produktiv genutzt werden sollen.
3. PROJECT_CONTEXT.md / Notfall_Start.md / Projektstatus.md aktualisieren: Status von „gründlicher Test steht aus" auf „getestet am {Datum}, siehe Protokoll" ändern (Abschnitt 9 bzw. Abschnitt 7).
4. Bei aufgetretenen Fehlern: pro Fehler eigener Diagnose-Punkt im Chat, vor Fortsetzung mit Phase 7.
