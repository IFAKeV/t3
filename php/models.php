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
             "COUNT(CASE WHEN s.StatusName NOT IN ('Gelöst','Storniert') THEN 1 END) AS OpenTickets, " .
             "COUNT(DISTINCT tu.TicketID) AS SolvedTickets " .
             "FROM Agents a " .
             "JOIN Teams t ON a.TeamID = t.TeamID " .
             "LEFT JOIN (" .
             "    SELECT ta.TicketID, ta.AgentID " .
             "    FROM TicketAssignees ta " .
             "    JOIN (" .
             "        SELECT TicketID, MAX(AssignedAt) AS MaxAssignedAt FROM TicketAssignees GROUP BY TicketID" .
             "    ) last ON ta.TicketID = last.TicketID AND ta.AssignedAt = last.MaxAssignedAt" .
             ") la ON a.AgentID = la.AgentID " .
             "LEFT JOIN Tickets tk ON tk.TicketID = la.TicketID " .
             "LEFT JOIN TicketStatus s ON tk.StatusID = s.StatusID " .
             "LEFT JOIN TicketUpdates tu ON tk.TicketID = tu.TicketID AND tu.IsSolution = 1 AND tu.UpdatedByName = a.AgentName " .
             "WHERE a.Active = 1 " .
             "GROUP BY a.AgentID ORDER BY a.AgentName";
    return query_db($query);
}

function get_tickets_with_filters($team_id = null, $status_filter = 'open', $search_term = null, $agent_id = null, $assigned_only = false, $include_unassigned_new = false, $agent_team_id = null) {
    $base_query = "SELECT t.TicketID, t.Title, t.Description, t.StatusID, t.PriorityID, t.TeamID, COALESCE(emp.FirstName || ' ' || emp.LastName, t.ContactName) AS ContactName, t.ContactPhone, t.ContactEmail, a.AgentName AS CreatedByName, t.Source, s.StatusName, s.ColorCode as StatusColor, p.PriorityName, p.ColorCode as PriorityColor, team.TeamName, team.TeamColor, fac.Facility AS FacilityName, loc.Location AS LocationName, strftime('%d.%m.%Y %H:%M', t.CreatedAt) as CreatedAt, t.CreatedAt as CreatedAtTS, CAST(julianday('now') - julianday(t.CreatedAt) AS INT) as AgeDays, COALESCE(u.LastUpdatedAt, t.CreatedAt) as LastUpdatedTS, CAST(julianday('now') - julianday(COALESCE(u.LastUpdatedAt, t.CreatedAt)) AS INT) as LastUpdatedDays, GROUP_CONCAT(ta.AgentName, ', ') as AssignedAgents, COUNT(ta.AgentID) as AssignedCount FROM Tickets t JOIN TicketStatus s ON t.StatusID = s.StatusID JOIN TicketPriorities p ON t.PriorityID = p.PriorityID JOIN Teams team ON t.TeamID = team.TeamID JOIN Agents a ON t.CreatedByAgentID = a.AgentID LEFT JOIN address.Employees emp ON t.ContactEmployeeID = emp.EmployeeID LEFT JOIN address.Facilities fac ON t.FacilityID = fac.FacilityID LEFT JOIN address.Locations loc ON loc.LocationID = COALESCE(t.LocationID, fac.LocationID) LEFT JOIN (SELECT TicketID, MAX(UpdatedAt) AS LastUpdatedAt FROM TicketUpdates GROUP BY TicketID) u ON t.TicketID = u.TicketID LEFT JOIN (SELECT ta1.TicketID, ta1.AgentID, ta1.AgentName FROM TicketAssignees ta1 JOIN (SELECT TicketID, MAX(AssignedAt) AS MaxAssignedAt FROM TicketAssignees GROUP BY TicketID) ta2 ON ta1.TicketID = ta2.TicketID AND ta1.AssignedAt = ta2.MaxAssignedAt) ta ON t.TicketID = ta.TicketID";
    $conditions = [];
    $params = [];
    if ($team_id) { $conditions[] = 't.TeamID = ?'; $params[] = $team_id; }
    if ($status_filter === 'open') {
        $conditions[] = "s.StatusName NOT IN ('Gelöst','Storniert')"; // offen
    } elseif ($status_filter !== 'all') {
        if (is_int($status_filter)) {
            $conditions[] = 's.StatusID = ?';
            $params[] = $status_filter;
        } elseif (is_string($status_filter) && ctype_digit($status_filter)) {
            $conditions[] = 's.StatusID = ?';
            $params[] = intval($status_filter);
        } else {
            $conditions[] = 's.StatusName = ?';
            $params[] = $status_filter;
        }
    }
    if ($search_term) {
        $conditions[] = "(t.Title LIKE ? OR t.Description LIKE ? OR CAST(t.TicketID AS TEXT) LIKE ? OR t.ContactName LIKE ? OR (emp.FirstName || ' ' || emp.LastName) LIKE ? OR fac.Facility LIKE ? OR loc.Location LIKE ? OR EXISTS (SELECT 1 FROM TicketUpdates tu WHERE tu.TicketID = t.TicketID AND (tu.UpdateText LIKE ? OR tu.UpdatedByName LIKE ?)))";
        $like = "%$search_term%";
        $params = array_merge($params, array_fill(0, 7, $like));
        $params[] = $like;
        $params[] = $like;
    }
    if ($agent_id) {
        if ($assigned_only) {
            if ($include_unassigned_new && $agent_team_id) {
                $conditions[] = '(ta.AgentID = ? OR (t.TeamID = ? AND s.StatusName = "Neu" AND NOT EXISTS (SELECT 1 FROM TicketAssignees ta2 WHERE ta2.TicketID = t.TicketID)))';
                $params[] = $agent_id;
                $params[] = $agent_team_id;
            } else {
                $conditions[] = 'ta.AgentID = ?';
                $params[] = $agent_id;
            }
        } else {
            $conditions[] = '(ta.AgentID = ? OR t.CreatedByAgentID = ?)';
            $params[] = $agent_id; $params[] = $agent_id;
        }
    } elseif ($include_unassigned_new && $agent_team_id) {
        $conditions[] = '(t.TeamID = ? AND s.StatusName = "Neu" AND NOT EXISTS (SELECT 1 FROM TicketAssignees ta2 WHERE ta2.TicketID = t.TicketID))';
        $params[] = $agent_team_id;
    }
    if ($conditions) { $base_query .= ' WHERE ' . implode(' AND ', $conditions); }
    $base_query .= " GROUP BY t.TicketID ORDER BY CASE WHEN COUNT(ta.AgentID) = 0 AND s.StatusName = 'Neu' THEN 0 ELSE 1 END, t.CreatedAt DESC";
    return query_db($base_query, $params);
}

