<?php
require_once __DIR__ . '/../helpers.php';

error_reporting(E_ALL);

set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

$logFile = __DIR__ . '/mail_output.log';
if (file_exists($logFile)) {
    unlink($logFile);
}

$desiredScript = realpath(__DIR__ . '/fake_mail.php');
$currentPath = ini_get('sendmail_path');
$candidate = PHP_BINARY . ' ' . escapeshellarg($desiredScript);
$applied = false;
if (strpos($currentPath, $desiredScript) === false) {
    ini_set('sendmail_path', $candidate);
    $currentPath = ini_get('sendmail_path');
}
if (strpos($currentPath, $desiredScript) !== false) {
    $applied = true;
}

global $MAIL_CONFIG;
if (!$applied) {
    if (!is_array($MAIL_CONFIG)) {
        $MAIL_CONFIG = [];
    }
    $MAIL_CONFIG['transport'] = 'sendmail';
    $MAIL_CONFIG['sendmail_path'] = $candidate;
    $applied = true;
}

if (!$applied) {
    restore_error_handler();
    fwrite(STDERR, 'Fehler: sendmail_path nicht auf fake_mail.php gesetzt.' . PHP_EOL);
    exit(1);
}

global $QUALITY_CONTROL_EMAIL;

$ticket = [
    'TicketID' => 999,
    'Title' => 'Testticket',
    'Description' => 'Beschreibung',
    'ContactName' => 'Kontakt Person',
    'ContactEmail' => null
];

$updates = [
    [
        'IsSolution' => 1,
        'UpdatedByName' => 'Agent Tester',
        'FormattedUpdatedAt' => '01.01.2024 12:00',
        'UpdateText' => 'Lösung wurde dokumentiert.'
    ]
];

try {
    send_solution_email($ticket, $updates);
    restore_error_handler();
} catch (Throwable $e) {
    restore_error_handler();
    fwrite(STDERR, 'Fehler: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

if (!file_exists($logFile)) {
    fwrite(STDERR, "Fehler: Keine Mail protokolliert." . PHP_EOL);
    exit(1);
}

$content = file_get_contents($logFile);
$occurrences = substr_count($content, '=== MAIL ===');
if ($occurrences !== 1) {
    fwrite(STDERR, 'Fehler: Erwartet 1 Mail, gefunden ' . $occurrences . PHP_EOL);
    exit(1);
}

if (strpos($content, 'To: ' . $QUALITY_CONTROL_EMAIL) === false) {
    fwrite(STDERR, 'Fehler: Qualitätskontrolladresse nicht gefunden.' . PHP_EOL);
    exit(1);
}

echo "OK\n";
