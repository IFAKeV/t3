<?php
require_once __DIR__ . '/../helpers.php';

error_reporting(E_ALL);

set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

function extract_mail_entries($logContent) {
    if (!preg_match_all('/=== MAIL ===\nARGS: [^\n]*\nDATA:\n(.+?)=== END ===\n/s', $logContent, $matches)) {
        return [];
    }
    return $matches[1];
}

function decode_mail_body_from_raw($raw) {
    if (!preg_match('/Content-Type: text\/plain; charset=UTF-8\s+Content-Transfer-Encoding: base64\s+([A-Za-z0-9\/+=\s]+)/', $raw, $matches)) {
        return false;
    }

    $base64Body = preg_replace('/\s+/', '', $matches[1]);
    $decodedBody = base64_decode($base64Body, true);
    if ($decodedBody === false) {
        return false;
    }

    return $decodedBody;
}

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
$entries = extract_mail_entries($content);
if (count($entries) !== 1) {
    fwrite(STDERR, 'Fehler: Erwartet 1 Mail, gefunden ' . count($entries) . PHP_EOL);
    exit(1);
}

$qualityMail = $entries[0];
if (strpos($qualityMail, 'To: ' . $QUALITY_CONTROL_EMAIL) === false) {
    fwrite(STDERR, 'Fehler: Qualitätskontrolladresse nicht gefunden.' . PHP_EOL);
    exit(1);
}

$decodedBody = decode_mail_body_from_raw($qualityMail);
if ($decodedBody === false) {
    fwrite(STDERR, 'Fehler: Mailbody konnte nicht dekodiert werden.' . PHP_EOL);
    exit(1);
}

if (strpos($decodedBody, 'Das Ticket #999 wurde als gelöst markiert.') === false) {
    fwrite(STDERR, 'Fehler: Einleitung für Qualitätskontrolle fehlt.' . PHP_EOL);
    exit(1);
}

if (strpos($decodedBody, 'Kommentarhistorie:') === false) {
    fwrite(STDERR, 'Fehler: Kommentarhistorie fehlt in Qualitätskontrollmail.' . PHP_EOL);
    exit(1);
}

if (strpos($decodedBody, 'Lösung wurde dokumentiert.') === false) {
    fwrite(STDERR, 'Fehler: Lösungseintrag fehlt in Qualitätskontrollmail.' . PHP_EOL);
    exit(1);
}

if (strpos($decodedBody, 'Dein Ticket') !== false) {
    fwrite(STDERR, 'Fehler: Qualitätskontrollmail enthält Submitter-Anrede.' . PHP_EOL);
    exit(1);
}

if (strpos($decodedBody, "\r\n") === false) {
    fwrite(STDERR, 'Fehler: Mailbody enthält keine CRLF-Zeilenumbrüche.' . PHP_EOL);
    exit(1);
}

$withoutCrlf = str_replace("\r\n", '', $decodedBody);
if (strpos($withoutCrlf, "\n") !== false) {
    fwrite(STDERR, 'Fehler: Mailbody enthält Zeilenumbrüche ohne CRLF.' . PHP_EOL);
    exit(1);
}

unlink($logFile);

$ticket = [
    'TicketID' => 1000,
    'Title' => 'Testticket mit Submitter',
    'Description' => 'Neue Beschreibung',
    'ContactName' => 'Kontakt Person',
    'ContactEmail' => 'kunde@example.com'
];

$updates = [
    [
        'IsSolution' => 0,
        'UpdatedByName' => 'Qualitätsprüfer',
        'FormattedUpdatedAt' => '02.01.2024 09:00',
        'UpdateText' => 'Analyse durchgeführt.'
    ],
    [
        'IsSolution' => 1,
        'UpdatedByName' => 'Agent Tester',
        'FormattedUpdatedAt' => '02.01.2024 10:00',
        'UpdateText' => 'Problem gelöst.'
    ]
];

send_solution_email($ticket, $updates);

if (!file_exists($logFile)) {
    fwrite(STDERR, "Fehler: Keine Mail für Submitter-Szenario protokolliert." . PHP_EOL);
    exit(1);
}

$content = file_get_contents($logFile);
$entries = extract_mail_entries($content);
if (count($entries) !== 2) {
    fwrite(STDERR, 'Fehler: Erwartet 2 Mails, gefunden ' . count($entries) . PHP_EOL);
    exit(1);
}

