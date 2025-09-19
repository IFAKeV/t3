<?php
require_once __DIR__ . '/../models.php';
require_once __DIR__ . '/../push.php';

$now = new DateTime('now', new DateTimeZone('Europe/Berlin'));
$today = $now->format('Y-m-d');

// Unassigned open tickets
$query = "SELECT t.TicketID, t.Title, t.PriorityID, p.PriorityName, t.CreatedAt " .
         "FROM Tickets t " .
         "JOIN TicketPriorities p ON t.PriorityID = p.PriorityID " .
         "JOIN TicketStatus s ON t.StatusID = s.StatusID " .
         "WHERE s.StatusName != 'Gelöst' " .
         "AND NOT EXISTS (SELECT 1 FROM TicketAssignees ta WHERE ta.TicketID = t.TicketID)";
$tickets = query_db($query);

$overdue = [];
foreach ($tickets as $t) {
    $priority = (int)$t['PriorityID'];
    $threshold = $UNASSIGNED_WARNING_HOURS[$priority] ?? null;
    if (!$threshold) continue;
    try {
        $created = new DateTime($t['CreatedAt'], new DateTimeZone('Europe/Berlin'));
        $age_hours = ($now->getTimestamp() - $created->getTimestamp()) / 3600;
        if ($age_hours >= $threshold) {
            $overdue[] = $t;
        }
    } catch (Exception $e) {}
}

if (empty($overdue)) {
    exit;
}

$agents = load_agents();
$status_map = get_availability_status_map();
$available_id = $status_map[1]['StatusID'] ?? 1;

foreach ($agents as $ag) {
    $avail = get_availability_for_agent($ag['AgentID'], $today, $today);
    $status_id = $avail[$today]['StatusID'] ?? $available_id;
    if ($status_id != $available_id) continue;
    $lines = [];
    foreach ($overdue as $t) {
        $lines[] = '#' . $t['TicketID'] . ' (' . $t['PriorityName'] . ') ' . $t['Title'];
    }
    $body = "Folgende Tickets sind unzugewiesen:\n\n" . implode("\n", $lines) . "\n";
    send_mail_message($ag['AgentEmail'], 'Unzugewiesene Tickets', $body);
    send_push_notification([$ag['AgentID']], 'Unzugewiesene Tickets', $body);
}
