# Roadmap - IFAK Ticket-System

## Aktueller Status
- **Version:** v0.2 (deployed)
- **Status:** Public Beta

---

## v0.2 ✅ **Deployed** (06.06.2025)
**Basis-Funktionalität etabliert**
- Dashboard mit Team-/Status-Filtern
- Ticket-Erstellung mit Adressbuch-Integration
- Verwandte Tickets (Einrichtung/Standort)
- Anhang-Support
- SSL-Setup und Systemd-Service
- Public Beta gestartet

---

## v0.2.1 📋 **Planned**
**Produktiv-Rollout Vorbereitung**

### Features
- Standard-Filter "Meine offenen Tickets"
- Suchfunktion für Tickets
- Erweiterte verwandte Tickets:
  - Gleiche Person
  - Gleiche Einrichtung (falls nicht durch Person erfasst)
  - Gleicher Standort (falls nicht durch Person/Einrichtung erfasst)
  - Duplikat-Vermeidung

### Mobile Optimierung
- Container volle Breite nutzen
- Buttons/Tags kompakter gestalten
- Engere Zeilenabstände
- Mehr Tickets pro Screen sichtbar
- Weniger verschwendeter Whitespace

**Ziel:** Produktiv-Rollout für alle 5 Agenten

---

## v0.2.2 📋 **Planned**
**Workflow-Verbesserungen**
- Push-Notifications (Service Worker)
  - Neue Tickets
  - Ticket-Zuweisungen
  - Kritische Prioritäten
- Warnung bei sehr alten offenen Tickets

**Voraussetzung:** Stabile v0.2.1 und positives Agent-Feedback

### Was in der ersten Nutzungswoche aufgefallen ist
- Wer hat ein Ticket erzeugt?
- "verwandte Tickets" sollten noch offen sein
- Zeitzone

### Regeln (für codex)
- Schreibe alle Änderungen **ausschließlich in den Branch `codex`**.
- Führe **keine Commits und keinen Merge in `main`** durch.
- Kommentiere jede Änderung **direkt im Code** (inline), damit Funktion und Grund erkennbar sind.
- Bearbeite die Punkte der Reihe nach, klar getrennt.
- Verwende vorhandene Felder, Templates und Module, wo sinnvoll.
- Lege neue Hilfsfunktionen (z.\u200bB. für Mailversand) in separaten Modulen ab (`mailer.py` etc.).
