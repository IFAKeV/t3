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

### Features
- [ ] Standard-Filter "Meine offenen Tickets"
- [ ] "CreatedBy" - Wer hat ein Ticket angelegt?
- [ ] Angelegt aufgrund einer Mail, eines Anrufs, oder weil man sich auf dem Flur getroffen hat
- [ ] Suchfunktion für Tickets
- [ ] Erweiterte verwandte Tickets:
  - [ ] "verwandte Tickets" nur zeigen, wenn nicht gelöst
  - [ ] Gleiche Person
  - [ ] Gleiche Einrichtung (falls nicht durch Person erfasst)
  - [ ] Gleicher Standort (falls nicht durch Person/Einrichtung erfasst)
  - [ ] Duplikat-Vermeidung
- [ ] Push-Notifications (Service Worker)
  - [ ] Neue Tickets
  - [ ] Ticket-Zuweisungen
  - [ ] Kritische Prioritäten
- [ ] Warnung bei sehr alten offenen Tickets nach einzustellendem Schwellwert. 
- [ ] Zusätzlich zu "erstellt am" möchte ich im Dashboard noch das Alter des Tickets in Tagen sehen

### Mobile Optimierung
- [ ] Container volle Breite nutzen
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

