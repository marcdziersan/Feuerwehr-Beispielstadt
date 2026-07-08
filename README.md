# Freiwillige Feuerwehr Beispielstadt – Virtuelle Feuerwehrwache

Dieses Projekt ist eine **Vorlage für eine virtuelle Feuerwehrwache** der fiktiven **Freiwilligen Feuerwehr Beispielstadt**.

Die Website dient als Demonstrations-, Lern- und Entwurfsprojekt. Sie kann als Ausgangspunkt für eine fiktive Feuerwehrseite, eine Übungsumgebung, ein Portfolio-Projekt oder eine virtuelle Feuerwehrwache genutzt werden.

> **Wichtiger Hinweis:**  
> Dieses Projekt ist in der vorliegenden Form **nicht für ein Produktivsystem geeignet**.  
> Vor einem echten Einsatz im Internet sind zwingend fachgerechte Sicherheits-, Datenschutz-, Rechts- und Serveranpassungen erforderlich.
https://marcdziersan.github.io/Feuerwehr-Beispielstadt/
---

## Projektcharakter

Diese Website ist eine **Musterseite** und keine offizielle Feuerwehrpräsenz.

Alle Inhalte sind Platzhalter oder fiktiv, darunter:

- Feuerwehrname
- Ansprechpartner
- Mitglieder
- Fahrzeuge
- Einsätze
- Termine
- Kontaktinformationen
- Impressum
- Datenschutzhinweise
- Bildmaterial
- Adminbereich
- SQLite-Datenbankstruktur

Die Bezeichnung „Feuerwehr Beispielstadt“ steht stellvertretend für eine virtuelle oder beispielhafte Feuerwehrwache.

---

## Enthaltene Bereiche

Das Projekt enthält unter anderem:

- Startseite
- Über uns
- Gruppen
- Mitglieder
- Fahrzeuge
- Fahrzeugdetailseiten
- Einsätze
- Termine
- Brandschutz
- Kontaktformular
- Impressum
- Datenschutz
- Barrierefreiheit
- Adminbereich
- Nachrichtenverwaltung
- Benutzerverwaltung
- SQLite-Speicherung für Kontaktanfragen

---

## Technische Grundlage

Verwendete Technologien:

- HTML5
- CSS3
- Vanilla JavaScript
- PHP
- SQLite über PDO
- keine externen Frameworks
- keine externen CDN-Abhängigkeiten

Der Adminbereich ist optisch an moderne Admin-Dashboards angelehnt, aber bewusst lokal und ohne externe Bibliotheken umgesetzt.

---

## Lokaler Start

Für die PHP-Funktionen muss das Projekt über einen PHP-Server laufen.

Beispiel:

```bash
php -S localhost:8000
```

Danach im Browser öffnen:

```text
http://localhost:8000/
```

Für das Kontaktformular:

```text
http://localhost:8000/kontakt.php
```

Der Adminbereich ist über das Schloss auf der Kontaktseite erreichbar oder direkt über:

```text
http://localhost:8000/admin/index.php
```

Beim ersten Aufruf wird ein Administrator über `admin/setup.php` angelegt.

---

## Kontaktformular

Das Kontaktformular speichert Nachrichten in einer lokalen SQLite-Datenbank:

```text
storage/kontakt.sqlite
```

Vorhandene Schutzmaßnahmen im Musterprojekt:

- CSRF-Token
- Rechen-Captcha
- Honeypot-Feld
- einfache Rate-Limitierung
- Prepared Statements
- Eingabevalidierung
- Längenlimits
- XSS-Schutz bei Ausgaben
- gehärtete Session-Cookies, soweit lokal möglich

Diese Maßnahmen sind für eine Vorlage sinnvoll, ersetzen aber **keine professionelle Sicherheitsprüfung**.

---

## Adminbereich

Der Adminbereich enthält:

- Login
- First-Run-Setup
- Dashboard
- Nachrichtenübersicht
- Nachrichtendetailansicht
- Statusverwaltung
- Benutzerverwaltung
- Rollen `admin` und `editor`
- Passwortspeicherung mit `password_hash()`
- Loginprüfung mit `password_verify()`

