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
    "CreatedByName" TEXT NOT NULL, -- Agent der das Ticket angelegt hat
    "CreatedVia" TEXT, -- Mail, Telefon oder persönliches Gespräch
    "CreatedAt" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
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