function get_ticket_by_id($ticket_id) {
    $query = "SELECT t.TicketID, t.Title, t.Description, t.StatusID, t.PriorityID, t.TeamID, t.ContactName, t.ContactPhone, t.ContactEmail, t.ContactEmployeeID, t.FacilityID, t.LocationID, t.DepartmentID, a.AgentName AS CreatedByName, t.Source, s.StatusName, s.ColorCode as StatusColor, p.PriorityName, p.ColorCode as PriorityColor, team.TeamName, team.TeamColor, strftime('%d.%m.%Y %H:%M', t.CreatedAt) as CreatedAt, CAST(julianday('now') - julianday(t.CreatedAt) AS INT) as AgeDays FROM Tickets t JOIN TicketStatus s ON t.StatusID = s.StatusID JOIN TicketPriorities p ON t.PriorityID = p.PriorityID JOIN Teams team ON t.TeamID = team.TeamID JOIN Agents a ON t.CreatedByAgentID = a.AgentID WHERE t.TicketID = ?";
    return query_db($query, [$ticket_id], true);
}

function search_tickets($term, $person = null, $facility = null, $limit = 10) {
    $query = "SELECT t.TicketID, t.Title, COALESCE(emp.FirstName || ' ' || emp.LastName, t.ContactName) AS PersonName, fac.Facility AS FacilityName, loc.Location AS LocationName FROM Tickets t LEFT JOIN address.Employees emp ON t.ContactEmployeeID = emp.EmployeeID LEFT JOIN address.Facilities fac ON t.FacilityID = fac.FacilityID LEFT JOIN address.Locations loc ON loc.LocationID = COALESCE(t.LocationID, fac.LocationID) WHERE (t.Title LIKE ? OR t.Description LIKE ? OR CAST(t.TicketID AS TEXT) LIKE ? OR COALESCE(emp.FirstName || ' ' || emp.LastName, t.ContactName) LIKE ? OR fac.Facility LIKE ? OR loc.Location LIKE ? OR EXISTS (SELECT 1 FROM TicketUpdates tu WHERE tu.TicketID = t.TicketID AND (tu.UpdateText LIKE ? OR tu.UpdatedByName LIKE ?)))";
    $like = "%$term%";
    $params = array_fill(0, 6, $like);
    $params[] = $like;
    $params[] = $like;
    if ($person) {
        $query .= " AND ((emp.FirstName || ' ' || emp.LastName) LIKE ? OR t.ContactName LIKE ?)";
        $params[] = "%$person%";
        $params[] = "%$person%";
    }
    if ($facility) {
        $query .= " AND (fac.Facility LIKE ? OR loc.Location LIKE ?)";
        $params[] = "%$facility%";
        $params[] = "%$facility%";
    }
    $query .= " ORDER BY t.CreatedAt DESC LIMIT ?";
    $params[] = $limit;
    return query_db($query, $params);
}

