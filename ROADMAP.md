# Roadmap - IFAK Ticket-System

- Plain PHP
- Keine PHP-Frameworks (z.B. Laravel, Symfony)
- Keine Javascript Frameworks und Erweiterungen (z.B. jquery, vue)

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
- Tickets die anderen Agenten zugewiesen sind, können gezielt angezeigt werden. Durch Klick auf den Agentennamen im Header
- Chronologische Reihenfolge der Kommentare zu einem Ticket. Neuste oben!
- "Meine Tickets" soll die "mir" zugewiesenen Tickets zeigen, nicht die von mir erstellten.
- Hinterlegung eines Verfügbarkeitskalenders für jeden Agenten.

---

## To-Do

- Bei Zuweisungen an einen Agenten muss ein entsprechender Text an den Kommentar angehängt werden. Aus dem muss deutlich werden, wer wann an wen zugewiesen hat.
- Ticketzuweisungen nur an innerhalb der Reaktionszeit verfügbare Agenten ermöglichen. Bei normaler Priorität kann es z.B. ausreichen, dass jemand am nächsten Tag das Ticket in Arbeit nehmen kann.
- Benachrichtigung der Agenten per EMail bei:
  - Neuen Tickets (Funktions-Postfach helpdesk@ifak-sozial.de)
  - Ticket-Zuweisungen (individuelle Mail) mit Angabe der Reaktionszeit, die sich aus der Priorität ableitet.
- Benachrichtigung bei gelösten Tickets an Qualitätskontrolle und Ticket-Ersteller (Platzhalter helpdesk@ifak-sozial.de)
- Prioritätsstufe "HOT" für Tickets mit sofortigem Handlungsbedarf

---

# Wichtig /  Hinweise für Codex

- Code wird ausschließlich in **Plain PHP** erzeugt.
- Keine Verwendung von Frameworks.
- Die App soll bei jedem Hoster in einem Standard Webspace durch einfaches kopieren/hochladen einsetzbar sein.
- Wir verwenden zwei SQLite Datenbanken. Eine für die Tickets und die Adressbuch-Datenbank als Grundlage für die Suche nach der aufgebenden Person. Nur wer bei uns beschäftigt ist kann ein Ticket aufgeben. Daher nutzen wir hier das Adressbuch als seperate Ressource.

---

## Langfristig:
- Einsatz der App nicht nur im IT Team, sondern auch für andere interne Supportbereiche, wie z.b. die Haustechnik
- Auswertungen
- Reports
- Wissensmanagement durch aus gelösten Tickets abgeleitete FAQs
- additional_contacts
