import os

# Basis-Verzeichnis
BASE_DIR = os.path.dirname(os.path.abspath(__file__))

# Datenbank-Konfiguration
DATABASE = {
    "ticket_db": os.path.join(BASE_DIR, "db", "tickets.db"),
    "address_db": os.path.join(BASE_DIR, "db", "ifak.db"),
}

# Upload-Konfiguration
UPLOAD_FOLDER = os.path.join(BASE_DIR, "static", "uploads")
ALLOWED_EXTENSIONS = {
    "png",
    "jpg",
    "jpeg",
    "gif",
    "pdf",
    "doc",
    "docx",
    "txt",
    "zip",
}
MAX_CONTENT_LENGTH = 10 * 1024 * 1024  # 10MB

# Flask-Konfiguration
SECRET_KEY = "your-secret-key-here"  # TODO: In Produktion ändern
DEBUG = True  # TODO: In Produktion auf False setzen

# Session-Konfiguration
SESSION_COOKIE_SECURE = False  # TODO: In Produktion auf True setzen (HTTPS)
SESSION_COOKIE_HTTPONLY = True
PERMANENT_SESSION_LIFETIME = 30 * 24 * 60 * 60  # 30 Tage

# Offene Tickets, die älter als dieser Schwellenwert sind, werden im Dashboard
# farblich hervorgehoben.
OLD_TICKET_THRESHOLD_DAYS = 30

# Schwellenwerte in Stunden für unzugewiesene Tickets nach Priorität
# Wird genutzt, um Tickets im Dashboard optisch hervorzuheben,
# wenn sie länger als die definierte Zeit offen und unzugewiesen sind.
UNASSIGNED_WARNING_HOURS = {
    3: 1,  # Hoch
    2: 4,  # Mittel
    1: 8,  # Niedrig
}
