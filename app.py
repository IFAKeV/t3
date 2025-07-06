import os
import time
from datetime import datetime
from flask import (
    Flask,
    render_template,
    request,
    redirect,
    url_for,
    jsonify,
    g,
    flash,
    send_from_directory,
)
from werkzeug.utils import secure_filename
from PIL import Image

# Projektinterne Imports
from config import (
    DATABASE,
    UPLOAD_FOLDER,
    ALLOWED_EXTENSIONS,
    MAX_CONTENT_LENGTH,
    SECRET_KEY,
    DEBUG,
)
from database import (
    get_ticket_db,
    get_address_db,
    close_db,
    query_db,
    insert_db,
    update_db,
    get_all_teams,
    get_all_statuses,
    get_all_priorities,
    load_agents,
    get_agent_by_token,
    get_tickets_with_filters,
    get_ticket_by_id,
    get_status_by_id,
    get_priority_by_id,
    search_tickets,
)
from addressbook import (
    search_employees,
    get_employee_details,
    format_contact_info,
    get_facility_info,
    get_location_info,
    get_department_info,
    get_addressbook_date,
)

# Zeitzone festlegen, damit Zeiten der Datenbank stimmen
os.environ["TZ"] = "Europe/Berlin"
time.tzset()

# Flask App initialisieren
app = Flask(__name__)
app.config["SECRET_KEY"] = SECRET_KEY
app.config["DEBUG"] = DEBUG
app.config["UPLOAD_FOLDER"] = UPLOAD_FOLDER
app.config["MAX_CONTENT_LENGTH"] = MAX_CONTENT_LENGTH

# Upload-Ordner sicherstellen
os.makedirs(UPLOAD_FOLDER, exist_ok=True)


# ==============================================
# CONTEXT PROCESSORS & UTILITIES
# ==============================================


@app.context_processor
def inject_global_variables():
    """Fügt allen Templates globale Variablen hinzu"""
    return {
        "current_year": datetime.now().year,
        "addressbook_date": get_addressbook_date(),
        "current_agent": getattr(g, "current_agent", None),
    }


@app.teardown_appcontext
def close_connection(exception):
    """Datenbankverbindungen schließen"""
    close_db()


def allowed_file(filename):
    """Prüft, ob Dateiendung erlaubt ist"""
    return "." in filename and filename.rsplit(".", 1)[1].lower() in ALLOWED_EXTENSIONS


def optimize_image(file_path, max_size=(800, 800)):
    """Bilder optimieren"""
    try:
        with Image.open(file_path) as img:
            img.thumbnail(max_size)
            img.save(file_path, optimize=True, quality=85)
    except Exception as e:
        print(f"Fehler bei der Bildoptimierung: {e}")


# ==============================================
# AUTHENTICATION & MIDDLEWARE
# ==============================================


@app.before_request
def check_token():
    """Token-basierte Authentifizierung"""
    # Öffentliche Pfade ausnehmen
    public_paths = ["/static", "/login"]
    if any(request.path.startswith(path) for path in public_paths):
        return

    # Token aus Cookie holen
    token = request.cookies.get("agent_token")
    if not token:
        return redirect(url_for("login"))

    # Agent anhand Token finden
    current_agent = get_agent_by_token(token)
    if not current_agent:
        return redirect(url_for("login"))

    # Agent-Info in g speichern für Templates
    g.current_agent = current_agent


@app.route("/login", methods=["GET", "POST"])
def login():
    """Login-Route"""
    if request.method == "POST":
        token = request.form.get("token")
        agent = get_agent_by_token(token)

        if agent:
            response = redirect(url_for("dashboard"))
            # cookie für 30 Tage setzen
            response.set_cookie("agent_token", token, max_age=30 * 24 * 60 * 60)
            return response

        flash("Ungültiges Token", "error")
        return render_template("login.html")

    return render_template("login.html")


@app.route("/logout")
def logout():
    """Logout-Route"""
    response = redirect(url_for("login"))
    response.delete_cookie("agent_token")
    flash("Sie wurden abgemeldet", "info")
    return response


# ==============================================
# MAIN ROUTES
# ==============================================


