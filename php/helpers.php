<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/lib/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/lib/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;

function allowed_file($filename) {
    global $ALLOWED_EXTENSIONS;
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, $ALLOWED_EXTENSIONS);
}

function get_allowed_extension_strings() {
    global $ALLOWED_EXTENSIONS;

    if (!is_array($ALLOWED_EXTENSIONS)) {
        return ['accept' => '', 'hint' => ''];
    }

    $extensions = [];
    foreach ($ALLOWED_EXTENSIONS as $ext) {
        if (!is_string($ext)) {
            continue;
        }
        $clean = strtolower(trim($ext));
        if ($clean === '') {
            continue;
        }
        if (!in_array($clean, $extensions, true)) {
            $extensions[] = $clean;
        }
    }

    if (empty($extensions)) {
        return ['accept' => '', 'hint' => ''];
    }

    $accept = '.' . implode(',.', $extensions);
    $hint = implode(', ', $extensions);

    return ['accept' => $accept, 'hint' => $hint];
}

function get_local_timestamp() {
    $dt = new DateTime('now', new DateTimeZone('Europe/Berlin'));
    return $dt->format('Y-m-d H:i:s');
}

function optimize_image($file_path) {
    $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg','jpeg'])) {
        $img = imagecreatefromjpeg($file_path);
        if ($img) {
            $width = imagesx($img);
            $height = imagesy($img);
            $scale = min(800/$width, 800/$height, 1);
            $new_w = (int)($width*$scale);
            $new_h = (int)($height*$scale);
            $thumb = imagecreatetruecolor($new_w,$new_h);
            imagecopyresampled($thumb,$img,0,0,0,0,$new_w,$new_h,$width,$height);
            imagejpeg($thumb,$file_path,85);
            imagedestroy($img); imagedestroy($thumb);
        }
    }
}

function get_addressbook_date() {
    global $DATABASE;
    $path = $DATABASE['address_db'];
    try {
        if (file_exists($path)) {
            $mod_time = filemtime($path);
            return date('d.m.Y H:i', $mod_time);
        }
        return 'Datei nicht gefunden';
    } catch (Exception $e) {
        error_log('Fehler beim Abrufen des Adressbuch-Datums: ' . $e->getMessage());
        return 'Nicht verfügbar';
    }
}

function get_base_url() {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    return $scheme . '://' . $host . $path;
}

function linkify_urls($text) {
    $escaped = htmlspecialchars($text);
    $pattern = '/(https:\/\/[^\s]+)/';
    return preg_replace($pattern, '<a href="$1" target="_blank" rel="noopener">$1</a>', $escaped);
}

function mail_log_enabled() {
    global $MAIL_LOG_FILE;
    return isset($MAIL_LOG_FILE) && is_string($MAIL_LOG_FILE) && trim($MAIL_LOG_FILE) !== '';
}

function write_mail_log($level, $message, array $context = []) {
    if (!mail_log_enabled()) {
        return;
    }

    global $MAIL_LOG_FILE;

    $entry = [
        'timestamp' => date('c'),
        'level' => strtoupper((string)$level),
        'message' => $message,
    ];

    if (!empty($context)) {
        $entry['context'] = $context;
    }

    $encoded = json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($encoded === false) {
        $encoded = date('c') . ' ' . strtoupper((string)$level) . ' ' . $message;
    }

    $logDir = dirname($MAIL_LOG_FILE);
    if (!is_dir($logDir)) {
        if (!mkdir($logDir, 0775, true) && !is_dir($logDir)) {
            error_log('Mail-Log-Verzeichnis konnte nicht erstellt werden: ' . $logDir);
            return;
        }
    }

    $result = file_put_contents($MAIL_LOG_FILE, $encoded . PHP_EOL, FILE_APPEND | LOCK_EX);
    if ($result === false) {
        error_log('Mail-Log konnte nicht geschrieben werden: ' . $MAIL_LOG_FILE);
    }
}

