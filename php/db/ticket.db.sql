BEGIN TRANSACTION;
CREATE TABLE IF NOT EXISTS "Teams" (
	"TeamID"	INTEGER,
	"TeamName"	TEXT NOT NULL UNIQUE,
	"TeamColor"	TEXT NOT NULL,
	"TeamDescription"	TEXT,
	PRIMARY KEY("TeamID" AUTOINCREMENT)
);
CREATE TABLE IF NOT EXISTS "TicketStatus" (
	"StatusID"	INTEGER,
	"StatusName"	TEXT NOT NULL,
	"ColorCode"	TEXT NOT NULL,
	PRIMARY KEY("StatusID" AUTOINCREMENT)
);
CREATE TABLE IF NOT EXISTS "TicketPriorities" (
	"PriorityID"	INTEGER,
	"PriorityName"	TEXT NOT NULL,
	"ColorCode"	TEXT NOT NULL,
	PRIMARY KEY("PriorityID" AUTOINCREMENT)
);
CREATE TABLE IF NOT EXISTS "Agents" (
	"AgentID"	INTEGER,
	"AgentName"	TEXT NOT NULL,
	"AgentEmail"	TEXT,
	"Token"	TEXT NOT NULL UNIQUE,
	"Active"	BOOLEAN DEFAULT 1,
	"TeamID"	INTEGER NOT NULL,
	FOREIGN KEY("TeamID") REFERENCES "Teams"("TeamID"),
	PRIMARY KEY("AgentID" AUTOINCREMENT)
);
CREATE TABLE IF NOT EXISTS "TicketUpdates" (
	"UpdateID"	INTEGER,
	"TicketID"	INTEGER NOT NULL,
	"UpdatedAt"	TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	"UpdatedByName"	TEXT NOT NULL,
	"UpdateText"	TEXT NOT NULL,
	"IsSolution"	INTEGER DEFAULT 0,
	FOREIGN KEY("TicketID") REFERENCES "Tickets"("TicketID"),
	PRIMARY KEY("UpdateID" AUTOINCREMENT)
);
CREATE TABLE IF NOT EXISTS "TicketAttachments" (
	"AttachmentID"	INTEGER,
	"TicketID"	INTEGER NOT NULL,
	"UpdateID"	INTEGER,
	"FileName"	TEXT NOT NULL,
	"StoragePath"	TEXT NOT NULL,
	"FileSize"	INTEGER NOT NULL,
	"UploadedAt"	TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY("TicketID") REFERENCES "Tickets"("TicketID"),
	FOREIGN KEY("UpdateID") REFERENCES "TicketUpdates"("UpdateID"),
	PRIMARY KEY("AttachmentID" AUTOINCREMENT)
);
CREATE TABLE IF NOT EXISTS "TicketAssignees" (
	"AssignmentID"	INTEGER,
	"TicketID"	INTEGER NOT NULL,
	"AgentID"	INTEGER NOT NULL,
	"AgentName"	TEXT NOT NULL,
	"AssignedAt"	TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY("TicketID") REFERENCES "Tickets"("TicketID"),
	FOREIGN KEY("AgentID") REFERENCES "Agents"("AgentID"),
	PRIMARY KEY("AssignmentID" AUTOINCREMENT)
);
CREATE TABLE IF NOT EXISTS "Tickets" (
	"TicketID"	INTEGER,
	"Title"	TEXT NOT NULL,
	"Description"	TEXT NOT NULL,
	"CreatedAt"	TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	"StatusID"	INTEGER NOT NULL,
	"PriorityID"	INTEGER NOT NULL,
	"TeamID"	INTEGER NOT NULL,
	"CreatedByAgentID"	INTEGER NOT NULL,
	"Source"	TEXT,
	"ContactName"	TEXT NOT NULL,
	"ContactPhone"	TEXT,
	"ContactEmail"	TEXT,
	"ContactEmployeeID"	INTEGER,
	"FacilityID"	INTEGER,
	"LocationID"	INTEGER,
	"DepartmentID"	INTEGER,
	FOREIGN KEY("PriorityID") REFERENCES "TicketPriorities"("PriorityID"),
	FOREIGN KEY("StatusID") REFERENCES "TicketStatus"("StatusID"),
	FOREIGN KEY("TeamID") REFERENCES "Teams"("TeamID"),
	FOREIGN KEY("CreatedByAgentID") REFERENCES "Agents"("AgentID"),
	PRIMARY KEY("TicketID" AUTOINCREMENT)
);
CREATE TABLE IF NOT EXISTS "AvailabilityStatuses" (
	"StatusID"	INTEGER,
	"ShortCode"	TEXT NOT NULL UNIQUE,
	"StatusName"	TEXT NOT NULL,
	"ColorCode"	TEXT NOT NULL,
	PRIMARY KEY("StatusID" AUTOINCREMENT)
);
CREATE TABLE IF NOT EXISTS "AgentAvailability" (
	"AgentID"	INTEGER NOT NULL,
	"Date"	DATE NOT NULL,
	"StatusID"	INTEGER NOT NULL,
	FOREIGN KEY("AgentID") REFERENCES "Agents"("AgentID"),
	FOREIGN KEY("StatusID") REFERENCES "AvailabilityStatuses"("StatusID"),
	PRIMARY KEY("AgentID","Date")
);
CREATE INDEX IF NOT EXISTS "idx_tickets_team" ON "Tickets" (
	"TeamID"
);
CREATE INDEX IF NOT EXISTS "idx_tickets_facility" ON "Tickets" (
	"FacilityID"
);
CREATE INDEX IF NOT EXISTS "idx_tickets_location" ON "Tickets" (
	"LocationID"
);
CREATE INDEX IF NOT EXISTS "idx_tickets_status" ON "Tickets" (
	"StatusID"
);
CREATE INDEX IF NOT EXISTS "idx_availability_agent" ON "AgentAvailability" (
	"AgentID"
);
CREATE INDEX IF NOT EXISTS "idx_availability_date" ON "AgentAvailability" (
	"Date"
);
COMMIT;
