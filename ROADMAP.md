# Roadmap - IFAK Ticket-System

Am Anfang stand die Idee: **Wir entwickeln ein Ticketsystem**

## Phase 0: Ausgangsbasis
- Die Sprachmodelle sagen, sie sprechen am besten Python/Flask, also nehmen wir das um ein erstes System maßgeblich mit Claude zu entwickeln. 
- SQLite-Datenbank
- REST-API-Endpunkte:
  - Ticketverwaltung (CRUD)
  - Kommentare & Status-Updates
  - Agentenverwaltung (rudimentär)
- Lokale Test- und Entwicklungsumgebung

Dann kam zum einen die Erkenntnis, dass kurze und einfache Scripte mit KI ganz toll funktionieren und Entwicklungszeiten dramatisch verkürzen. Komplexere Dinge über ein Chatfenster aber auf Dauer kompliziert werden. Und das das Hosting eines Flask Systems auf dem Markt quasi nicht verfügbar ist. Auf einem Lokalen, ganzen oder vServer kein Problem, nur doch aufwändiger als ein Standard PHP Hosting, welches es bei jedem Anbieter gibt.
Die Entwicklung schwenkt dazu von Claude auf Codex, der mit diesem Repo spricht und was Dinge nach einer Eingewöhnungsphase deutlich einfacher und das ganze System wesentlich mächtiger macht.

## Phase 1: Migration auf PHP
- Ziel: Vollständiger Umstieg von Python/Flask auf Plain PHP
- Keine PHP-Frameworks (z.B. Laravel, Symfony)
- Keine Javascript Frameworks und Erweiterungen (z.B. jquery, vue)
- Fokus auf einfache, modulare Struktur mit klarer Trennung von:
  - API-Handlern 
  - Datenbankzugriffen
  - Reiner Serverlogik
- REST-Logik bleibt gleich
- API-Struktur übernehmen
- Neue PHP-Funktionen analog zu bestehenden Endpunkten implementieren

## Phase 2: Web-Frontend
- Umsetzung eines responsiven Frontends

Die responsive Umsetzung ist noch gar nicht abgeschlossen, da keimt die Idee ob codex in der Lage ist, aus der Vorlage von gut zwei Monaten Betrieb und schrittweiser Weiterentwicklung der php-Version alle Änderungen und Erweiterungen auf die Flask-Version zu übertragen.

## Features

- Dashboard mit Team-/Status-Filtern
- Ticket-Erstellung mit Adressbuch-Integration
- "Verwandte" Tickets (Ticket der gleichen Person/Einrichtung/Standort)
  - "verwandte Tickets" nur zeigen, wenn nicht gelöst
  - Gleiche Person
  - Gleiche Einrichtung (falls nicht durch Person erfasst)
  - Gleicher Standort (falls nicht durch Person/Einrichtung erfasst)
  - Duplikat-Vermeidung
- Anhang-Support
- Standard-Filter "Meine offenen Tickets"
- "CreatedBy" - Wer hat ein Ticket angelegt?
- Angelegt aufgrund: Einer Mail, eines Anrufs, oder weil man sich auf dem Flur getroffen hat
- Suchfunktion für Tickets
  - Volltext
  - Alle Tickets einer Person/Einrichtung/Standort - Wie "verwandte" Tickets
- Warnung/Hinweis bei sehr alten offenen Tickets nach einzustellendem Schwellwert durch farbige Hervorhebung der Zeile im Dashboard
- Zusätzlich zu "erstellt am" möchte ich im Dashboard noch das Alter des Tickets in Tagen sehen. Auch hier eine farbige Hervorhebung bei erreichen eines zu definierenden Schwellwerts

---

## To-Do

- Benachrichtigung der Agenten bei:
  - Neuen Tickets (Funktions-Postfach helpdesk@ifak-sozial.de)
  - Ticket-Zuweisungen (individuelle Mail)

---

# Wichtig /  Hinweise für Codex

Aktuelles Ziel:
Im Ordner PHP befindet sich eine PHP Version der Ticketlösung, die jetzt gut zwei Monate Testbetrieb hinter sich hat und in der viele Erweiterungen umgesetzt wurden.
Übertrage **ALLE** diese Features und Funktionen in die Flask-Version! Analysiere dafür den Quellcode sorgfältig. Dafür steht er vollständig zur Verfügung.

---

## Langfristig:
- Einsatz der App nicht nur im IT Team, sondern auch für andere interne Supportbereiche, wie z.b. die Haustechnik
- Auswertungen
- Reports
- Wissensmanagement durch aus gelösten Tickets abgeleitete FAQs
- additional_contacts
