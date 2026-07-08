# Freiwillige Feuerwehr Beispielstadt – vollständiges Musterprojekt

Dieses Projekt ist eine vollständige mehrseitige Feuerwehr-Webseite als statisches HTML/CSS/JavaScript-Projekt mit optionalem PHP-Kontaktformular.

## Enthaltene Seiten

- Startseite (`index.html`)
- Über uns
- Gruppen und Unterseiten: Einsatzabteilung, Jugendfeuerwehr, Kinderfeuerwehr, Ehrenabteilung, Förderverein, Ausbildung
- Mitglieder
- Fahrzeuge mit Detailseiten: LF 20, HLF 20, TLF 3000, DLK 23/12, ELW 1, MTF
- Gerätehaus
- Einsätze
- Termine / Dienstplan
- Brandschutztipps
- Galerie
- Downloads
- Kontakt
- Notruf 112
- Impressum
- Datenschutz
- Barrierefreiheit
- 404-Seite

## Technik

- HTML5
- CSS3
- Vanilla JavaScript
- Keine externen Bibliotheken
- Responsive Hamburger-Menü
- Dark-/Light-Mode
- Filter für Einsatzübersicht
- Scroll-Reveal-Animationen
- SVG-Platzhaltergrafiken
- Optionales PHP-Kontaktformular unter `php/kontakt_senden.php`

## Anpassung

Suche im Projekt nach folgenden Platzhaltern und ersetze sie:

- `Beispielstadt`
- `Musterstraße 12`
- `12345`
- `kontakt@feuerwehr-beispielstadt.de`
- `[Name des Diensteanbieters / Trägers]`
- `[Straße Hausnummer]`
- `[PLZ Ort]`
- `[Vorname Nachname, Funktion]`

## Rechtlicher Hinweis

Impressum und Datenschutz sind Vorlagen. Vor Veröffentlichung müssen sie mit echten Angaben ergänzt und rechtlich geprüft werden.

## Lokal starten

Einfach `index.html` im Browser öffnen. Für das PHP-Kontaktformular wird ein PHP-fähiger Webserver benötigt.


## Sicheres Kontaktformular mit SQLite

Das Kontaktformular wurde auf `kontakt.php` umgestellt und speichert Anfragen in `storage/kontakt.sqlite`.

Wichtige Punkte:

- PHP 8.x mit PDO SQLite muss aktiv sein.
- Der Ordner `storage/` braucht Schreibrechte für PHP.
- `storage/.htaccess` blockiert direkten Webzugriff bei Apache. Bei Nginx muss zusätzlich eine passende Deny-Regel gesetzt werden.
- Das Formular nutzt CSRF-Token, serverseitiges Rechen-Captcha, Honeypot, Rate-Limit, Längenlimits und Prepared Statements.
- Ausgaben werden mit `htmlspecialchars()` escaped.
- Der optionale Admin-Viewer liegt unter `php/kontakt_admin.php`.
- Für den Admin-Viewer müssen serverseitig Umgebungsvariablen gesetzt werden:
  - `CONTACT_ADMIN_USER`
  - `CONTACT_ADMIN_PASSWORD`

Für echten Produktivbetrieb sollten zusätzlich HTTPS, regelmäßige Backups, Logging, Monitoring, restriktive Dateirechte und ein vollwertiges Rollen-/Login-System eingerichtet werden.


## Adminbereich

Auf der Kontaktseite `kontakt.php` befindet sich unten rechts ein kleines Schloss. Darüber öffnet sich der Adminbereich unter:

`admin/index.php`

Beim ersten Aufruf wird automatisch `admin/setup.php` geöffnet. Dort wird der erste Administrator angelegt. Danach ist die Setup-Seite gesperrt.

Funktionen:

- Login mit PHP-Session
- Dashboard mit Kennzahlen
- Nachrichtenübersicht
- Nachrichtendetailansicht
- Status: `new`, `read`, `done`
- Nachrichten löschen
- Benutzerverwaltung
- Rollen: `admin`, `editor`
- Benutzer aktivieren/deaktivieren
- Passwortänderung mit `password_hash()` / `password_verify()`

Sicherheitsmaßnahmen:

- CSRF-Schutz für Login, Setup und Adminformulare
- Session-Regeneration nach Login
- Prepared Statements
- Rollenprüfung für Benutzerverwaltung
- XSS-Schutz durch `htmlspecialchars()`
- kein Default-Passwort
- First-Run-Setup statt fest eingebautem Admin
- Schutz gegen Löschen/Sperren des eigenen Accounts
- Schutz gegen Löschen des letzten aktiven Admins

Hinweis: Für Produktivbetrieb zusätzlich HTTPS, restriktive Serverrechte, Backups, Webserver-Deny-Regeln für `storage/`, sichere Passwortrichtlinien und ggf. 2FA einplanen.


## Kontaktformular-Fix

Das Rechen-Captcha wird in `kontakt.php` serverseitig erzeugt. Die Seite darf nicht als statische Datei oder als `kontakt.html` geöffnet werden.

Richtig lokal starten:

```bash
php -S localhost:8000
```

Danach im Browser öffnen:

```text
http://localhost:8000/kontakt.php
```

Wenn das Captcha nicht angezeigt wird, läuft die Seite nicht durch PHP.