@app.route("/")
def dashboard():
    """Dashboard mit Team-Filter"""
    # Filter aus Query-Parametern
    team_filter = request.args.get("team", "my_team")
    status_filter = request.args.get("status", "open")
    search_term = request.args.get("q", "").strip()
    include_closed = request.args.get("include_closed") == "1"

    # Team-ID bestimmen
    if team_filter == "my_team":
        team_id = g.current_agent["TeamID"]
    elif team_filter == "all":
        team_id = None
    else:
        team_id = int(team_filter) if team_filter.isdigit() else None

    # Tickets laden
    tickets = get_tickets_with_filters(
        team_id=team_id,
        status_filter=status_filter,
        search_term=search_term or None,
        include_closed=include_closed,
    )

    # Teams und Status für Filter laden
    teams = get_all_teams()
    statuses = get_all_statuses()

    return render_template(
        "dashboard.html",
        tickets=tickets,
        teams=teams,
        statuses=statuses,
        current_team_filter=team_filter,
        current_status_filter=status_filter,
        search_term=search_term,
        include_closed=include_closed,
    )


@app.route("/ticket/new", methods=["GET", "POST"])
def new_ticket():
    """Neues Ticket erstellen"""
    statuses = get_all_statuses()
    new_status_id = statuses[0]["StatusID"] if statuses else 1
    if request.method == "POST":
        # Basis-Ticket-Daten
        title = request.form.get("title")
        description = request.form.get("description")
        priority_id = request.form.get("priority_id")
        team_id = request.form.get("team_id", g.current_agent["TeamID"])

        # Kontakt-Daten
        contact_name = request.form.get("contact_name")
        contact_phone = request.form.get("contact_phone")
        contact_email = request.form.get("contact_email")
        contact_employee_id = request.form.get("contact_employee_id") or None

        # Organisations-Daten (automatisch oder manuell)
        facility_id = request.form.get("facility_id") or None
        location_id = request.form.get("location_id") or None
        department_id = request.form.get("department_id") or None

        # Ticket erstellen
        ticket_id = insert_db(
            "Tickets",
            [
                "Title",
                "Description",
                "PriorityID",
                "TeamID",
                "StatusID",
                "ContactName",
                "ContactPhone",
                "ContactEmail",
                "ContactEmployeeID",
                "FacilityID",
                "LocationID",
                "DepartmentID",
            ],
            [
                title,
                description,
                priority_id,
                team_id,
                new_status_id,
                contact_name,
                contact_phone,
                contact_email,
                contact_employee_id,
                facility_id,
                location_id,
                department_id,
            ],
        )

        # Agent-Zuweisung verarbeiten
        assigned_agent_id = request.form.get("assigned_agent")
        if assigned_agent_id:
            assigned_agent = query_db(
                "SELECT AgentName FROM Agents WHERE AgentID = ?",
                (assigned_agent_id,),
                one=True,
            )
            if assigned_agent:
                insert_db(
                    "TicketAssignees",
                    ["TicketID", "AgentID", "AgentName"],
                    [ticket_id, assigned_agent_id, assigned_agent["AgentName"]],
                )

        # Anhang verarbeiten
        if "attachment" in request.files:
            file = request.files["attachment"]
            if file and file.filename:
                if allowed_file(file.filename):
                    filename = secure_filename(file.filename)
                    timestamp = int(time.time())
                    save_filename = f"{timestamp}_{filename}"
                    file_path = os.path.join(app.config["UPLOAD_FOLDER"], save_filename)

                    file.save(file_path)

                    # Bilder optimieren
                    if filename.lower().endswith((".png", ".jpg", ".jpeg", ".gif")):
                        optimize_image(file_path)

                    # Anhang in DB speichern
                    file_size = os.path.getsize(file_path)
                    insert_db(
                        "TicketAttachments",
                        ["TicketID", "FileName", "StoragePath", "FileSize"],
                        [ticket_id, filename, save_filename, file_size],
                    )
                else:
                    # Fehlermeldung bei unzulässiger Dateiendung
                    flash("Dateiendung nicht erlaubt", "error")

        flash("Ticket erfolgreich erstellt", "success")
        return redirect(url_for("view_ticket", ticket_id=ticket_id))

    # GET: Formular anzeigen
    teams = get_all_teams()
    priorities = get_all_priorities()
    agents = load_agents()

    return render_template(
        "ticket_create.html", teams=teams, priorities=priorities, agents=agents
    )


