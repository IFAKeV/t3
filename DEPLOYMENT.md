# Deployment-Dokumentation Ticket-System

Wild und unkonventionell

## Übersicht
Dokumentation für das Deployment von Code-Änderungen und Datenbank-Updates auf das Produktivsystem.

## Systemstruktur
- **Entwicklung:** Lokales System (Mac M3)
- **Produktion:** `/opt/ifakticket/app/` (Debian Server)
- **Service:** `ifakticket` (systemd)
- **Port:** 5001 (HTTPS)
- **User:** `ifakticket` (Service-Account ohne Shell)

## Code-Deployment

### 1. Branch-basiertes Deployment
```bash
# Auf Produktivsystem als root
cd /opt/ifakticket/app

# Service stoppen
systemctl stop ifakticket

# Remote-Branches aktualisieren
sudo -u ifakticket git -C /opt/ifakticket/app fetch origin

# Verfügbare Branches prüfen
sudo -u ifakticket git -C /opt/ifakticket/app branch -a

# Zu gewünschtem Branch wechseln
sudo -u ifakticket git -C /opt/ifakticket/app checkout [branch-name]
sudo -u ifakticket git -C /opt/ifakticket/app pull origin [branch-name]

# Service starten
systemctl start ifakticket
systemctl status ifakticket
```

### 2. Status-Checks
```bash
# Git-Status prüfen
sudo -u ifakticket git -C /opt/ifakticket/app status

# Service-Status
systemctl status ifakticket

# Logs bei Problemen
journalctl -u ifakticket -f
```

## Datenbank-Updates

### 1. SQLite-Dateien übertragen
```bash
# Vom Entwicklungssystem
scp db/tickets.db rafael@192.168.0.2:/tmp/
# Optional: scp db/ifak.db rafael@192.168.0.2:/tmp/
```

### 2. Datenbank austauschen
```bash
# Auf Produktivsystem als root
systemctl stop ifakticket

# Backup der aktuellen DB
cp /opt/ifakticket/app/db/tickets.db /opt/ifakticket/app/db/tickets.db.backup-$(date +%Y%m%d-%H%M)

# Neue DB einsetzen
mv /tmp/tickets.db /opt/ifakticket/app/db/
chown ifakticket:ifakticket /opt/ifakticket/app/db/tickets.db

# Service starten
systemctl start ifakticket
```

## Schema-Migration (bei DB-Struktur-Änderungen)

### Problem: "table has X columns but Y values were supplied"
Tritt auf wenn neue Spalten hinzugefügt wurden.

### Lösung: Temporäre Tabelle
```sql
-- 1. Foreign Key Constraints deaktivieren
PRAGMA foreign_keys = OFF;

-- 2. Neue Tabelle mit erweiterten Spalten erstellen
CREATE TABLE IF NOT EXISTS "Tickets_neu" (
    -- Vollständiges neues Schema hier einfügen
);

-- 3. Daten übertragen mit Default-Werten für neue Spalten
INSERT INTO Tickets_neu (
    -- Nur existierende Spalten auflisten
)
SELECT 
    -- Entsprechende Spalten + Default-Werte für neue Spalten
FROM Tickets;

-- 4. Tabellen tauschen
DROP TABLE Tickets;
ALTER TABLE Tickets_neu RENAME TO Tickets;

-- 5. Foreign Key Constraints wieder aktivieren
PRAGMA foreign_keys = ON;
```

## Wichtige Pfade
- **Produktiv-App:** `/opt/ifakticket/app/`
- **Datenbanken:** `/opt/ifakticket/app/db/`
- **SSL-Zertifikate:** `/opt/ifakticket/cert.pem`, `/opt/ifakticket/key.pem`
- **Service-Config:** `/etc/systemd/system/ifakticket.service`

## Git-Befehle für ifakticket User
Da der `ifakticket` User keine Shell hat, alle Git-Befehle als root mit:
```bash
sudo -u ifakticket git -C /opt/ifakticket/app [git-befehl]
```

## Rollback-Strategie
```bash
# Code-Rollback
sudo -u ifakticket git -C /opt/ifakticket/app checkout main
systemctl restart ifakticket

# DB-Rollback
systemctl stop ifakticket
cp /opt/ifakticket/app/db/tickets.db.backup-[timestamp] /opt/ifakticket/app/db/tickets.db
systemctl start ifakticket
```

## Troubleshooting
- **Service startet nicht:** `journalctl -u ifakticket -f`
- **Git-Probleme:** Besitzverhältnisse prüfen (`ls -la`)
- **DB-Probleme:** Backup wiederherstellen
- **Port-Konflikte:** `netstat -tlnp | grep 5001`