Auch dieser Bereich ist eine Vorlage und muss für einen echten Produktivbetrieb weiter abgesichert werden.

---

## Nicht für Produktivbetrieb geeignet ohne Anpassungen

Dieses Projekt darf nicht unverändert produktiv eingesetzt werden.

Vor einem echten Betrieb sind mindestens folgende Punkte erforderlich:

- HTTPS erzwingen
- sichere Webserver-Konfiguration
- Schutz des `storage/`-Verzeichnisses auch bei Nginx oder anderen Servern
- restriktive Datei- und Ordnerrechte
- professionelles Rollen- und Rechtesystem
- Schutz gegen Brute-Force-Angriffe
- erweitertes Rate-Limiting
- Logging und Monitoring
- Backup-Konzept
- Datenschutzprüfung
- rechtlich geprüftes Impressum
- rechtlich geprüfte Datenschutzerklärung
- Prüfung aller Formulare
- Prüfung aller Adminfunktionen
- sichere Fehlerbehandlung
- regelmäßige Updates der Serverumgebung
- Prüfung durch eine fachkundige Person

---

## Datenschutz und Rechtliches

Die mitgelieferten Seiten für Impressum und Datenschutz sind **nur Platzhalter bzw. Vorlagen**.

Sie müssen vor einer Veröffentlichung durch echte Angaben ersetzt und rechtlich geprüft werden.

Insbesondere müssen angepasst werden:

- Betreiberangaben
- Verantwortliche Stelle
- Kontaktinformationen
- Datenschutzinformationen
- Hostingangaben
- Speicherfristen
- Rechtsgrundlagen
- technische und organisatorische Maßnahmen
- ggf. Cookie- und Trackinghinweise

---

## Bildmaterial

Die verwendeten Bilder und Grafiken sind als Projektmaterial für diese virtuelle Feuerwehrwache gedacht.

Vor produktiver Nutzung muss geprüft werden:

- ob die Bilder rechtlich verwendet werden dürfen
- ob reale Personen abgebildet sind
- ob Logos, Wappen oder Markenrechte betroffen sind
- ob Feuerwehrfahrzeuge oder Kennzeichen realen Organisationen zugeordnet werden könnten

Für eine echte Feuerwehrseite sollten eigene, freigegebene Bilder verwendet werden.

---

## Empfohlene Weiterentwicklung

Für eine produktionsnahe Version wären sinnvoll:

- echtes CMS oder Adminsystem
- Einsatzverwaltung mit Rollenmodell
- Medienverwaltung
- sichere Upload-Funktion
- Protokollierung administrativer Aktionen
- Zwei-Faktor-Authentifizierung
- Passwort-Reset-Funktion
- serverseitige Validierung aller Datenmodelle
- zentrale Konfigurationsdatei
- Migrationen für Datenbankänderungen
- Deployment-Konzept
- automatisierte Tests
- Sicherheitsheader
- Content-Security-Policy
- Backup- und Restore-Skripte

---

## Zweck der Vorlage

Diese Vorlage eignet sich für:

- eine virtuelle Feuerwehrwache
- ein Designkonzept
- ein Portfolio-Projekt
- eine Lernumgebung
- einen Prototyp
- eine Präsentation
- eine nichtöffentliche Demo

Sie ist ausdrücklich **kein fertiges Produktivsystem**.

---

## Lizenz- und Nutzungshinweis

Dieses Projekt ist als freie Vorlage für Lern-, Test- und Demonstrationszwecke gedacht.

Bei Verwendung für reale Organisationen müssen Inhalt, Rechtliches, Sicherheit und Datenschutz eigenverantwortlich geprüft und angepasst werden.

---

## Kurzfassung

**Feuerwehr Beispielstadt** ist eine fiktive, virtuelle Feuerwehrwache.  
Das Projekt zeigt Struktur, Gestaltung und Grundfunktionen einer Feuerwehrseite.  
Für echte Veröffentlichung ist es ohne professionelle Anpassungen **nicht geeignet**.