@app.route("/ticket/<int:ticket_id>/update", methods=["POST"])
def update_ticket(ticket_id):
    """Ticket aktualisieren - Status, Priorität, Kommentar, Zuweisung"""
    ticket = get_ticket_by_id(ticket_id)
    if not ticket:
        flash("Ticket nicht gefunden", "error")
        return redirect(url_for("dashboard"))

    # Formulardaten
    new_status = request.form.get("status_id")
    new_priority = request.form.get("priority_id")
    update_text = request.form.get("update_text", "").strip()
    is_solution = 1 if request.form.get("is_solution") else 0
    assign_agent = request.form.get("assign_agent")

    # Wenn Lösungs-Checkbox angehakt, automatisch Status auf "Gelöst"
    if is_solution:
        # Gelöst-Status finden
        solved_status = query_db(
            'SELECT StatusID FROM TicketStatus WHERE StatusName = "Gelöst"',
            (),
            one=True,
        )
        if solved_status:
            new_status = str(solved_status["StatusID"])

    # Status/Priorität aktualisieren
    updates_made = []

    if new_status and new_status != str(ticket["StatusID"]):
        update_db("Tickets", "TicketID", ticket_id, ["StatusID"], [new_status])
        status_info = get_status_by_id(new_status)
        updates_made.append(f"Status geändert zu: {status_info['StatusName']}")

    if new_priority and new_priority != str(ticket["PriorityID"]):
        update_db("Tickets", "TicketID", ticket_id, ["PriorityID"], [new_priority])
        priority_info = get_priority_by_id(new_priority)
        updates_made.append(f"Priorität geändert zu: {priority_info['PriorityName']}")

    # Agent zuweisen
    if assign_agent:
        assigned_agent = query_db(
            "SELECT AgentName FROM Agents WHERE AgentID = ?", (assign_agent,), one=True
        )
        if assigned_agent:
            # Prüfen ob bereits zugewiesen
            existing = query_db(
                "SELECT * FROM TicketAssignees WHERE TicketID = ? AND AgentID = ?",
                (ticket_id, assign_agent),
                one=True,
            )
            if not existing:
                insert_db(
                    "TicketAssignees",
                    ["TicketID", "AgentID", "AgentName"],
                    [ticket_id, assign_agent, assigned_agent["AgentName"]],
                )
                updates_made.append(f"Zugewiesen an: {assigned_agent['AgentName']}")

    # Update-Kommentar hinzufügen
    if update_text or updates_made:
        full_update_text = update_text
        if updates_made:
            changes_text = " | ".join(updates_made)
            if update_text:
                full_update_text = f"{update_text}\n\n[Änderungen: {changes_text}]"
            else:
                full_update_text = f"[Änderungen: {changes_text}]"

        insert_db(
            "TicketUpdates",
            ["TicketID", "UpdatedByName", "UpdateText", "IsSolution"],
            [ticket_id, g.current_agent["AgentName"], full_update_text, is_solution],
        )

    # Anhang verarbeiten
    if "attachment" in request.files:
        file = request.files["attachment"]
        if file and file.filename:
            if allowed_file(file.filename):
                filename = secure_filename(file.filename)
                timestamp = int(time.time())
                save_filename = f"{timestamp}_{filename}"
                file_path = os.path.join(app.config["UPLOAD_FOLDER"], save_filename)

                file.save(file_path)

                # Bilder optimieren
                if filename.lower().endswith((".png", ".jpg", ".jpeg", ".gif")):
                    optimize_image(file_path)

                # Anhang in DB speichern
                file_size = os.path.getsize(file_path)
                insert_db(
                    "TicketAttachments",
                    ["TicketID", "FileName", "StoragePath", "FileSize"],
                    [ticket_id, filename, save_filename, file_size],
                )
            else:
                # Fehlermeldung bei unzulässiger Dateiendung
                flash("Dateiendung nicht erlaubt", "error")

    flash("Ticket erfolgreich aktualisiert", "success")
    return redirect(url_for("view_ticket", ticket_id=ticket_id))


