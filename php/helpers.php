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

function send_mail_message($recipients, $subject, $body, $options = []) {
    if (empty($recipients)) {
        return false;
    }

    if (!is_array($recipients) || array_keys($recipients) !== range(0, count($recipients) - 1)) {
        $recipients = [$recipients];
    }

    $mailer = null;
    try {
        $mailer = create_mailer_instance();
        foreach ($recipients as $entry) {
            $normalized = normalize_recipient($entry);
            if (!$normalized || empty($normalized['email'])) {
                continue;
            }
            $mailer->addAddress($normalized['email'], $normalized['name']);
        }

        if (count($mailer->getToAddresses()) === 0) {
            return false;
        }

        if (!empty($options['reply_to'])) {
            $replyTo = normalize_recipient($options['reply_to']);
            if ($replyTo && !empty($replyTo['email'])) {
                $mailer->clearReplyTos();
                $mailer->addReplyTo($replyTo['email'], $replyTo['name']);
            }
        }

        $mailer->Subject = $subject;
        $mailer->Body = $body;
        $mailer->AltBody = $body;

        return $mailer->send();
    } catch (PHPMailerException $e) {
        error_log('Mailversand fehlgeschlagen: ' . $e->getMessage());
    } catch (Throwable $e) {
        error_log('Unerwarteter Fehler beim Mailversand: ' . $e->getMessage());
    }

    return false;
}

function send_new_ticket_email($ticket) {
    global $HELPDESK_FUNCTIONAL;
    $base_url = get_base_url();
    $link = $base_url . '/index.php?action=view_ticket&id=' . $ticket['TicketID'];
    $subject = 'Neues Ticket #' . $ticket['TicketID'];
    $priority = $ticket['PriorityName'] ?? '';
    $body = "Neues Ticket wurde erstellt:\n" .
            "Titel: {$ticket['Title']}\n" .
            ($priority ? "Priorität: $priority\n" : '') .
            "Kontakt: {$ticket['ContactName']}\n" .
            ($ticket['ContactPhone'] ? "Telefon: {$ticket['ContactPhone']}\n" : '') .
            ($ticket['ContactEmail'] ? "E-Mail: {$ticket['ContactEmail']}\n" : '') .
            "\nZum Ticket: $link\n\n" .
            "Beschreibung:\n{$ticket['Description']}\n";
    send_mail_message($HELPDESK_FUNCTIONAL, $subject, $body);
}

function send_ticket_confirmation_email($email, $ticket_id) {
    if (!$email) return;
    $subject = 'Ticket #' . $ticket_id;
    $body = 'Dein Fall wird unter #' . $ticket_id . ' bearbeitet.';
    send_mail_message($email, $subject, $body);
}

function send_assignment_email($agent_email, $agent_name, $ticket) {
    if (!$agent_email) return;
    $base_url = get_base_url();
    $link = $base_url . '/index.php?action=view_ticket&id=' . $ticket['TicketID'];
    $subject = 'Ticket #' . $ticket['TicketID'] . ' zugewiesen';
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
    $body = "Hallo $agent_name,\n\n" .
            "Dir wurde ein neues Ticket zugewiesen:\n" .
            "Titel: {$ticket['Title']}\n" .
            ($priority ? "Priorität: $priority\n" : '') .
            ($reaction ? "Reaktionszeit: {$reaction}h\n" : '') .
            ($remaining !== null ? "Reaktionszeit verbleibend: {$remaining}h\n" : '') .
            "Kontakt: {$ticket['ContactName']}\n" .
            ($ticket['ContactPhone'] ? "Telefon: {$ticket['ContactPhone']}\n" : '') .
            ($ticket['ContactEmail'] ? "E-Mail: {$ticket['ContactEmail']}\n" : '') .
            "\nZum Ticket: $link\n\n" .
            "Beschreibung:\n{$ticket['Description']}\n";
    send_mail_message($agent_email, $subject, $body);
}

function send_solution_email($ticket, $updates) {
    global $QUALITY_CONTROL_EMAIL;
    $base_url = get_base_url();
    $link = $base_url . '/index.php?action=view_ticket&id=' . $ticket['TicketID'];
    $subject = 'Ticket #' . $ticket['TicketID'] . ' gelöst';
    $body = 'Ticket #' . $ticket['TicketID'] . " wurde als gelöst markiert.\n\n" .
            "Aufgabenstellung:\n{$ticket['Description']}\n\n" .
            "Kommentarhistorie:\n";
    $history = array_reverse($updates);
    foreach ($history as $u) {
        $prefix = $u['IsSolution'] ? '[Lösung] ' : '';
        $body .= $prefix . $u['UpdatedByName'] . ' (' . $u['FormattedUpdatedAt'] . "):\n" .
                 $u['UpdateText'] . "\n\n";
    }
    $body .= "Zum Ticket: $link\n";

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
