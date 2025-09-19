<?php
$BASE_DIR = __DIR__;

// Database configuration
$DATABASE = [
    'ticket_db' => $BASE_DIR . '/db/tickets.db',
    'address_db' => $BASE_DIR . '/db/ifak.db',
];

$UPLOAD_FOLDER = $BASE_DIR . '/static/uploads';
$ALLOWED_EXTENSIONS = ['png','jpg','jpeg','gif','pdf','doc','docx','txt','zip','md'];
$MAX_CONTENT_LENGTH = 10 * 1024 * 1024; // 10MB

$SECRET_KEY = 'your-secret-key-here'; // TODO: change in production
$DEBUG = true;

$STALE_TICKET_THRESHOLD_DAYS = 14;

// Thresholds (in hours) for highlighting unassigned tickets by priority
$UNASSIGNED_WARNING_HOURS = [
    3 => 1, // Hoch
    2 => 4, // Mittel
    1 => 8  // Niedrig
];

// ID des Auszubildenden für spezielle Verfügbarkeitsoptionen
// Der Auszubildende nutzt zusätzliche Verfügbarkeitsoptionen (z. B. Schule)
$TRAINEE_AGENT_ID = 2;

// IDs der Teilzeit-Beschäftigten für den Status "Frei" (Albenni, Errachidi)
$PART_TIME_AGENT_IDS = [4, 5];

// Absenderadresse für Benachrichtigungen
$HELPDESK_FROM = 'helpdesk@ifak-bochum.de';

// Funktions-Postfach für neue Tickets
$HELPDESK_FUNCTIONAL = 'helpdesk@ifak-sozial.de';

// Empfänger für gelöste Tickets (Qualitätskontrolle)
$QUALITY_CONTROL_EMAIL = 'helpdesk@ifak-bochum.de';

// Mailer-Konfiguration (Standard: sendmail, optional: SMTP)
$MAIL_CONFIG = [
    'transport' => 'sendmail',
    'sendmail_path' => null, // null nutzt ini_get('sendmail_path') zur Laufzeit
    'from_name' => 'IFAK Helpdesk',
    'sender' => null, // optional: Envelope-Sender/Return-Path
    'reply_to' => null, // z. B. ['email' => 'reply@example.com', 'name' => 'Support']
    'smtp' => [
        'host' => '',
        'port' => 587,
        'username' => '',
        'password' => '',
        'encryption' => 'starttls', // starttls | ssl | none
        'auth' => true,
        'options' => [] // optionale PHPMailer SMTPOptions
    ]
];

// Reaktionszeiten pro Priorität (in Stunden)
$REACTION_TIME_HOURS = [
    3 => 2, // Hoch
    2 => 4, // Mittel
    1 => 8  // Niedrig
];

// Web Push VAPID configuration
$PUSH_VAPID_PUBLIC_KEY = 'YOUR_PUBLIC_KEY';
$PUSH_VAPID_PRIVATE_KEY = 'YOUR_PRIVATE_KEY';
$PUSH_VAPID_SUBJECT = 'mailto:admin@example.com';
?>
