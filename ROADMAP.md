# Roadmap - IFAK Ticket-System

## Aktueller Status
- **Version:** v0.2 (deployed)
- **Status:** Public Beta

---

## v0.2 ✅ **Deployed** (06.06.2025)
**Basis-Funktionalität etabliert**

- ✅ Dashboard mit Team-/Status-Filtern
- ✅ Ticket-Erstellung mit Adressbuch-Integration
- ✅ Verwandte Tickets (Einrichtung/Standort)
- ✅ Anhang-Support
- ✅ SSL-Setup und Systemd-Service
- ✅ Public Beta gestartet

---

## v0.2.1 📋 **Planned**
**Produktiv-Rollout Vorbereitung**

### Fixes

- [x] Das System hat noch einen Zeitzonenfehler. Die Uhrzeit liegt zwei Stunden vor der tatsächlichen Ortszeit. Wir sind Europe/Berlin.
- [x] Upload
  - [x] Aktueller Fehler: Meldung über erfolgreichen Upload bei nicht zugelassener Dateiendung

-### Features
- [x] Standard-Filter "Meine offenen Tickets"
- [x] "CreatedBy" - Wer hat ein Ticket angelegt?
- [x] Angelegt aufgrund einer Mail, eines Anrufs, oder weil man sich auf dem Flur getroffen hat
- [x] Suchfunktion für Tickets
- [x] Erweiterte verwandte Tickets:
  - [x] "verwandte Tickets" nur zeigen, wenn nicht gelöst
  - [x] Gleiche Person
  - [x] Gleiche Einrichtung (falls nicht durch Person erfasst)
  - [x] Gleicher Standort (falls nicht durch Person/Einrichtung erfasst)
  - [x] Duplikat-Vermeidung
- [x] Push-Notifications (Service Worker)
  - [ ] Neue Tickets
  - [ ] Ticket-Zuweisungen
  - [ ] Kritische Prioritäten
- [x] Warnung bei sehr alten offenen Tickets nach einzustellendem Schwellwert.
- [x] Zusätzlich zu "erstellt am" möchte ich im Dashboard noch das Alter des Tickets in Tagen sehen

### Mobile Optimierung
 - [x] Container volle Breite nutzen
- [ ] Buttons/Tags kompakter gestalten
- [ ] Engere Zeilenabstände
- [ ] Mehr Tickets pro Screen sichtbar
- [ ] Weniger verschwendeter Whitespace



### Regeln (für codex):
- Schreibe alle Änderungen **ausschließlich in den Branch `codex`**.
- Führe **keine Commits und keinen Merge in `main`** durch.
- Kommentiere jede Änderung **direkt im Code** (inline), damit Funktion und Grund erkennbar sind.
- Bearbeite die Punkte der Reihe nach, klar getrennt.
- Verwende vorhandene Felder, Templates und Module, wo sinnvoll.
- Lege neue Hilfsfunktionen (z. B. für Mailversand) in separaten Modulen ab (`mailer.py` etc.).