function describe_mail_recipient($recipient) {
    if (is_array($recipient)) {
        $email = $recipient['email'] ?? ($recipient[0] ?? null);
        $name = $recipient['name'] ?? ($recipient[1] ?? null);
        $email = is_string($email) ? trim($email) : '';
        $name = is_string($name) ? trim($name) : '';

        if ($email !== '' && $name !== '') {
            return $name . ' <' . $email . '>';
        }

        if ($email !== '') {
            return $email;
        }

        if ($name !== '') {
            return $name;
        }
    }

    if (is_string($recipient)) {
        $trimmed = trim($recipient);
        if ($trimmed !== '') {
            return $trimmed;
        }
    } elseif ($recipient === null) {
        return 'NULL';
    } elseif (is_scalar($recipient)) {
        return (string)$recipient;
    }

    $encoded = json_encode($recipient, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($encoded !== false) {
        return $encoded;
    }

    return gettype($recipient);
}

function get_mail_transport_context(PHPMailer $mailer) {
    $context = [
        'mailer' => $mailer->Mailer,
    ];

    if (!empty($mailer->From)) {
        $context['from'] = describe_mail_recipient([
            'email' => $mailer->From,
            'name' => $mailer->FromName,
        ]);
    }

    if ($mailer->Mailer === 'smtp') {
        $context['host'] = $mailer->Host;
        $context['port'] = $mailer->Port;
        $context['encryption'] = $mailer->SMTPSecure ?: 'none';
        $context['smtp_auth'] = (bool)$mailer->SMTPAuth;
        $context['username_configured'] = $mailer->Username !== '';
    } elseif (in_array($mailer->Mailer, ['sendmail', 'qmail'], true)) {
        $context['path'] = $mailer->Sendmail;
    }

    return $context;
}

function create_mailer_instance() {
    global $HELPDESK_FROM, $MAIL_CONFIG;

    $settings = $MAIL_CONFIG ?? [];

    $mailer = new PHPMailer(true);
    $mailer->CharSet = 'UTF-8';
    $mailer->Encoding = 'base64';
    $mailer->isHTML(false);

    $fromName = $settings['from_name'] ?? '';
    if ($fromName) {
        $mailer->setFrom($HELPDESK_FROM, $fromName);
    } else {
        $mailer->setFrom($HELPDESK_FROM);
    }

    $senderAddress = $settings['sender'] ?? $HELPDESK_FROM;
    if ($senderAddress) {
        $mailer->Sender = $senderAddress;
    }

    $replyTo = $settings['reply_to'] ?? null;
    if ($replyTo) {
        if (is_array($replyTo)) {
            $email = $replyTo['email'] ?? ($replyTo[0] ?? null);
            $name = $replyTo['name'] ?? ($replyTo[1] ?? '');
            if ($email) {
                $mailer->addReplyTo($email, $name);
            }
        } elseif (is_string($replyTo)) {
            $mailer->addReplyTo($replyTo);
        }
    }

    $transport = $settings['transport'] ?? 'sendmail';
    if ($transport === 'smtp') {
        $smtp = $settings['smtp'] ?? [];
        $host = trim($smtp['host'] ?? '');
        if ($host === '') {
            $transport = 'sendmail';
        } else {
            $mailer->isSMTP();
            $mailer->Host = $host;
            $mailer->Port = (int)($smtp['port'] ?? 587);
            $encryption = strtolower((string)($smtp['encryption'] ?? ''));
            if ($encryption === 'ssl') {
                $mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($encryption === 'tls' || $encryption === 'starttls') {
                $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }
            $username = $smtp['username'] ?? '';
            $password = $smtp['password'] ?? '';
            $auth = $smtp['auth'] ?? null;
            if (!empty($username) || !empty($password)) {
                $mailer->SMTPAuth = true;
                $mailer->Username = $username;
                $mailer->Password = $password;
            } elseif ($auth !== null) {
                $mailer->SMTPAuth = (bool)$auth;
                if ($mailer->SMTPAuth) {
                    $mailer->Username = $username;
                    $mailer->Password = $password;
                }
            } else {
                $mailer->SMTPAuth = false;
            }
            if (!empty($smtp['options']) && is_array($smtp['options'])) {
                $mailer->SMTPOptions = $smtp['options'];
            }
        }
    }

    if ($transport !== 'smtp') {
        $mailer->isSendmail();
        $path = $settings['sendmail_path'] ?? null;
        if ($path) {
            $mailer->Sendmail = $path;
        } else {
            $configuredPath = ini_get('sendmail_path');
            if ($configuredPath) {
                $mailer->Sendmail = $configuredPath;
            }
        }
    }

    return $mailer;
}

function normalize_recipient($recipient) {
    if (is_string($recipient)) {
        return ['email' => $recipient, 'name' => ''];
    }
    if (is_array($recipient)) {
        if (isset($recipient['email'])) {
            return ['email' => $recipient['email'], 'name' => $recipient['name'] ?? ''];
        }
        if (isset($recipient[0])) {
            return ['email' => $recipient[0], 'name' => $recipient[1] ?? ''];
        }
    }
    return null;
}

function build_plaintext_mail_body(array $lines) {
    $bodyLines = [];

    foreach ($lines as $line) {
        if ($line === null || $line === false) {
            continue;
        }

        $bodyLines[] = $line;
    }

    return implode("\r\n", $bodyLines);
}

function send_mail_message($recipients, $subject, $body, $options = []) {
    if (empty($recipients)) {
        write_mail_log('warning', 'Mailversand übersprungen – keine Empfänger angegeben', [
            'subject' => $subject,
        ]);
        return false;
    }

    if (!is_array($recipients) || array_keys($recipients) !== range(0, count($recipients) - 1)) {
        $recipients = [$recipients];
    }

    $validRecipients = [];
    $skippedRecipients = [];
    $replyToNormalized = null;
    $bodyLength = is_string($body) ? strlen($body) : null;
    $mailer = null;

    try {
        $mailer = create_mailer_instance();
        foreach ($recipients as $entry) {
            $normalized = normalize_recipient($entry);
            if (!$normalized || empty($normalized['email'])) {
                $skippedRecipients[] = $entry;
                continue;
            }
            $mailer->addAddress($normalized['email'], $normalized['name']);
            $validRecipients[] = $normalized;
        }

        if (count($validRecipients) === 0) {
            write_mail_log('warning', 'Mailversand übersprungen – keine gültigen Empfänger', [
                'subject' => $subject,
                'skipped_recipients' => array_map('describe_mail_recipient', $skippedRecipients),
            ]);
            return false;
        }

        if (!empty($options['reply_to'])) {
            $replyToNormalized = normalize_recipient($options['reply_to']);
            if ($replyToNormalized && !empty($replyToNormalized['email'])) {
                $mailer->clearReplyTos();
                $mailer->addReplyTo($replyToNormalized['email'], $replyToNormalized['name']);
            }
        }

        $mailer->Subject = $subject;
        $normalizedBody = PHPMailer::normalizeBreaks((string)$body, PHPMailer::CRLF);
        $mailer->Body = $normalizedBody;
        $mailer->AltBody = '';

        $logContext = [
            'subject' => $subject,
            'recipients' => array_map('describe_mail_recipient', $validRecipients),
            'transport' => get_mail_transport_context($mailer),
        ];

        if ($bodyLength !== null) {
            $logContext['body_length'] = $bodyLength;
        }

        if (!empty($skippedRecipients)) {
            $logContext['skipped_recipients'] = array_map('describe_mail_recipient', $skippedRecipients);
        }

        if ($replyToNormalized) {
            $logContext['reply_to'] = describe_mail_recipient($replyToNormalized);
        } elseif (!empty($options['reply_to'])) {
            $logContext['reply_to'] = describe_mail_recipient($options['reply_to']);
        }

        $result = $mailer->send();

        if ($result) {
            write_mail_log('info', 'Mail erfolgreich versendet', $logContext);
        } else {
            $logContext['error'] = $mailer->ErrorInfo ?: 'Unbekannter Fehler';
            write_mail_log('error', 'Mailversand fehlgeschlagen', $logContext);
        }

        return $result;
    } catch (PHPMailerException $e) {
        $errorContext = [
            'subject' => $subject,
            'recipients' => array_map('describe_mail_recipient', $validRecipients ?: $recipients),
            'error' => $e->getMessage(),
        ];

        if ($bodyLength !== null) {
            $errorContext['body_length'] = $bodyLength;
        }

        if (!empty($skippedRecipients)) {
            $errorContext['skipped_recipients'] = array_map('describe_mail_recipient', $skippedRecipients);
        }

        if ($mailer instanceof PHPMailer) {
            $errorContext['transport'] = get_mail_transport_context($mailer);
        }

        if ($replyToNormalized) {
            $errorContext['reply_to'] = describe_mail_recipient($replyToNormalized);
        } elseif (!empty($options['reply_to'])) {
            $errorContext['reply_to'] = describe_mail_recipient($options['reply_to']);
        }

        write_mail_log('error', 'Mailversand fehlgeschlagen (PHPMailerException)', $errorContext);
        error_log('Mailversand fehlgeschlagen: ' . $e->getMessage());
    } catch (Throwable $e) {
        $errorContext = [
            'subject' => $subject,
            'recipients' => array_map('describe_mail_recipient', $validRecipients ?: $recipients),
            'error' => $e->getMessage(),
        ];

        if ($bodyLength !== null) {
            $errorContext['body_length'] = $bodyLength;
        }

        if (!empty($skippedRecipients)) {
            $errorContext['skipped_recipients'] = array_map('describe_mail_recipient', $skippedRecipients);
        }

        if ($mailer instanceof PHPMailer) {
            $errorContext['transport'] = get_mail_transport_context($mailer);
        }

        if ($replyToNormalized) {
            $errorContext['reply_to'] = describe_mail_recipient($replyToNormalized);
        } elseif (!empty($options['reply_to'])) {
            $errorContext['reply_to'] = describe_mail_recipient($options['reply_to']);
        }

        write_mail_log('error', 'Unerwarteter Fehler beim Mailversand', $errorContext);
        error_log('Unerwarteter Fehler beim Mailversand: ' . $e->getMessage());
    }

    return false;
}

function send_new_ticket_email($ticket) {
    global $HELPDESK_FUNCTIONAL;
    $base_url = get_base_url();
    $link = $base_url . '/index.php?action=view_ticket&id=' . $ticket['TicketID'];
    $subject = 'Neues Ticket #' . $ticket['TicketID'] . ' - ' . $ticket['Title'];
    $priority = $ticket['PriorityName'] ?? '';
    $body = build_plaintext_mail_body([
        'Neues Ticket wurde erstellt:',
        "Titel: {$ticket['Title']}",
        $priority ? "Priorität: $priority" : null,
        'Kontakt: ' . ($ticket['ContactName'] ?? ''),
        !empty($ticket['ContactPhone']) ? "Telefon: {$ticket['ContactPhone']}" : null,
        !empty($ticket['ContactEmail']) ? "E-Mail: {$ticket['ContactEmail']}" : null,
        '',
        "Zum Ticket: $link",
        '',
        'Beschreibung:',
        $ticket['Description'] ?? '',
    ]);
    send_mail_message($HELPDESK_FUNCTIONAL, $subject, $body);
}

function send_ticket_confirmation_email($email, $ticket) {
    if (!$email) return;
    $subject = 'Ticket #' . $ticket['TicketID'] . ' - ' . $ticket['Title'];
    $priority = $ticket['PriorityName'] ?? '';
    $body = build_plaintext_mail_body([
        'Dein Anliegen wird unter der Ticket-Nr: ' . $ticket['TicketID'] . ' bearbeitet.',
        '',
        "Titel: {$ticket['Title']}",
        $priority ? "Priorität: $priority" : null,
        '',
        'Beschreibung:',
        $ticket['Description'] ?? '',
    ]);
    send_mail_message($email, $subject, $body);
}

function send_assignment_email($agent_email, $agent_name, $ticket) {
    if (!$agent_email) return;
    $base_url = get_base_url();
    $link = $base_url . '/index.php?action=view_ticket&id=' . $ticket['TicketID'];
    $subject = 'Ticket #' . $ticket['TicketID'] . ' - ' . $ticket['Title'] . ' - zugewiesen';
    $priority = $ticket['PriorityName'] ?? '';
    global $REACTION_TIME_HOURS;
    $reaction = $ticket['PriorityID'] ? ($REACTION_TIME_HOURS[$ticket['PriorityID']] ?? null) : null;
    $remaining = null;
    if ($reaction && !empty($ticket['CreatedAt'])) {
        try {
            $created = new DateTime($ticket['CreatedAt'], new DateTimeZone('Europe/Berlin'));
            $now = new DateTime('now', new DateTimeZone('Europe/Berlin'));
            $elapsed = ($now->getTimestamp() - $created->getTimestamp()) / 3600;
            $remaining = max(0, $reaction - $elapsed);
            $remaining = round($remaining);
        } catch (Exception $e) {}
    }
    $body = build_plaintext_mail_body([
        "Hallo $agent_name,",
        '',
        'Dir wurde ein neues Ticket zugewiesen:',
        "Titel: {$ticket['Title']}",
        $priority ? "Priorität: $priority" : null,
        $reaction ? "Reaktionszeit: {$reaction}h" : null,
        $remaining !== null ? "Reaktionszeit verbleibend: {$remaining}h" : null,
        'Kontakt: ' . ($ticket['ContactName'] ?? ''),
        !empty($ticket['ContactPhone']) ? "Telefon: {$ticket['ContactPhone']}" : null,
        !empty($ticket['ContactEmail']) ? "E-Mail: {$ticket['ContactEmail']}" : null,
        '',
        "Zum Ticket: $link",
        '',
        'Beschreibung:',
        $ticket['Description'] ?? '',
    ]);
    send_mail_message($agent_email, $subject, $body);
}

function send_solution_email($ticket, $updates) {
    global $QUALITY_CONTROL_EMAIL;
    $base_url = get_base_url();
    $link = $base_url . '/index.php?action=view_ticket&id=' . $ticket['TicketID'];
    $subject = 'Ticket #' . $ticket['TicketID'] . ' - ' . $ticket['Title'] . ' - gelöst';
    $lines = [
        'Dein Ticket #' . $ticket['TicketID'] . ' wurde als gelöst markiert.',
        '',
        'Aufgabenstellung:',
        $ticket['Description'] ?? '',
        '',
        'Kommentarhistorie:',
    ];
    $history = array_reverse($updates);
    foreach ($history as $u) {
        $prefix = $u['IsSolution'] ? '[Lösung] ' : '';
        $lines[] = $prefix . $u['UpdatedByName'] . ' (' . $u['FormattedUpdatedAt'] . '):';
        $lines[] = $u['UpdateText'];
        $lines[] = '';
    }
    if (end($lines) === '') {
        array_pop($lines);
    }
    $lines[] = '';
    $lines[] = "Zum Ticket: $link";
    $body = build_plaintext_mail_body($lines);

    if (!empty($QUALITY_CONTROL_EMAIL)) {
        send_mail_message($QUALITY_CONTROL_EMAIL, $subject, $body);
    }

    $submitter = $ticket['ContactEmail'] ?? null;
    if (!empty($submitter)) {
        send_mail_message($submitter, $subject, $body);
    }
}

function markdown_to_html($text) {
    $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    $patterns = [
        '/^### (.+)$/m' => '<h3>$1</h3>',
        '/^## (.+)$/m' => '<h2>$1</h2>',
        '/^# (.+)$/m' => '<h1>$1</h1>',
        '/\*\*(.+?)\*\*/s' => '<strong>$1</strong>',
        '/\*(.+?)\*/s' => '<em>$1</em>',
        '/`(.+?)`/s' => '<code>$1</code>'
    ];
    foreach ($patterns as $regex => $replacement) {
        $text = preg_replace($regex, $replacement, $text);
    }
    return nl2br($text);
}
?>
