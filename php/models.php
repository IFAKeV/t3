<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

function get_all_teams() {
    return query_db("SELECT TeamID, TeamName, TeamColor, TeamDescription FROM Teams ORDER BY TeamName");
}

function get_all_statuses() {
    return query_db("SELECT StatusID, StatusName, ColorCode FROM TicketStatus ORDER BY StatusID");
}

function get_all_priorities() {
    return query_db("SELECT PriorityID, PriorityName, ColorCode FROM TicketPriorities ORDER BY PriorityID");
}

function load_agents() {
    return query_db("SELECT a.AgentID, a.AgentName, a.AgentEmail, a.Token, a.Active, a.TeamID, t.TeamName, t.TeamColor FROM Agents a JOIN Teams t ON a.TeamID = t.TeamID WHERE a.Active = 1 ORDER BY a.AgentName");
}

function get_agent_by_token($token) {
    return query_db("SELECT a.AgentID, a.AgentName, a.AgentEmail, a.Token, a.Active, a.TeamID, t.TeamName, t.TeamColor FROM Agents a JOIN Teams t ON a.TeamID = t.TeamID WHERE a.Token = ? AND a.Active = 1", [$token], true);
}

function get_agents_with_ticket_counts() {
    $query = "SELECT a.AgentID, a.AgentName, a.TeamID, t.TeamName, " .
             "COUNT(CASE WHEN s.StatusName != 'Gelöst' THEN 1 END) AS OpenTickets " .
             "FROM Agents a " .
             "JOIN Teams t ON a.TeamID = t.TeamID " .
             "LEFT JOIN TicketAssignees ta ON a.AgentID = ta.AgentID " .
             "LEFT JOIN Tickets tk ON tk.TicketID = ta.TicketID " .
             "LEFT JOIN TicketStatus s ON tk.StatusID = s.StatusID " .
             "WHERE a.Active = 1 " .
             "GROUP BY a.AgentID ORDER BY a.AgentName";
    return query_db($query);
}

function get_tickets_with_filters($team_id = null, $status_filter = 'open', $search_term = null, $agent_id = null, $assigned_only = false) {
    $base_query = "SELECT t.TicketID, t.Title, t.Description, t.StatusID, t.PriorityID, t.TeamID, t.ContactName, t.ContactPhone, t.ContactEmail, a.AgentName AS CreatedByName, t.Source, s.StatusName, s.ColorCode as StatusColor, p.PriorityName, p.ColorCode as PriorityColor, team.TeamName, team.TeamColor, strftime('%d.%m.%Y %H:%M', t.CreatedAt, 'localtime') as CreatedAt, CAST(julianday('now') - julianday(t.CreatedAt) AS INT) as AgeDays, GROUP_CONCAT(ta.AgentName, ', ') as AssignedAgents FROM Tickets t JOIN TicketStatus s ON t.StatusID = s.StatusID JOIN TicketPriorities p ON t.PriorityID = p.PriorityID JOIN Teams team ON t.TeamID = team.TeamID JOIN Agents a ON t.CreatedByAgentID = a.AgentID LEFT JOIN TicketAssignees ta ON t.TicketID = ta.TicketID";
    $conditions = [];
    $params = [];
    if ($team_id) { $conditions[] = 't.TeamID = ?'; $params[] = $team_id; }
    if ($status_filter === 'open') {
        $conditions[] = "s.StatusName != 'Gelöst'"; // Gelöst
    } elseif ($status_filter !== 'all') {
        $conditions[] = 's.StatusName = ?'; $params[] = $status_filter; }
    if ($search_term) {
        $conditions[] = '(t.Title LIKE ? OR t.TicketID = ?)';
        $params[] = "%$search_term%";
        $params[] = ctype_digit($search_term) ? $search_term : -1;
    }
    if ($agent_id) {
        if ($assigned_only) {
            $conditions[] = 'ta.AgentID = ?'; $params[] = $agent_id;
        } else {
            $conditions[] = '(ta.AgentID = ? OR t.CreatedByAgentID = ?)';
            $params[] = $agent_id; $params[] = $agent_id;
        }
    }
    if ($conditions) { $base_query .= ' WHERE ' . implode(' AND ', $conditions); }
    $base_query .= ' GROUP BY t.TicketID ORDER BY t.CreatedAt DESC';
    return query_db($base_query, $params);
}

function get_ticket_by_id($ticket_id) {
    $query = "SELECT t.TicketID, t.Title, t.Description, t.StatusID, t.PriorityID, t.TeamID, t.ContactName, t.ContactPhone, t.ContactEmail, t.ContactEmployeeID, t.FacilityID, t.LocationID, t.DepartmentID, a.AgentName AS CreatedByName, t.Source, s.StatusName, s.ColorCode as StatusColor, p.PriorityName, p.ColorCode as PriorityColor, team.TeamName, team.TeamColor, strftime('%d.%m.%Y %H:%M', t.CreatedAt, 'localtime') as CreatedAt, CAST(julianday('now') - julianday(t.CreatedAt) AS INT) as AgeDays FROM Tickets t JOIN TicketStatus s ON t.StatusID = s.StatusID JOIN TicketPriorities p ON t.PriorityID = p.PriorityID JOIN Teams team ON t.TeamID = team.TeamID JOIN Agents a ON t.CreatedByAgentID = a.AgentID WHERE t.TicketID = ?";
    return query_db($query, [$ticket_id], true);
}

function search_tickets($term, $limit = 10) {
    $query = "SELECT TicketID, Title FROM Tickets WHERE Title LIKE ? OR CAST(TicketID AS TEXT) LIKE ? ORDER BY CreatedAt DESC LIMIT ?";
    return query_db($query, ["%$term%", "%$term%", $limit]);
}

function get_status_by_id($status_id) {
    return query_db("SELECT StatusID, StatusName, ColorCode FROM TicketStatus WHERE StatusID = ?", [$status_id], true);
}

function get_priority_by_id($priority_id) {
    return query_db("SELECT PriorityID, PriorityName, ColorCode FROM TicketPriorities WHERE PriorityID = ?", [$priority_id], true);
}

function get_ticket_updates($ticket_id) {
    $query = "SELECT UpdateID, TicketID, UpdatedByName, UpdateText, IsSolution, strftime('%d.%m.%Y %H:%M', UpdatedAt, 'localtime') as FormattedUpdatedAt FROM TicketUpdates WHERE TicketID = ? ORDER BY UpdatedAt ASC";
    return query_db($query, [$ticket_id]);
}

function get_ticket_attachments($ticket_id) {
    $query = "SELECT AttachmentID, FileName, StoragePath, FileSize, strftime('%d.%m.%Y %H:%M', UploadedAt, 'localtime') as FormattedUploadedAt FROM TicketAttachments WHERE TicketID = ? ORDER BY UploadedAt ASC";
    return query_db($query, [$ticket_id]);
}

function get_ticket_assignees($ticket_id) {
    $query = "SELECT AgentID, AgentName, strftime('%d.%m.%Y %H:%M', AssignedAt, 'localtime') as AssignedAt FROM TicketAssignees WHERE TicketID = ? ORDER BY AssignedAt DESC";
    return query_db($query, [$ticket_id]);
}
?>
