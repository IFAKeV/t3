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

function send_assignment_email($agent_email, $agent_name, $ticket) {
    if (!$agent_email) return;
    $base_url = get_base_url();
    $link = $base_url . '/index.php?action=view_ticket&id=' . $ticket['TicketID'];
    $subject = 'Ticket #' . $ticket['TicketID'] . ' zugewiesen';
    $priority = $ticket['PriorityName'] ?? '';
    global $REACTION_TIME_HOURS, $HELPDESK_FROM;
    $reaction = $ticket['PriorityID'] ? ($REACTION_TIME_HOURS[$ticket['PriorityID']] ?? null) : null;
    $body = "Hallo $agent_name,\n\n" .
            "Dir wurde ein neues Ticket zugewiesen:\n" .
            "Titel: {$ticket['Title']}\n" .
            ($priority ? "Priorität: $priority\n" : '') .
            ($reaction ? "Reaktionszeit: {$reaction}h\n" : '') .
            "Kontakt: {$ticket['ContactName']}\n" .
            ($ticket['ContactPhone'] ? "Telefon: {$ticket['ContactPhone']}\n" : '') .
            ($ticket['ContactEmail'] ? "E-Mail: {$ticket['ContactEmail']}\n" : '') .
            "\nZum Ticket: $link\n\n" .
            "Beschreibung:\n{$ticket['Description']}\n";
    @mail($agent_email, $subject, $body, "From: $HELPDESK_FROM");
}
?>
