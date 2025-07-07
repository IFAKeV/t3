import sqlite3
from flask import g
from config import DATABASE


def get_ticket_db():
    """Verbindung zur Ticket-Datenbank herstellen"""
    if "ticket_db" not in g:
        g.ticket_db = sqlite3.connect(DATABASE["ticket_db"])
        g.ticket_db.row_factory = sqlite3.Row
    return g.ticket_db


def get_address_db():
    """Verbindung zur Adressbuch-Datenbank herstellen"""
    if "address_db" not in g:
        g.address_db = sqlite3.connect(DATABASE["address_db"])
        g.address_db.row_factory = sqlite3.Row
    return g.address_db


def close_db(e=None):
    """Datenbankverbindungen schließen"""
    ticket_db = g.pop("ticket_db", None)
    if ticket_db is not None:
        ticket_db.close()

    address_db = g.pop("address_db", None)
    if address_db is not None:
        address_db.close()


def query_db(query, args=(), one=False, db_type="ticket"):
    """Allgemeine Datenbankabfrage - gibt IMMER normale Dicts zurück"""
    if db_type == "address":
        db = get_address_db()
    else:
        db = get_ticket_db()

    cur = db.execute(query, args)
    rows = cur.fetchall()
    cur.close()

    if one:
        return dict(rows[0]) if rows else None
    return [dict(row) for row in rows]


def insert_db_original(table, fields, values, db_type="ticket"):
    """Daten in die Datenbank einfügen"""
    if db_type == "address":
        db = get_address_db()
    else:
        db = get_ticket_db()

    placeholders = ", ".join(["?"] * len(values))
    query = (
        f"INSERT INTO {table} ({', '.join(fields)}) VALUES ({placeholders})"
    )

    cur = db.execute(query, values)
    db.commit()
    return cur.lastrowid


def insert_db(table, fields, values, db_type="ticket"):
    """Daten in die Datenbank einfügen - DEBUG-Version"""
    if db_type == "address":
        db = get_address_db()
    else:
        db = get_ticket_db()

    placeholders = ", ".join(["?"] * len(values))
    query = (
        f"INSERT INTO {table} ({', '.join(fields)}) VALUES ({placeholders})"
    )

    # DEBUG: SQL ausgeben
    print(f"DEBUG SQL: {query}")
    print(f"DEBUG VALUES: {values}")

    cur = db.execute(query, values)
    db.commit()
    return cur.lastrowid


def update_db(table, id_field, id_value, fields, values, db_type="ticket"):
    """Daten in der Datenbank aktualisieren"""
    if db_type == "address":
        db = get_address_db()
    else:
        db = get_ticket_db()

    set_clause = ", ".join([f"{field} = ?" for field in fields])
    query = f"UPDATE {table} SET {set_clause} WHERE {id_field} = ?"

    values.append(id_value)
    db.execute(query, values)
    db.commit()


# TEAMS
def get_all_teams():
    """Alle verfügbaren Teams abrufen"""
    return query_db(
        "SELECT TeamID, TeamName, TeamColor, TeamDescription FROM Teams ORDER BY TeamName"
    )


def get_team_by_id(team_id):
    """Team nach ID abrufen"""
    return query_db(
        "SELECT TeamID, TeamName, TeamColor, TeamDescription FROM Teams WHERE TeamID = ?",
        (team_id,),
        one=True,
    )


# AGENTS
def load_agents():
    """Alle aktiven Agenten mit Team-Informationen laden"""
    return query_db(
        """
        SELECT a.AgentID, a.AgentName, a.AgentEmail, a.Token, a.Active, a.TeamID,
               t.TeamName, t.TeamColor
        FROM Agents a
        JOIN Teams t ON a.TeamID = t.TeamID
        WHERE a.Active = 1
        ORDER BY a.AgentName
    """
    )


def get_agent_by_token(token):
    """Agent nach Token finden - mit Team-Informationen"""
    return query_db(
        """
        SELECT a.AgentID, a.AgentName, a.AgentEmail, a.Token, a.Active, a.TeamID,
               t.TeamName, t.TeamColor
        FROM Agents a
        JOIN Teams t ON a.TeamID = t.TeamID
        WHERE a.Token = ? AND a.Active = 1
    """,
        (token,),
        one=True,
    )


# STATUS & PRIORITÄTEN
def get_all_statuses():
    """Alle verfügbaren Ticket-Status abrufen"""
    return query_db(
        "SELECT StatusID, StatusName, ColorCode FROM TicketStatus ORDER BY StatusID"
    )


def get_all_priorities():
    """Alle verfügbaren Ticket-Prioritäten abrufen"""
    return query_db(
        "SELECT PriorityID, PriorityName, ColorCode FROM TicketPriorities ORDER BY PriorityID"
    )


def get_status_by_id(status_id):
    """Status nach ID abrufen"""
    return query_db(
        "SELECT StatusID, StatusName, ColorCode FROM TicketStatus WHERE StatusID = ?",
        (status_id,),
        one=True,
    )


def get_priority_by_id(priority_id):
    """Priorität nach ID abrufen"""
    return query_db(
        "SELECT PriorityID, PriorityName, ColorCode FROM TicketPriorities WHERE PriorityID = ?",
        (priority_id,),
        one=True,
    )