// ----------------------------------------------------------------------
// Address book helpers
// ----------------------------------------------------------------------

function search_employees($search_term) {
    $query = "SELECT DISTINCT e.EmployeeID, e.FirstName, e.LastName, e.Phone, e.Mobile, e.Mail, " .
             "f.FacilityID, f.Facility, f.LocationID, f.DepartmentID, " .
             "l.Location, d.Department " .
             "FROM Employees e " .
             "LEFT JOIN FacilityLinks fl ON e.EmployeeID = fl.EmployeeID " .
             "LEFT JOIN Facilities f ON fl.FacilityID = f.FacilityID " .
             "LEFT JOIN Locations l ON f.LocationID = l.LocationID " .
             "LEFT JOIN Departments d ON f.DepartmentID = d.DepartmentID " .
             "WHERE e.FirstName LIKE ? OR e.LastName LIKE ? " .
             "ORDER BY e.LastName, e.FirstName LIMIT 10";
    $rows = query_db($query, ["%$search_term%", "%$search_term%"], false, 'address');

    $results = [];
    foreach ($rows as $row) {
        $results[] = format_contact_info($row);
    }
    return $results;
}

function get_employee_details($employee_id) {
    $query = "SELECT e.EmployeeID, e.FirstName, e.LastName, e.Phone, e.Mobile, e.Mail, " .
             "f.FacilityID, f.Facility, f.LocationID, f.DepartmentID, " .
             "l.Location, d.Department " .
             "FROM Employees e " .
             "LEFT JOIN FacilityLinks fl ON e.EmployeeID = fl.EmployeeID " .
             "LEFT JOIN Facilities f ON fl.FacilityID = f.FacilityID " .
             "LEFT JOIN Locations l ON f.LocationID = l.LocationID " .
             "LEFT JOIN Departments d ON f.DepartmentID = d.DepartmentID " .
             "WHERE e.EmployeeID = ? LIMIT 1";
    $row = query_db($query, [$employee_id], true, 'address');
    return $row ? format_contact_info($row) : null;
}

function format_contact_info($row) {
    if (!$row) return null;
    $phone = !empty($row['Mobile']) ? $row['Mobile'] : ($row['Phone'] ?? '');
    $full_name = trim(($row['FirstName'] ?? '') . ' ' . ($row['LastName'] ?? ''));
    $facility = $row['Facility'] ?? '';
    $location = $row['Location'] ?? '';
    $department = $row['Department'] ?? '';
    $org_info = '';
    if ($facility && $location) {
        $org_info = $facility . ' (' . $location . ')';
    } else {
        $org_info = $facility ?: $location;
    }
    return [
        'id' => $row['EmployeeID'] ?? null,
        'name' => $full_name,
        'phone' => $phone,
        'email' => $row['Mail'] ?? '',
        'facility_id' => $row['FacilityID'] ?? null,
        'facility_name' => $facility,
        'location_id' => $row['LocationID'] ?? null,
        'location_name' => $location,
        'department_id' => $row['DepartmentID'] ?? null,
        'department_name' => $department,
        'organization_info' => $org_info,
    ];
}

function get_facility_info($facility_id) {
    $query = "SELECT f.FacilityID, f.Facility, f.LocationID, f.DepartmentID, " .
             "l.Location, d.Department " .
             "FROM Facilities f " .
             "LEFT JOIN Locations l ON f.LocationID = l.LocationID " .
             "LEFT JOIN Departments d ON f.DepartmentID = d.DepartmentID " .
             "WHERE f.FacilityID = ?";
    return query_db($query, [$facility_id], true, 'address');
}

function get_location_info($location_id) {
    $query = "SELECT LocationID, Location, Short, Phone, Street, ZIP, Town FROM Locations WHERE LocationID = ?";
    return query_db($query, [$location_id], true, 'address');
}

function get_status_by_id($status_id) {
    return query_db("SELECT StatusID, StatusName, ColorCode FROM TicketStatus WHERE StatusID = ?", [$status_id], true);
}

function get_priority_by_id($priority_id) {
    return query_db("SELECT PriorityID, PriorityName, ColorCode FROM TicketPriorities WHERE PriorityID = ?", [$priority_id], true);
}

function get_ticket_updates($ticket_id) {
    $query = "SELECT UpdateID, TicketID, UpdatedByName, UpdateText, IsSolution, strftime('%d.%m.%Y %H:%M', UpdatedAt) as FormattedUpdatedAt FROM TicketUpdates WHERE TicketID = ? ORDER BY UpdatedAt DESC";
    return query_db($query, [$ticket_id]);
}

