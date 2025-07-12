<?php
$BASE_DIR = dirname(__DIR__);

// Database configuration
$DATABASE = [
    'ticket_db' => $BASE_DIR . '/db/tickets.db',
    'address_db' => $BASE_DIR . '/db/ifak.db',
];

$UPLOAD_FOLDER = $BASE_DIR . '/static/uploads';
$ALLOWED_EXTENSIONS = ['png','jpg','jpeg','gif','pdf','doc','docx','txt','zip'];
$MAX_CONTENT_LENGTH = 10 * 1024 * 1024; // 10MB

$SECRET_KEY = 'your-secret-key-here'; // TODO: change in production
$DEBUG = true;

$OLD_TICKET_THRESHOLD_DAYS = 30;
?>