# TICKETS
def get_tickets_with_filters(
    team_id=None,
    status_filter="open",
    search_term=None,
    agent_id=None,
    assigned_only=False,
):
    """Erweiterte Ticket-Abfrage mit Team-Filtern und Zuweisungen

    Optional kann ein Suchbegriff übergeben werden, um Tickets nach Titel oder
    ID zu filtern. Damit lässt sich eine einfache Ticket-Suche realisieren.
    """
    base_query = """
        SELECT t.TicketID, t.Title, t.Description, t.StatusID, t.PriorityID, t.TeamID,
               t.ContactName, t.ContactPhone, t.ContactEmail,
               a.AgentName AS CreatedByName, t.Source,
               s.StatusName, s.ColorCode as StatusColor,
               p.PriorityName, p.ColorCode as PriorityColor,
               team.TeamName, team.TeamColor,
                strftime('%d.%m.%Y %H:%M', t.CreatedAt, 'localtime') as CreatedAt,
               CAST(julianday('now') - julianday(t.CreatedAt) AS INT) as AgeDays,
               GROUP_CONCAT(ta.AgentName, ', ') as AssignedAgents
        FROM Tickets t
        JOIN TicketStatus s ON t.StatusID = s.StatusID
        JOIN TicketPriorities p ON t.PriorityID = p.PriorityID
        JOIN Teams team ON t.TeamID = team.TeamID
        JOIN Agents a ON t.CreatedByAgentID = a.AgentID
        LEFT JOIN TicketAssignees ta ON t.TicketID = ta.TicketID
    """

    conditions = []
    params = []

    if team_id:
        conditions.append("t.TeamID = ?")
        params.append(team_id)

    if status_filter == "open":
        conditions.append("s.StatusName != 'Gelöst'")
    elif status_filter != "all":
        conditions.append("s.StatusName = ?")
        params.append(status_filter)

    if search_term:
        conditions.append("(t.Title LIKE ? OR t.TicketID = ?)")
        params.append(f"%{search_term}%")
        params.append(search_term if str(search_term).isdigit() else -1)

    if agent_id:
        if assigned_only:
            conditions.append("ta.AgentID = ?")
            params.append(agent_id)
        else:
            conditions.append("(ta.AgentID = ?)")
            params.append(agent_id)
            params.append(agent_id)

    if conditions:
        base_query += " WHERE " + " AND ".join(conditions)

    base_query += " GROUP BY t.TicketID ORDER BY t.CreatedAt DESC"

    return query_db(base_query, params)


def get_ticket_by_id(ticket_id):
    """Vollständige Ticket-Informationen abrufen"""
    return query_db(
        """
        SELECT t.TicketID, t.Title, t.Description, t.StatusID, t.PriorityID, t.TeamID,
               t.ContactName, t.ContactPhone, t.ContactEmail,
               t.ContactEmployeeID, t.FacilityID, t.LocationID, t.DepartmentID,
               a.AgentName AS CreatedByName, t.Source,
               s.StatusName, s.ColorCode as StatusColor,
               p.PriorityName, p.ColorCode as PriorityColor,
               team.TeamName, team.TeamColor,
                strftime('%d.%m.%Y %H:%M', t.CreatedAt, 'localtime') as CreatedAt,
               CAST(julianday('now') - julianday(t.CreatedAt) AS INT) as AgeDays
        FROM Tickets t
        JOIN TicketStatus s ON t.StatusID = s.StatusID
        JOIN TicketPriorities p ON t.PriorityID = p.PriorityID
        JOIN Teams team ON t.TeamID = team.TeamID
        JOIN Agents a ON t.CreatedByAgentID = a.AgentID
        WHERE t.TicketID = ?
    """,
        (ticket_id,),
        one=True,
    )


def search_tickets(search_term, limit=10):
    """Tickets nach Titel oder ID durchsuchen"""
    return query_db(
        """
        SELECT TicketID, Title
        FROM Tickets
        WHERE Title LIKE ? OR CAST(TicketID AS TEXT) LIKE ?
        ORDER BY CreatedAt DESC
        LIMIT ?
        """,
        (f"%{search_term}%", f"%{search_term}%", limit),
    )


# ADRESSBUCH
def search_employees(search_term):
    """Mitarbeiter im Adressbuch suchen"""
    return query_db(
        """
        SELECT e.EmployeeID, e.FirstName, e.LastName, e.Phone, e.Mobile, e.Mail
        FROM Employees e
        WHERE e.FirstName LIKE ? OR e.LastName LIKE ?
        ORDER BY e.LastName, e.FirstName
        LIMIT 10
    """,
        (f"%{search_term}%", f"%{search_term}%"),
        db_type="address",
    )


def get_employee_details(employee_id):
    """Details eines bestimmten Mitarbeiters mit Facility-Informationen holen"""
    return query_db(
        """
        SELECT e.EmployeeID, e.FirstName, e.LastName, e.Phone, e.Mobile, e.Mail,
               f.FacilityID, f.Facility, f.LocationID, f.DepartmentID
        FROM Employees e
        LEFT JOIN FacilityLinks fl ON e.EmployeeID = fl.EmployeeID
        LEFT JOIN Facilities f ON fl.FacilityID = f.FacilityID
        WHERE e.EmployeeID = ?
        LIMIT 1
    """,
        (employee_id,),
        one=True,
        db_type="address",
    )
