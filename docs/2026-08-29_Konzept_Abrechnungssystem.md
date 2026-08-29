# Konzept: Abrechnungssystem (subscriptiondb)

*Stand: 28.08.2026 · Status: **Konzept + DDL angelegt**, keine Anwendungslogik implementiert · Bezug: PROJECT_CONTEXT.md*

---

## 1. Zweck und Abgrenzung

Vorbereitung einer Schnittstelle/Datenstruktur für ein späteres Abrechnungssystem für `mand` und `cust`. Das Modell ist bewusst **vorbereitend** angelegt: Die Tabellen definieren nur die Felder, die zur Identifikation eines Vorgangs zwingend erforderlich sind. Weitere Felder (Rechnungsadresse, USt-ID, Steuersatz, Zahlungsdienstleister-Referenzen, Mahnstufen, Zahlungsstatus) werden bei der Realisierung ergänzt.

**Die eigentliche Buchhaltungslogik ist NICHT Teil dieser Implementierung.** Die in Abschnitt 4 beschriebenen Abläufe (Umlage von Zahlungseingängen, FIFO-Ausgleich, Erstattung von Guthaben) sind als **Erweiterungsoption** dokumentiert — zum Verständnis der Datenstruktur und als Grundlage, falls später eine kommerzielle Nutzung umgesetzt wird. Sie erfordern Logik in einem Buchhaltungsprogramm, das hier nicht existiert.

**Kein Eingriff in bestehende Tabellen.** Die Verknüpfung zu `userdb.mand_user`/`userdb.cust_user` erfolgt ausschließlich logisch über `user_type` + `user_id` — analog zum bereits etablierten Muster bei `passkey`, `passkey_dismissed` und `trusted_device`. Kein FK-Constraint über Datenbankgrenzen hinweg (Projektkonvention).

**Eigene Datenbank:** `u14bc1w8_v08_subscriptiondb`, Laravel-Connection `subscriptiondb`, Base-Model `App\Models\SubscriptionDb\SubscriptionDbModel`. Grund für die Auslagerung: abweichende Aufbewahrungsfristen (10 Jahre für Buchungsdaten), eigenes Backup, getrennte Zugriffsrechte. Ohne Subscription bräuchte man diese Datenbank nicht — daher der Name.

---

## 2. Leitprinzipien

1. **Das Journal ist unveränderlich.** Buchungen werden nur angelegt, nie geändert oder gelöscht. Korrekturen ausschließlich durch Gegenbuchung. Entspricht den GoBD-Anforderungen an Unveränderbarkeit und Nachvollziehbarkeit.
2. **Der Saldo wird nie gespeichert**, sondern immer als Summe über die Buchungen berechnet — wie ein Kontoauszug.
3. **Die Rechnung ist ein Dokument, kein Forderungsträger.** Die Forderungsverwaltung liegt vollständig im Ledger. Die Rechnung ist eine Momentaufnahme und bezieht sich immer auf **eine** Subscription.
4. **Beträge als Snapshot.** Buchungen speichern Betrag, Plan-Code und Plan-Version als eingefrorene Werte, damit sie selbsttragend bleiben und spätere Änderungen an `plan` alte Buchungen nicht verfälschen.
5. **Plan-Versionen sind unveränderlich.** Eine Preisänderung erzeugt eine neue Versionszeile, keine Änderung der bestehenden.
6. **Zwei getrennte Konten** je Subscriber: das Geldkonto (was tatsächlich geflossen ist) und die Vertragssalden (was geschuldet wird). Siehe Abschnitt 3.4.

---

## 3. Tabellen

### 3.1 `subscriber` — Vertragspartner

Wer zahlt. Getrennt vom Login-Datensatz, damit ein Subscriber einen gelöschten Account überdauern kann (Aufbewahrungspflicht) und damit ein Subscriber mehrere Verträge für mehrere Nutzer bündeln kann (z. B. ein Verein für mehrere Galerist:innen).

| Feld | Typ | Zweck |
|---|---|---|
| `sr_id` | BIGINT UNSIGNED, PK, AI | |
| `sr_name` | VARCHAR(255) | Bezeichnung des Vertragspartners |
| `created_at` | DATETIME | |

