# Roadmap - IFAK Ticket-System

## Phase 0: Ausgangsbasis
- Vorhandenes Ticketsystem, implementiert mit Python (Flask)
- SQLite-Datenbank
- REST-API-Endpunkte:
  - Ticketverwaltung (CRUD)
  - Kommentare & Status-Updates
  - Agentenverwaltung (rudimentär)
- Lokale Test- und Entwicklungsumgebung

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

---

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

- "Meine Tickets" soll die "mir" zugewiesenen Tickets zeigen, nicht die von mir erstellten.
- Tickets die anderen Agenten zugewiesen sind müssen gezielt anzeigbar sein. Durch Klick auf den Agentennamen im Header
- Chronologische Reihenfolge der Kommentare zu einem Ticket. Neuste oben!
- Benachrichtigung der Agenten bei:
  - Neuen Tickets (Funktions-Postfach helpdesk@ifak-sozial.de)
  - Ticket-Zuweisungen (individuelle Mail)

---

# Wichtig /  Hinweise für Codex

- Code wird ab sofort ausschließlich in **Plain PHP** (nicht Flask/Python) erzeugt
- Keine Verwendung von Frameworks
- Die App soll bei jedem Hoster in einem Standard Webspace durch einfaches kopieren/hochladen einsetzbar sein.
- Bestehende Python-Logik kann als semantisches Referenzmodell genutzt werden
- Wir verwenden zwei SQLite Datenbanken. Eine für die Tickets und die Adressbuch-Datenbank als Grundlage für die Suche nach der aufgebenden Person. Nur wer bei uns beschäftigt ist kann ein Ticket aufgeben. Daher nutzen wir hier das Adressbuch als seperate Ressource
- Erstes Ziel: Erreichen des gleichen Funktionsumfangs und Implemetierung der schon gelisteten neuen Features.

---

## Langfristig:
- Einsatz der App nicht nur im IT Team, sondern auch für andere interne Supportbereiche, wie z.b. die Haustechnik
- Auswertungen
- Reports
- Wissensmanagement durch aus gelösten Tickets abgeleitete FAQs