@app.route("/ticket/<int:ticket_id>")
def view_ticket(ticket_id):
    """Ticket-Detailansicht"""
    # Ticket laden
    ticket = get_ticket_by_id(ticket_id)
    if not ticket:
        flash("Ticket nicht gefunden", "error")
        return redirect(url_for("dashboard"))

    # Updates laden
    updates = query_db(
        """
        SELECT UpdateID, TicketID, UpdatedByName, UpdateText, IsSolution,
               strftime('%d.%m.%Y %H:%M', UpdatedAt) as UpdatedAt
        FROM TicketUpdates
        WHERE TicketID = ?
        ORDER BY UpdatedAt ASC
    """,
        (ticket_id,),
    )

    # Anhänge laden
    attachments = query_db(
        """
        SELECT AttachmentID, FileName, StoragePath, FileSize,
               strftime('%d.%m.%Y %H:%M', UploadedAt) as UploadedAt
        FROM TicketAttachments
        WHERE TicketID = ?
        ORDER BY UploadedAt ASC
    """,
        (ticket_id,),
    )

    # Zugewiesene Agenten
    assignees = query_db(
        """
        SELECT AgentID, AgentName,
               strftime('%d.%m.%Y %H:%M', AssignedAt) as AssignedAt
        FROM TicketAssignees
        WHERE TicketID = ?
        ORDER BY AssignedAt DESC
    """,
        (ticket_id,),
    )

    # Verwandte Tickets (gleiche Einrichtung/Standort)
    related_facility = []
    related_location = []

    # if ticket.get('FacilityID'):
    #     related_facility = query_db("""
    #         SELECT t.TicketID, t.Title, s.StatusName, s.ColorCode,
    #                strftime('%d.%m.%Y', t.CreatedAt) as CreatedAt
    #         FROM Tickets t
    #         JOIN TicketStatus s ON t.StatusID = s.StatusID
    #         WHERE t.FacilityID = ? AND t.TicketID != ?
    #         ORDER BY t.CreatedAt DESC LIMIT 5
    #     """, (ticket['FacilityID'], ticket_id))

    # if ticket.get('FacilityID'):
    #     related_facility = query_db("""
    #         SELECT t.TicketID, t.Title, s.StatusName, s.ColorCode,
    #             team.TeamName, team.TeamColor,
    #             strftime('%d.%m.%Y', t.CreatedAt) as CreatedAt
    #         FROM Tickets t
    #         JOIN TicketStatus s ON t.StatusID = s.StatusID
    #         JOIN Teams team ON t.TeamID = team.TeamID
    #         WHERE t.FacilityID = ? AND t.TicketID != ?
    #         ORDER BY t.CreatedAt DESC LIMIT 5
    #     """, (ticket['FacilityID'], ticket_id))

    # Facility-Tickets
    if ticket.get("FacilityID"):
        related_facility = query_db(
            """
            SELECT t.TicketID, t.Title, t.ContactName, s.StatusName, s.ColorCode,
                team.TeamName, team.TeamColor,
                strftime('%d.%m.%Y', t.CreatedAt) as CreatedAt
            FROM Tickets t
            JOIN TicketStatus s ON t.StatusID = s.StatusID
            JOIN Teams team ON t.TeamID = team.TeamID
            WHERE t.FacilityID = ? AND t.TicketID != ?
            ORDER BY t.CreatedAt DESC LIMIT 5
        """,
            (ticket["FacilityID"], ticket_id),
        )

    # if ticket.get('LocationID'):
    #     related_location = query_db("""
    #         SELECT t.TicketID, t.Title, s.StatusName, s.ColorCode,
    #                team.TeamName, team.TeamColor,
    #                strftime('%d.%m.%Y', t.CreatedAt) as CreatedAt
    #         FROM Tickets t
    #         JOIN TicketStatus s ON t.StatusID = s.StatusID
    #         JOIN Teams team ON t.TeamID = team.TeamID
    #         WHERE t.LocationID = ? AND t.TicketID != ?
    #         ORDER BY t.CreatedAt DESC LIMIT 5
    #     """, (ticket['LocationID'], ticket_id))

    # Zeigt "an diesem Standort" nur Tickets, die NICHT zur gleichen Einrichtung gehören.
    # if ticket.get('LocationID'):
    #     related_location = query_db("""
    #         SELECT t.TicketID, t.Title, s.StatusName, s.ColorCode,
    #             team.TeamName, team.TeamColor,
    #             strftime('%d.%m.%Y', t.CreatedAt) as CreatedAt
    #         FROM Tickets t
    #         JOIN TicketStatus s ON t.StatusID = s.StatusID
    #         JOIN Teams team ON t.TeamID = team.TeamID
    #         WHERE t.LocationID = ? AND t.TicketID != ?
    #         AND (t.FacilityID != ? OR t.FacilityID IS NULL)
    #         ORDER BY t.CreatedAt DESC LIMIT 5
    #     """, (ticket['LocationID'], ticket_id, ticket['FacilityID'] or 0))

    # Location-Tickets
    if ticket.get("LocationID"):
        related_location = query_db(
            """
            SELECT t.TicketID, t.Title, t.ContactName, s.StatusName, s.ColorCode,
                team.TeamName, team.TeamColor,
                strftime('%d.%m.%Y', t.CreatedAt) as CreatedAt
            FROM Tickets t
            JOIN TicketStatus s ON t.StatusID = s.StatusID
            JOIN Teams team ON t.TeamID = team.TeamID
            WHERE t.LocationID = ? AND t.TicketID != ?
            AND (t.FacilityID != ? OR t.FacilityID IS NULL)
            ORDER BY t.CreatedAt DESC LIMIT 5
        """,
            (ticket["LocationID"], ticket_id, ticket["FacilityID"] or 0),
        )

    # Facility/Location-Informationen laden
    facility_info = None
    location_info = None

    if ticket.get("FacilityID"):
        facility_info = get_facility_info(ticket["FacilityID"])

    if ticket.get("LocationID"):
        location_info = get_location_info(ticket["LocationID"])

    # Dropdown-Daten für Updates
    statuses = get_all_statuses()
    priorities = get_all_priorities()
    agents = load_agents()

    return render_template(
        "ticket_view.html",
        ticket=ticket,
        updates=updates,
        attachments=attachments,
        assignees=assignees,
        related_facility=related_facility,
        related_location=related_location,
        facility_info=facility_info,
        location_info=location_info,
        statuses=statuses,
        priorities=priorities,
        agents=agents,
    )


