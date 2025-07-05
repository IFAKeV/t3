-- IFAK Ticketsystem Database Schema v2.0
-- Mit Teams und Organisationsebenen

BEGIN TRANSACTION;

-- Teams für Multi-Team Support
CREATE TABLE IF NOT EXISTS "Teams" (
    "TeamID" INTEGER PRIMARY KEY AUTOINCREMENT,
    "TeamName" TEXT NOT NULL UNIQUE,
    "TeamColor" TEXT NOT NULL,
    "TeamDescription" TEXT
);

-- Ticket-Status
CREATE TABLE IF NOT EXISTS "TicketStatus" (
    "StatusID" INTEGER PRIMARY KEY AUTOINCREMENT,
    "StatusName" TEXT NOT NULL,
    "ColorCode" TEXT NOT NULL
);

-- Ticket-Prioritäten
CREATE TABLE IF NOT EXISTS "TicketPriorities" (
    "PriorityID" INTEGER PRIMARY KEY AUTOINCREMENT,
    "PriorityName" TEXT NOT NULL,
    "ColorCode" TEXT NOT NULL
);

-- Agenten mit Team-Zugehörigkeit
CREATE TABLE IF NOT EXISTS "Agents" (
    "AgentID" INTEGER PRIMARY KEY AUTOINCREMENT,
    "AgentName" TEXT NOT NULL,
    "AgentEmail" TEXT,
    "Token" TEXT NOT NULL UNIQUE,
    "Active" BOOLEAN DEFAULT 1,
    "TeamID" INTEGER NOT NULL,
    FOREIGN KEY("TeamID") REFERENCES "Teams"("TeamID")
);

-- Erweiterte Tickets-Tabelle
CREATE TABLE IF NOT EXISTS "Tickets" (
    "TicketID" INTEGER PRIMARY KEY AUTOINCREMENT,
    "Title" TEXT NOT NULL,
    "Description" TEXT NOT NULL,
    "CreatedAt" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "CreatedByName" TEXT NOT NULL,
    "Source" TEXT,
    "StatusID" INTEGER NOT NULL,
    "PriorityID" INTEGER NOT NULL,
    "TeamID" INTEGER NOT NULL,
    "ContactName" TEXT NOT NULL,
    "ContactPhone" TEXT,
    "ContactEmail" TEXT,
    "ContactEmployeeID" INTEGER,
    "FacilityID" INTEGER,
    "LocationID" INTEGER,
    "DepartmentID" INTEGER,
    FOREIGN KEY("PriorityID") REFERENCES "TicketPriorities"("PriorityID"),
    FOREIGN KEY("StatusID") REFERENCES "TicketStatus"("StatusID"),
    FOREIGN KEY("TeamID") REFERENCES "Teams"("TeamID")
);

-- Weitere Tabellen...
CREATE TABLE IF NOT EXISTS "TicketUpdates" (
    "UpdateID" INTEGER PRIMARY KEY AUTOINCREMENT,
    "TicketID" INTEGER NOT NULL,
    "UpdatedAt" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "UpdatedByName" TEXT NOT NULL,
    "UpdateText" TEXT NOT NULL,
    "IsSolution" INTEGER DEFAULT 0,
    FOREIGN KEY("TicketID") REFERENCES "Tickets"("TicketID")
);

CREATE TABLE IF NOT EXISTS "TicketAttachments" (
    "AttachmentID" INTEGER PRIMARY KEY AUTOINCREMENT,
    "TicketID" INTEGER NOT NULL,
    "UpdateID" INTEGER,
    "FileName" TEXT NOT NULL,
    "StoragePath" TEXT NOT NULL,
    "FileSize" INTEGER NOT NULL,
    "UploadedAt" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY("TicketID") REFERENCES "Tickets"("TicketID"),
    FOREIGN KEY("UpdateID") REFERENCES "TicketUpdates"("UpdateID")
);

CREATE TABLE IF NOT EXISTS "TicketAssignees" (
    "AssignmentID" INTEGER PRIMARY KEY AUTOINCREMENT,
    "TicketID" INTEGER NOT NULL,
    "AgentID" INTEGER NOT NULL,
    "AgentName" TEXT NOT NULL,
    "AssignedAt" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY("TicketID") REFERENCES "Tickets"("TicketID"),
    FOREIGN KEY("AgentID") REFERENCES "Agents"("AgentID")
);

-- Initial-Daten
INSERT OR IGNORE INTO "Teams" VALUES (1, 'IT', '#007bff', 'IT-Support und Systemadministration');
INSERT OR IGNORE INTO "Teams" VALUES (2, 'Haustechnik', '#28a745', 'Gebäudetechnik und Instandhaltung');

INSERT OR IGNORE INTO "TicketStatus" VALUES (1, 'Neu', '#007bff');
INSERT OR IGNORE INTO "TicketStatus" VALUES (2, 'In Arbeit', '#ffc107');
INSERT OR IGNORE INTO "TicketStatus" VALUES (3, 'Rückfrage', '#ff8c00');
INSERT OR IGNORE INTO "TicketStatus" VALUES (4, 'Wartend', '#6c757d');
INSERT OR IGNORE INTO "TicketStatus" VALUES (5, 'Storniert', '#dc3545');
INSERT OR IGNORE INTO "TicketStatus" VALUES (6, 'Gelöst', '#28a745');

INSERT OR IGNORE INTO "TicketPriorities" VALUES (1, 'Niedrig', '#95a5a6');
INSERT OR IGNORE INTO "TicketPriorities" VALUES (2, 'Mittel', '#3498db');
INSERT OR IGNORE INTO "TicketPriorities" VALUES (3, 'Hoch', '#e74c3c');

INSERT OR IGNORE INTO "Agents" VALUES (1, 'Rafael', 'haeusler@ifak-sozial.de', '8kx9m2nv4p7q', 1, 1);
INSERT OR IGNORE INTO "Agents" VALUES (2, 'Adil', 'raifi@ifak-sozial.de', '3w6r8j5h2z9c', 1, 1);
INSERT OR IGNORE INTO "Agents" VALUES (3, 'Martin', 'drees@ifak-sozial.de', '7b4n9xyk6m8s', 1, 1);
INSERT OR IGNORE INTO "Agents" VALUES (4, 'Hussam', 'albenni@ifak-sozial.de', '2q5v8t3f7g9s', 1, 1);
INSERT OR IGNORE INTO "Agents" VALUES (5, 'Ziad', 'errachidi@ifak-sozial.de', '4h7p2w9j5x8r', 1, 1);

-- Performance-Indizes
CREATE INDEX IF NOT EXISTS "idx_tickets_team" ON "Tickets"("TeamID");
CREATE INDEX IF NOT EXISTS "idx_tickets_facility" ON "Tickets"("FacilityID");
CREATE INDEX IF NOT EXISTS "idx_tickets_location" ON "Tickets"("LocationID");
CREATE INDEX IF NOT EXISTS "idx_tickets_status" ON "Tickets"("StatusID");

COMMIT;