### 3.2 `plan` — Tarif, versioniert

Jede Zeile ist eine unveränderliche Version eines Tarifs. Preisänderung = neue Zeile.

| Feld | Typ | Zweck |
|---|---|---|
| `pl_id` | BIGINT UNSIGNED, PK, AI | |
| `pl_code` | VARCHAR(20) | fachlicher Tarifschlüssel, über Versionen konstant |
| `pl_version` | VARCHAR(10) | Versionskürzel (wird im Ledger mitgeführt) |
| `pl_label` | VARCHAR(255) | Anzeigename |
| `price` | DECIMAL(10,2) | **DECIMAL, nicht FLOAT** (Rundungsfehler) |
| `currency` | CHAR(3) | |
| `billing_interval` | VARCHAR(20) | z. B. monatlich, jährlich |
| `valid_from` | DATE | |
| `valid_to` | DATE, NULL | NULL = aktuell gültige Version |

**Regeln:**
- UNIQUE über `pl_code` + `pl_version`
- Pro `pl_code` darf nur eine Zeile `valid_to IS NULL` haben (auf Anwendungsebene zu prüfen, MariaDB kennt keine partiellen UNIQUE-Indizes)

### 3.3 `subscription` — Vertrag

Ein Datensatz je Vertrag über die gesamte Laufzeit. Eine **Preisänderung** erzeugt keinen neuen Vertrag — sie schreibt nur `pl_version_current` fort. Ein **Tarifwechsel** dagegen ist faktisch ein neuer Vertrag: alte Subscription beenden, neue anlegen.

| Feld | Typ | Zweck |
|---|---|---|
| `sb_id` | BIGINT UNSIGNED, PK, AI | |
| `sr_id` | BIGINT UNSIGNED | Subscriber = wer zahlt |
| `user_type` | ENUM('mand','cust') | für wen der Vertrag gilt |
| `user_id` | BIGINT UNSIGNED | logische Referenz auf `mand_id`/`cust_id` |
| `pl_code` | VARCHAR(20) | Tarif (nicht die Version — die wechselt) |
| `pl_version_current` | VARCHAR(10) | aktuell gültige Version, wird fortgeschrieben |
| `sb_status` | VARCHAR(20) | active / cancelled / terminated / expired |
| `valid_from` | DATE | |
| `valid_to` | DATE, NULL | |

**Regel:** Pro `user_type` + `user_id` darf nur eine Subscription aktiv sein (ein Tarif gleichzeitig). Auf Anwendungsebene zu prüfen.

### 3.4 `ledger_entry` — Journal

Unveränderliches Buchungsjournal mit **zwei getrennten Betragsspalten**:

- **`money_amount` (Geld)** — tatsächliche Geldbewegung. Nur bei `ZE` und `ZA` gefüllt.
- **`amount` (Betrag)** — Wirkung auf den Vertragssaldo. Bei `FO`, `GG`, `ZG` und `ZA` gefüllt.

**Fünf Buchungsarten:**

| Kürzel | Bedeutung | `sb_id` | `money_amount` | `amount` |
|---|---|---|---|---|
| `FO` | Forderung | gesetzt | — | negativ |
| `GG` | Gewährte Gutschrift (Rabatt, Korrektur) | gesetzt | — | positiv |
| `ZE` | Zahlungseingang | NULL | positiv | — |
| `ZG` | Zahlungsgutschrift (Umlage einer `ZE` auf einen Vertrag) | gesetzt | — | positiv |
| `ZA` | Zahlungsausgang (Erstattung eines Guthabens) | gesetzt oder NULL | negativ | negativ |

**Vorzeichenkonvention aus Sicht des Subscribers** (wie ein Kontoauszug):
- **Negativer Saldo = Schuld**, positiver Saldo = Guthaben
- Forderungen belasten (negativ), Gutschriften und Zahlungsumlagen entlasten (positiv)
- Ein Zahlungsausgang gleicht ein Guthaben aus: `money_amount` negativ (Geld fließt ab), `amount` negativ (Guthaben wird abgebaut)

