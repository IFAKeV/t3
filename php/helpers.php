<?php
require_once __DIR__ . '/config.php';

function allowed_file($filename) {
    global $ALLOWED_EXTENSIONS;
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, $ALLOWED_EXTENSIONS);
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

function send_new_ticket_email($ticket) {
    global $HELPDESK_FUNCTIONAL, $HELPDESK_FROM;
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
    @mail($HELPDESK_FUNCTIONAL, $subject, $body, "From: $HELPDESK_FROM");
}

function send_ticket_confirmation_email($email, $ticket_id) {
    if (!$email) return;
    global $HELPDESK_FROM;
    $subject = 'Ticket #' . $ticket_id;
    $body = 'Dein Fall wird unter #' . $ticket_id . ' bearbeitet.';
    @mail($email, $subject, $body, "From: $HELPDESK_FROM");
}

function send_assignment_email($agent_email, $agent_name, $ticket) {
    if (!$agent_email) return;
    $base_url = get_base_url();
    $link = $base_url . '/index.php?action=view_ticket&id=' . $ticket['TicketID'];
    $subject = 'Ticket #' . $ticket['TicketID'] . ' zugewiesen';
    $priority = $ticket['PriorityName'] ?? '';
    global $REACTION_TIME_HOURS, $HELPDESK_FROM;
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
    @mail($agent_email, $subject, $body, "From: $HELPDESK_FROM");
}

function send_solution_email($ticket, $updates) {
    if (empty($ticket['ContactEmail'])) return;
    global $QUALITY_CONTROL_EMAIL, $HELPDESK_FROM;
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
        @mail($QUALITY_CONTROL_EMAIL, $subject, $body, "From: $HELPDESK_FROM");
    }
    $submitter = $ticket['ContactEmail'];
    @mail($submitter, $subject, $body, "From: $HELPDESK_FROM");
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
