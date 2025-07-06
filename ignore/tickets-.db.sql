BEGIN TRANSACTION;
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
CREATE TABLE IF NOT EXISTS "Tickets" (
	"TicketID"	INTEGER,
	"Title"	TEXT NOT NULL,
	"Description"	TEXT NOT NULL,
	"CreatedAt"	TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        "CreatedByName" TEXT NOT NULL,
        "Source"        TEXT,
	"StatusID"	INTEGER NOT NULL,
	"PriorityID"	INTEGER NOT NULL,
	"ContactName"	TEXT NOT NULL,
	"ContactPhone"	TEXT,
	"ContactEmail"	TEXT,
	PRIMARY KEY("TicketID" AUTOINCREMENT),
	FOREIGN KEY("PriorityID") REFERENCES "TicketPriorities"("PriorityID"),
	FOREIGN KEY("StatusID") REFERENCES "TicketStatus"("StatusID")
);
CREATE TABLE IF NOT EXISTS "TicketUpdates" (
	"UpdateID"	INTEGER,
	"TicketID"	INTEGER NOT NULL,
	"UpdatedAt"	TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	"UpdatedByName"	TEXT NOT NULL,
	"UpdateText"	TEXT NOT NULL,
	"IsSolution"	INTEGER DEFAULT 0,
	PRIMARY KEY("UpdateID" AUTOINCREMENT),
	FOREIGN KEY("TicketID") REFERENCES "Tickets"("TicketID")
);
CREATE TABLE IF NOT EXISTS "TicketAttachments" (
	"AttachmentID"	INTEGER,
	"TicketID"	INTEGER NOT NULL,
	"UpdateID"	INTEGER,
	"FileName"	TEXT NOT NULL,
	"StoragePath"	TEXT NOT NULL,
	"FileSize"	INTEGER NOT NULL,
	"UploadedAt"	TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY("AttachmentID" AUTOINCREMENT),
	FOREIGN KEY("TicketID") REFERENCES "Tickets"("TicketID"),
	FOREIGN KEY("UpdateID") REFERENCES "TicketUpdates"("UpdateID")
);
CREATE TABLE IF NOT EXISTS "TicketAssignees" (
	"AssignmentID"	INTEGER,
	"TicketID"	INTEGER NOT NULL,
	"AgentID"	INTEGER NOT NULL,
	"AgentName"	TEXT NOT NULL,
	"AssignedAt"	TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY("AssignmentID" AUTOINCREMENT),
	FOREIGN KEY("TicketID") REFERENCES "Tickets"("TicketID")
);
CREATE TABLE IF NOT EXISTS "Agents" (
	"AgentID"	INTEGER,
	"AgentName"	TEXT NOT NULL,
	"AgentEmail"	TEXT,
	"Token"	TEXT NOT NULL UNIQUE,
	"Active"	BOOLEAN DEFAULT 1,
	PRIMARY KEY("AgentID")
);
INSERT INTO "TicketStatus" VALUES (1,'Neu','#007bff');
INSERT INTO "TicketStatus" VALUES (2,'In Arbeit','#ffc107');
INSERT INTO "TicketStatus" VALUES (3,'Rückfrage','#ff8c00');
INSERT INTO "TicketStatus" VALUES (4,'Wartend','#6c757d');
INSERT INTO "TicketStatus" VALUES (5,'Storniert','#dc3545');
INSERT INTO "TicketStatus" VALUES (6,'Gelöst','#28a745');
INSERT INTO "TicketPriorities" VALUES (1,'Niedrig','#95a5a6');
INSERT INTO "TicketPriorities" VALUES (2,'Mittel','#3498db');
INSERT INTO "TicketPriorities" VALUES (3,'Hoch','#e74c3c');
INSERT INTO "Agents" VALUES (1,'Rafael','haeusler@ifak-sozial.de','8kx9m2nv4p7q',1);
INSERT INTO "Agents" VALUES (2,'Adil','raifi@ifak-sozial.de','3w6r8j5h2z9c',1);
INSERT INTO "Agents" VALUES (3,'Martin','drees@ifak-sozial.de','7b4n9xyk6m8s',1);
INSERT INTO "Agents" VALUES (4,'Hussam','albenni@ifak-sozial.de','2q5v8t3f7g9s',1);
INSERT INTO "Agents" VALUES (5,'Ziad','errachidi@ifak-sozial.de','4h7p2w9j5x8r',1);
INSERT INTO "Agents" VALUES (6,'',NULL,'',1);
COMMIT;