@app.route("/attachment/<filename>")
def serve_attachment(filename):
    """Anhang ausliefern"""
    return send_from_directory(app.config["UPLOAD_FOLDER"], filename)


# ==============================================
# API ROUTES
# ==============================================


@app.route("/api/search_employees")
def api_search_employees():
    """API: Mitarbeiter suchen"""
    search_term = request.args.get("term", "")
    if len(search_term) < 2:
        return jsonify([])

    employees = search_employees(search_term)
    return jsonify(employees)


@app.route("/api/employee/<int:employee_id>")
def api_employee_details(employee_id):
    """API: Mitarbeiter-Details abrufen"""
    employee = get_employee_details(employee_id)
    if not employee:
        return jsonify({"error": "Mitarbeiter nicht gefunden"}), 404

    return jsonify(employee)


@app.route("/api/search_tickets")
def api_search_tickets():
    """API: Tickets nach Titel oder ID suchen"""
    term = request.args.get("term", "")
    if len(term) < 2:
        return jsonify([])

    results = search_tickets(term)
    return jsonify(results)


# ==============================================
# DEVELOPMENT HELPERS
# ==============================================


@app.cli.command("init-db")
def init_db_command():
    """Datenbankschema initialisieren"""
    try:
        # Schema-Datei laden und ausführen
        db = get_ticket_db()
        with open("schema.sql", "r") as f:
            db.executescript(f.read())
        db.commit()
        print("✅ Datenbank erfolgreich initialisiert.")
    except FileNotFoundError:
        print("❌ schema.sql nicht gefunden.")
    except Exception as e:
        print(f"❌ Fehler bei der DB-Initialisierung: {e}")


if __name__ == "__main__":
    app.run(
        host="0.0.0.0",
        port=5001,
        debug=True,
        ssl_context=("/opt/ifakticket/cert.pem", "/opt/ifakticket/key.pem"),
    )