function get_ticket_attachments($ticket_id) {
    $query = "SELECT AttachmentID, FileName, StoragePath, FileSize, strftime('%d.%m.%Y %H:%M', UploadedAt) as FormattedUploadedAt FROM TicketAttachments WHERE TicketID = ? ORDER BY UploadedAt ASC";
    return query_db($query, [$ticket_id]);
}

function get_ticket_assignees($ticket_id) {
    $query = "SELECT AgentID, AgentName, strftime('%d.%m.%Y %H:%M', AssignedAt) as AssignedAt FROM TicketAssignees WHERE TicketID = ? ORDER BY AssignedAt DESC";
    return query_db($query, [$ticket_id]);
}

// ----------------------------------------------------------------------
// Verwandte Tickets
// ----------------------------------------------------------------------

function get_related_tickets_by_person($employee_id, $exclude_id) {
    $query = "SELECT t.TicketID, t.Title, t.ContactName, s.StatusName, s.ColorCode, " .
             "team.TeamName, team.TeamColor, " .
             "strftime('%d.%m.%Y', t.CreatedAt) AS CreatedAt " .
             "FROM Tickets t " .
             "JOIN TicketStatus s ON t.StatusID = s.StatusID " .
             "JOIN Teams team ON t.TeamID = team.TeamID " .
             "WHERE t.ContactEmployeeID = ? AND t.TicketID != ? AND s.StatusName != 'Gelöst' " .
             "ORDER BY t.CreatedAt DESC LIMIT 5";
    return query_db($query, [$employee_id, $exclude_id]);
}

function get_related_tickets_by_facility($facility_id, $exclude_id) {
    $query = "SELECT t.TicketID, t.Title, t.ContactName, s.StatusName, s.ColorCode, " .
             "team.TeamName, team.TeamColor, " .
             "strftime('%d.%m.%Y', t.CreatedAt) AS CreatedAt " .
             "FROM Tickets t " .
             "JOIN TicketStatus s ON t.StatusID = s.StatusID " .
             "JOIN Teams team ON t.TeamID = team.TeamID " .
             "WHERE t.FacilityID = ? AND t.TicketID != ? AND s.StatusName != 'Gelöst' " .
             "ORDER BY t.CreatedAt DESC LIMIT 5";
    return query_db($query, [$facility_id, $exclude_id]);
}

function get_related_tickets_by_location($location_id, $exclude_id, $facility_id = null) {
    $query = "SELECT t.TicketID, t.Title, t.ContactName, s.StatusName, s.ColorCode, " .
             "team.TeamName, team.TeamColor, " .
             "strftime('%d.%m.%Y', t.CreatedAt) AS CreatedAt " .
             "FROM Tickets t " .
             "JOIN TicketStatus s ON t.StatusID = s.StatusID " .
             "JOIN Teams team ON t.TeamID = team.TeamID " .
             "WHERE t.LocationID = ? AND t.TicketID != ? " .
             "AND (t.FacilityID != ? OR t.FacilityID IS NULL) " .
             "AND s.StatusName != 'Gelöst' " .
             "ORDER BY t.CreatedAt DESC LIMIT 5";
    $fid = $facility_id ? $facility_id : 0;
    return query_db($query, [$location_id, $exclude_id, $fid]);
}

// ----------------------------------------------------------------------
// Agent Availability
// ----------------------------------------------------------------------

function get_availability_statuses() {
    return query_db("SELECT StatusID, ShortCode, StatusName, ColorCode FROM AvailabilityStatuses ORDER BY StatusID");
}

function get_availability_status_map() {
    $rows = get_availability_statuses();
    $map = [];
    foreach ($rows as $r) {
        $map[$r['StatusID']] = $r;
    }
    return $map;
}

function get_availability_for_agent($agent_id, $start_date, $end_date) {
    $query = "SELECT Date, a.StatusID, s.ShortCode, s.ColorCode FROM AgentAvailability a JOIN AvailabilityStatuses s ON a.StatusID = s.StatusID WHERE a.AgentID = ? AND Date BETWEEN ? AND ?";
    $rows = query_db($query, [$agent_id, $start_date, $end_date]);
    $result = [];
    foreach ($rows as $row) {
        $result[$row['Date']] = $row;
    }
    return $result;
}

function upsert_agent_availability($agent_id, $date, $status_id) {
    $db = get_db();
    $stmt = $db->prepare('INSERT INTO AgentAvailability (AgentID, Date, StatusID) VALUES (?, ?, ?) ON CONFLICT(AgentID, Date) DO UPDATE SET StatusID=excluded.StatusID');
    bind_params($stmt, [$agent_id, $date, $status_id]);
    $stmt->execute();
    $stmt->close();
}
?>