$qualityMail = null;
$submitterMail = null;
foreach ($entries as $entry) {
    if (strpos($entry, 'To: ' . $QUALITY_CONTROL_EMAIL) !== false) {
        $qualityMail = $entry;
    }
    if (strpos($entry, 'To: kunde@example.com') !== false) {
        $submitterMail = $entry;
    }
}

if ($qualityMail === null) {
    fwrite(STDERR, 'Fehler: Qualitätskontrollmail im Submitter-Szenario fehlt.' . PHP_EOL);
    exit(1);
}

if ($submitterMail === null) {
    fwrite(STDERR, 'Fehler: Submitter-Mail nicht gefunden.' . PHP_EOL);
    exit(1);
}

$qualityBody = decode_mail_body_from_raw($qualityMail);
if ($qualityBody === false) {
    fwrite(STDERR, 'Fehler: Qualitätskontrollmail konnte nicht dekodiert werden.' . PHP_EOL);
    exit(1);
}

$submitterBody = decode_mail_body_from_raw($submitterMail);
if ($submitterBody === false) {
    fwrite(STDERR, 'Fehler: Submitter-Mail konnte nicht dekodiert werden.' . PHP_EOL);
    exit(1);
}

if (strpos($qualityBody, 'Das Ticket #1000 wurde als gelöst markiert.') === false) {
    fwrite(STDERR, 'Fehler: Einleitung in Qualitätskontrollmail (Submitter-Szenario) fehlt.' . PHP_EOL);
    exit(1);
}

if (strpos($qualityBody, 'Kommentarhistorie:') === false) {
    fwrite(STDERR, 'Fehler: Kommentarhistorie fehlt in Qualitätskontrollmail (Submitter-Szenario).' . PHP_EOL);
    exit(1);
}

if (strpos($qualityBody, 'Analyse durchgeführt.') === false) {
    fwrite(STDERR, 'Fehler: Kommentar wurde in Qualitätskontrollmail nicht gefunden.' . PHP_EOL);
    exit(1);
}

if (strpos($qualityBody, 'Problem gelöst.') === false) {
    fwrite(STDERR, 'Fehler: Lösung wurde in Qualitätskontrollmail nicht gefunden.' . PHP_EOL);
    exit(1);
}

if (strpos($qualityBody, 'Dein Ticket') !== false) {
    fwrite(STDERR, 'Fehler: Qualitätskontrollmail enthält falsche Anrede.' . PHP_EOL);
    exit(1);
}

if (strpos($submitterBody, 'Dein Ticket #1000 wurde als gelöst markiert.') === false) {
    fwrite(STDERR, 'Fehler: Einleitung in Submitter-Mail fehlt.' . PHP_EOL);
    exit(1);
}

if (strpos($submitterBody, 'Lösung:') === false) {
    fwrite(STDERR, 'Fehler: Submitter-Mail enthält keine Lösungsüberschrift.' . PHP_EOL);
    exit(1);
}

if (strpos($submitterBody, 'Problem gelöst.') === false) {
    fwrite(STDERR, 'Fehler: Lösungstext fehlt in Submitter-Mail.' . PHP_EOL);
    exit(1);
}

if (strpos($submitterBody, 'Analyse durchgeführt.') !== false) {
    fwrite(STDERR, 'Fehler: Submitter-Mail enthält Kommentare ohne Lösung.' . PHP_EOL);
    exit(1);
}

if (strpos($submitterBody, 'Kommentarhistorie:') !== false) {
    fwrite(STDERR, 'Fehler: Submitter-Mail enthält Kommentarhistorie.' . PHP_EOL);
    exit(1);
}

if (strpos($submitterBody, 'Aufgabenstellung:') !== false) {
    fwrite(STDERR, 'Fehler: Submitter-Mail enthält Aufgabenstellung.' . PHP_EOL);
    exit(1);
}

foreach (['Qualitätskontrolle' => $qualityBody, 'Submitter' => $submitterBody] as $label => $body) {
    if (strpos($body, "\r\n") === false) {
        fwrite(STDERR, 'Fehler: ' . $label . ' Mailbody enthält keine CRLF-Zeilenumbrüche.' . PHP_EOL);
        exit(1);
    }

    $normalized = str_replace("\r\n", '', $body);
    if (strpos($normalized, "\n") !== false) {
        fwrite(STDERR, 'Fehler: ' . $label . ' Mailbody enthält Zeilenumbrüche ohne CRLF.' . PHP_EOL);
        exit(1);
    }
}

echo "OK\n";