| Feld | Typ | Zweck |
|---|---|---|
| `le_id` | BIGINT UNSIGNED, PK, AI | fortlaufende Buchungsnummer, ab 1 |
| `sr_id` | BIGINT UNSIGNED | Subscriber — immer gesetzt |
| `sb_id` | BIGINT UNSIGNED, NULL | Subscription; NULL bei `ZE` |
| `entry_type` | ENUM('FO','GG','ZE','ZG','ZA') | |
| `context_le_id` | BIGINT UNSIGNED, NULL | bei `ZG`: Bezug zur zugrundeliegenden `ZE`-Buchung |
| `money_amount` | DECIMAL(10,2), NULL | Geldkonto-Bewegung |
| `amount` | DECIMAL(10,2), NULL | Vertragssaldo-Bewegung |
| `currency` | CHAR(3) | |
| `pl_code` | VARCHAR(20), NULL | Snapshot; NULL bei `ZE`/`ZA` |
| `pl_version` | VARCHAR(10), NULL | Snapshot — ordnet die Forderung der Plan-Version zu |
| `period_from` | DATE, NULL | abgerechneter Zeitraum |
| `period_to` | DATE, NULL | |
| `description` | VARCHAR(255), NULL | freier Kontext (z. B. „Rabatt", Rechnungsbezug) |
| `booked_at` | DATETIME | Buchungszeitpunkt |

**Bewusst kein `updated_at`** — die Zeile ist unveränderlich.

**Gutschriften haben keinen Bezug zu einer konkreten Forderung.** Die Zuordnung erfolgt über `sb_id` zum Vertrag; das genügt. Ein Kontext kann bei Bedarf in `description` hinterlegt werden. Die einzige harte Referenz ist `context_le_id` bei Zahlungsgutschriften — dort ist der Bezug zur Zahlung zwingend, da sonst bei mehreren Zahlungen im selben Zeitraum nicht nachvollziehbar wäre, aus welcher Zahlung eine Umlage stammt.

**Kontrollrechnung:** Summe aller `ZE`-Geldbeträge muss der Summe aller `ZG`-Beträge entsprechen. Eine Abweichung bedeutet: Geld ist eingegangen, aber noch nicht auf Verträge umgelegt.

### 3.5 `invoice` — Rechnung

Momentaufnahme, kein Forderungsträger. Bezieht sich immer auf **eine** Subscription. Eigener Nummernkreis, getrennt vom Journal.

| Feld | Typ | Zweck |
|---|---|---|
| `re_nr` | BIGINT UNSIGNED, PK, AI | Rechnungsnummer |
| `sr_id` | BIGINT UNSIGNED | |
| `sb_id` | BIGINT UNSIGNED | Vertrag, auf den sich die Rechnung bezieht |
| `invoice_date` | DATE | |
| `from_le_id` | BIGINT UNSIGNED | erste nicht ausgeglichene Forderung |
| `to_le_id` | BIGINT UNSIGNED | letzte einbezogene Buchung |
| `total_amount` | DECIMAL(10,2) | Summe zum Erstellungszeitpunkt (eingefroren) |
| `currency` | CHAR(3) | |

Eine Positions-/Zuordnungstabelle ist **nicht** erforderlich: Der Inhalt der Rechnung ergibt sich vollständig aus dem Ledger-Bereich zwischen `from_le_id` und `to_le_id`.

---

## 4. Abläufe (Erweiterungsoption — nicht implementiert)

> Die folgenden Abläufe beschreiben die Logik eines späteren Buchhaltungsprogramms. Sie sind hier dokumentiert, damit die Datenstruktur nachvollziehbar ist und eine kommerzielle Umsetzung darauf aufbauen kann.

### 4.1 Umlage eines Zahlungseingangs auf mehrere Verträge

Gibt ein Subscriber bei einer Zahlung mehrere Rechnungsnummern aus verschiedenen Verträgen an, wird die Zahlung per Zahlungsgutschriften umgelegt:

1. Zahlungseingang als `ZE` buchen — ohne Vertragsbezug, nur `money_amount`.
2. Erste angegebene Subscription: `ZG` in Höhe des dort offenen Betrags, `context_le_id` verweist auf die `ZE`-Buchung.
3. Ist dieser Vertrag ausgeglichen und Geld übrig: weitere `ZG` auf die nächste angegebene Subscription.
4. Und so fort. Der letzte Vertrag behält gegebenenfalls einen offenen Betrag — oder erhält bei Überzahlung ein Guthaben.

Dieses Verfahren wird **generell** angewandt, auch bei nur einer Subscription. Damit ist jede Geldbewegung nachvollziehbar von der Zahlung bis zum Vertrag.

### 4.2 Buchungsbeispiel

Subscriber 30118 mit drei Verträgen (115, 170, 205), einem gewährten Rabatt und einer Überzahlung, die am Vertragsende erstattet wird:

| le_id | sb_id | sr_id | entry_type | context_le_id | Geld | Betrag | Bemerkung | lfd. Saldo |
|---|---|---|---|---|---|---|---|---|
| 49 | 115 | 30118 | FO | | | −25 | | −25 |
| 50 | 170 | 30118 | FO | | | −35 | | −60 |
| 51 | 205 | 30118 | FO | | | −40 | | −100 |
| 52 | 205 | 30118 | GG | | | +10 | Rabatt | −90 |
| 53 | | 30118 | ZE | | +110 | | | −90 |
| 54 | 115 | 30118 | ZG | 53 | | +25 | | −65 |
| 55 | 170 | 30118 | ZG | 53 | | +35 | | −30 |
| 56 | 205 | 30118 | ZG | 53 | | +40 | | **+10** |
| 57 | 205 | 30118 | ZA | | −10 | −10 | Auszahlung Ende aller Verträge | 0 |

**Ablauf:** Drei Forderungen belasten den Saldo auf −100. Ein Rabatt von 10 auf Vertrag 205 entlastet auf −90. Der Subscriber zahlt 110 — also 20 mehr als geschuldet, weil er den Rabatt nicht berücksichtigt hat. Die Umlage verteilt die 110 auf die drei Verträge; nach Vertrag 205 steht ein Guthaben von +10. Am Vertragsende wird dieses Guthaben per Zahlungsausgang erstattet: Geld fließt ab (−10), das Guthaben wird abgebaut (−10), der Saldo steht auf null.

### 4.3 Rechnungsstellung (FIFO-Ausgleich)

Je Subscription:

1. Summe aller entlastenden Buchungen (`GG`, `ZG`) gegen die Summe der Forderungen (`FO`) stellen.
2. Die **späteste Forderung ermitteln, die nicht ausgeglichen ist** — diese ist `from_le_id`.
3. Alle Buchungen ab `from_le_id` bis zur aktuellsten (`to_le_id`) werden in der Rechnung ausgegeben.
4. Die Summe der Beträge in diesem Bereich ist der Rechnungsbetrag und wird zusammen mit `re_nr` an den Zahlungsdienstleister übermittelt.

**Darstellung im PDF:** Je Position wird sowohl der Buchungsbetrag als auch der fortlaufend offene Betrag ausgewiesen — für den Subscriber nachvollziehbar wie ein Kontoauszug.

**Randfall:** Übersteigen die Entlastungen die Forderungen (Guthaben), gibt es keine nicht ausgeglichene Forderung. Dann entweder keine Rechnung oder eine Schlussrechnung mit positivem Saldo, aus der eine Erstattung folgt (siehe 4.2, Zeile 57).

### 4.4 Versionswechsel mitten im Abrechnungszeitraum

Anteilige Korrektur per Gegenbuchung. Beispiel: Version A3 gilt bis 15.09., ab 16.09. gilt A4, Abrechnung erfolgte monatlich im Voraus:

| Typ | sb_id | Zeitraum | Version | Betrag |
|---|---|---|---|---|
| FO | 115 | 01.09.–30.09. | A3 | −10,00 |
| GG | 115 | 16.09.–30.09. | A3 | +5,00 |
| FO | 115 | 16.09.–30.09. | A4 | −7,00 |

Die Zuordnung erfolgt über `sb_id`; ein expliziter Bezug zur korrigierten Forderung ist nicht erforderlich, da Zeitraum und Version die Zuordnung eindeutig machen.

### 4.5 Übermittlung an den Zahlungsdienstleister

Reiner Lesevorgang gegenüber dem Ledger. Der Betrag wird im Code ermittelt und zusammen mit `re_nr` übermittelt — die Buchungen selbst bleiben unangetastet.

Der **Übermittlungs- und Zahlungsstatus** (gesendet, offen, eingegangen, fehlgeschlagen) ist ein veränderlicher Zustand und gehört **nicht** ins Journal. Bei der Realisierung entweder als Feld in `invoice` oder als separate Statustabelle. Der tatsächliche Zahlungseingang wird später als eigene `ZE`-Buchung erfasst.

---

## 5. Technische Absicherung

### 5.1 Unveränderbarkeit des Journals

Zwei Ebenen, beide bei der Realisierung umzusetzen:

1. **Anwendungsebene:** Im Model keine Update-/Delete-Pfade anbieten; zusätzlich über Model-Events (`updating`, `deleting`) eine Exception werfen.
2. **Datenbankebene:** Trigger `BEFORE UPDATE` und `BEFORE DELETE` auf `ledger_entry`, die einen Fehler auslösen. Greift auch bei manuellen Eingriffen über phpMyAdmin. Die DB-User haben laut Rechteprüfung vom 27.08. `TRIGGER`-Rechte.

```sql
DELIMITER $$

CREATE TRIGGER `trg_ledger_no_update`
BEFORE UPDATE ON `ledger_entry`
FOR EACH ROW
BEGIN
  SIGNAL SQLSTATE '45000'
  SET MESSAGE_TEXT = 'ledger_entry ist unveraenderlich. Korrektur nur per Gegenbuchung.';
END$$

CREATE TRIGGER `trg_ledger_no_delete`
BEFORE DELETE ON `ledger_entry`
FOR EACH ROW
BEGIN
  SIGNAL SQLSTATE '45000'
  SET MESSAGE_TEXT = 'ledger_entry darf nicht geloescht werden.';
END$$

DELIMITER ;
```

**Hinweis:** Die Trigger erst setzen, wenn eventuelle Testdaten bereinigt sind — sie blockieren sonst auch das Aufräumen.

### 5.2 Belegnummern

`le_id` wird per `AUTO_INCREMENT` vergeben — atomar, Duplikate ausgeschlossen, keine Zählertabelle nötig. Durchlaufende Nummerierung ohne Jahresreset.

**Lücken sind möglich und unproblematisch.** Wird eine Transaktion zurückgerollt (technischer Fehler, Validierungs-Exception), ist die Nummer verbraucht. Rechtlich verlangt wird Nachvollziehbarkeit, nicht mathematische Lückenlosigkeit. Zu dokumentieren mit dem Hinweis: *„Belegnummern werden per Datenbank-Auto-Increment vergeben; Lücken können durch abgebrochene technische Vorgänge entstehen und bedeuten keine gelöschte Buchung."*

Ein `INSERT` hinterlässt bei InnoDB nie eine halbe Zeile — entweder die Buchung entsteht vollständig oder gar nicht. Die verbrauchte Nummer bleibt jedoch verbraucht, da der Auto-Increment-Zähler bei einem Rollback bewusst nicht zurückgesetzt wird (sonst müssten parallele Transaktionen aufeinander warten).

**Startwert 1** — bewusst abweichend von der Projektkonvention hoher Startwerte (`trusted_device` ab 999906): Eine Rechnung mit sechsstelliger Anfangsnummer wirkt nach außen befremdlich, und ein späterer Wechsel ist bei fortlaufendem Journal nicht möglich.

### 5.3 Transaktionsklammer

Mehrteilige Vorgänge (z. B. Zahlungseingang plus mehrere Umlagen) gehören in eine gemeinsame Transaktion. Ohne diese Klammer könnte bei einem Fehler eine Zahlung ohne zugehörige Umlagen stehen bleiben — im unveränderlichen Journal ließe sich das nur durch weitere Gegenbuchungen heilen.

### 5.4 FK-Constraints

Anders als im übrigen Projekt (nur ein einziger echter FK im gesamten Schema) werden hier **bewusst FK-Constraints gesetzt** — sie dokumentieren die Verwendung der Datenbank und machen das Modell ohne zusätzliche Erklärung lesbar. Alle mit `ON DELETE RESTRICT`: Bei Buchungsdaten wäre `CASCADE` gefährlich, da ein gelöschter Subscriber sonst das gesamte Journal mitreißen würde.

| Tabelle | FK-Bezüge |
|---|---|
| `subscriber` | — |
| `plan` | — |
| `subscription` | → `subscriber`, → `plan` (zusammengesetzt über `pl_code` + `pl_version`) |
| `ledger_entry` | → `subscriber`, → `subscription`, → `ledger_entry` (Selbstreferenz `context_le_id`) |
| `invoice` | → `subscriber`, → `subscription`, → `ledger_entry` (from/to) |

FK-Constraints zu `userdb.mand_user`/`cust_user` sind datenbankübergreifend technisch nicht möglich und laut Projektkonvention auch nicht gewünscht — `user_type` + `user_id` bleiben logische Referenzen.

---

## 6. Offene Punkte für die Realisierung

1. **Rechnungsadresse und Steuerangaben** — Name, Firma, Adresse, USt-ID, Steuersatz, Reverse-Charge-Kennzeichen. Die Adressfelder existieren teilweise bereits in `mand_user`/`cust_user`, sind dort aber als optional konfigurierbar (siehe Pflichtfelder-System, PROJECT_CONTEXT.md Abschnitt 10j) — für zahlende Subscriber müssten sie zwingend werden. Der Mechanismus dafür existiert bereits.
2. **Zahlungsdienstleister-Anbindung** — Referenz/Token statt eigener Speicherung von Konto- oder Kreditkartendaten.
3. **Konflikt mit der bestehenden Löschlogik** — Buchungsdaten unterliegen 10-jähriger Aufbewahrungspflicht und dürfen bei einer Kontolöschung **nicht** mitgelöscht werden. Die bestehende Kaskadenlöschung (`CustAccountDeletedMail`, `MandAccountDeletedMail`) ist entsprechend abzugrenzen. Das `subscriber`-Konzept löst das bereits strukturell (kein FK zum Login-Datensatz), die Anwendungslogik muss es aber ebenfalls berücksichtigen.
4. **Verbrauchsabhängige Abrechnung** — falls nach Speicherplatz, Mitgliederzahl oder Traffic abgerechnet werden soll, sind zusätzliche Erfassungsstrukturen nötig (Traffic wird derzeit nicht geloggt).
5. **Offene-Posten-Verwaltung** — der reine Saldo genügt für „wer schuldet was". Für Mahnwesen („welche Rechnung ist wie lange überfällig?") wäre ein Ausgleichskennzeichen nachzurüsten.
6. **DB-Zugangsdaten** — die `subscriptiondb` wurde mit einem schwachen Passwort angelegt (Testphase, leere Datenbank). **Vor der ersten echten Buchung** durch ein starkes, zufälliges Passwort ersetzen. Gehört auf die Liste der Punkte für die Produktivsetzung.
7. **Buchhaltungslogik selbst** — Abschnitt 4 beschreibt sie, implementiert ist sie nicht.

---

## 7. Aktueller Umsetzungsstand (28.08.2026)

**Erledigt:**
- Datenbank `u14bc1w8_v08_subscriptiondb` angelegt, DB-User eingerichtet
- `.env`-Block `DB_SUBSCRIPTIONDB_*` ergänzt
- Connection `subscriptiondb` in `config/database.php`
- `.env.example` um den Block erweitert
- Base-Model `app/Models/SubscriptionDb/SubscriptionDbModel.php` (minimal, analog zu den vier bestehenden Base-Models: nur `$connection`)
- Verbindungstest erfolgreich
- DDL für alle fünf Tabellen ausgeführt (inkl. FK-Constraints)

**Noch nicht umgesetzt:** Trigger, Tabellen-Models, Controller, Views, jegliche Anwendungs-/Buchhaltungslogik.
