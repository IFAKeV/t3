import os

# Basis-Verzeichnis
BASE_DIR = os.path.dirname(os.path.abspath(__file__))

# Datenbank-Konfiguration
DATABASE = {
    'ticket_db': os.path.join(BASE_DIR, 'db', 'tickets.db'),
    'address_db': os.path.join(BASE_DIR, 'db', 'ifak.db')
}

# Upload-Konfiguration
UPLOAD_FOLDER = os.path.join(BASE_DIR, 'static', 'uploads')
ALLOWED_EXTENSIONS = {'png', 'jpg', 'jpeg', 'gif', 'pdf', 'doc', 'docx', 'txt'}
MAX_CONTENT_LENGTH = 10 * 1024 * 1024  # 10MB

# Flask-Konfiguration
SECRET_KEY = 'your-secret-key-here'  # TODO: In Produktion ändern
DEBUG = True  # TODO: In Produktion auf False setzen

# Session-Konfiguration
SESSION_COOKIE_SECURE = False  # TODO: In Produktion auf True setzen (HTTPS)
SESSION_COOKIE_HTTPONLY = True
PERMANENT_SESSION_LIFETIME = 30 * 24 * 60 * 60  # 30 Tage
