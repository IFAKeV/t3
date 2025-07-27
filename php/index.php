<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/models.php';

session_start();
$action = $_GET['action'] ?? 'dashboard';
$flash = '';
$agent = null;
$agents_overview = [];

if ($action !== 'login') {
    $token = $_COOKIE['agent_token'] ?? null;
    if (!$token) {
        header('Location: index.php?action=login');
        exit;
    }
    $agent = get_agent_by_token($token);
    if (!$agent) {
        header('Location: index.php?action=login');
        exit;
    }
    $agents_overview = get_agents_with_ticket_counts();
    $status_map = get_availability_status_map();
    $week1_dates = [];
    $tmp = new DateTime('monday this week', new DateTimeZone('Europe/Berlin'));
    for ($i=0; $i<5; $i++) { $week1_dates[] = $tmp->format('Y-m-d'); $tmp->modify('+1 day'); }
    $week2_dates = [];
    $tmp = new DateTime('monday next week', new DateTimeZone('Europe/Berlin'));
    for ($i=0; $i<5; $i++) { $week2_dates[] = $tmp->format('Y-m-d'); $tmp->modify('+1 day'); }
    $availability_overview = [];
    foreach ($agents_overview as $ov) {
        $a1 = get_availability_for_agent($ov['AgentID'], $week1_dates[0], $week1_dates[4]);
        $a2 = get_availability_for_agent($ov['AgentID'], $week2_dates[0], $week2_dates[4]);
        $availability_overview[$ov['AgentID']] = ['week1'=>$a1,'week2'=>$a2];
    }
}

if ($action === 'login') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['token'] ?? '';
        $found = get_agent_by_token($token);
        if ($found) {
            setcookie('agent_token', $token, time()+30*24*60*60, '/');
            header('Location: index.php');
            exit;
        } else {
            $flash = 'Ungültiges Token';
        }
    }
    include 'templates/login.php';
    exit;
}

if ($action === 'logout') {
    setcookie('agent_token', '', time()-3600, '/');
    header('Location: index.php?action=login');
    exit;
}

if ($action === 'set_availability') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $date = $_POST['date'] ?? '';
        $status_id = intval($_POST['status_id'] ?? 1);
        if ($date) {
            upsert_agent_availability($agent['AgentID'], $date, $status_id);
        }
    }
    exit;
}

if ($action === 'availabilities') {
    $calendar_start = new DateTime('first day of this month', new DateTimeZone('Europe/Berlin'));
    $calendar_end = (clone $calendar_start)->modify('+2 month')->modify('last day of this month');
    $agents = load_agents();
    $status_map = get_availability_status_map();
    $avail_data = [];
    foreach ($agents as $ag) {
        $avail_data[$ag['AgentID']] = get_availability_for_agent($ag['AgentID'], $calendar_start->format('Y-m-d'), $calendar_end->format('Y-m-d'));
    }
    include 'templates/availability_overview.php';
    exit;
}

if ($action === 'edit_availability') {
    $calendar_start = new DateTime('first day of this month', new DateTimeZone('Europe/Berlin'));
    $calendar_end = (clone $calendar_start)->modify('+2 month')->modify('last day of this month');
    $statuses = get_availability_statuses();
    $status_map = get_availability_status_map();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = $_POST['status'] ?? [];
        foreach ($data as $date => $status_id) {
            upsert_agent_availability($agent['AgentID'], $date, intval($status_id));
        }
        $flash = 'Gespeichert';
    }
    $availability = get_availability_for_agent($agent['AgentID'], $calendar_start->format('Y-m-d'), $calendar_end->format('Y-m-d'));
    include 'templates/availability_edit.php';
    exit;
}

if ($action === 'new_ticket') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $priority_id = $_POST['priority_id'];
        $team_id = $_POST['team_id'];
        $contact_name = $_POST['contact_name'];
        $contact_phone = $_POST['contact_phone'] ?? null;
        $contact_email = $_POST['contact_email'] ?? null;
        $contact_employee_id = $_POST['contact_employee_id'] ?? null;
        $facility_id = $_POST['facility_id'] ?? null;
        $location_id = $_POST['location_id'] ?? null;
        $department_id = $_POST['department_id'] ?? null;
        $source = $_POST['source'] ?? null;
        $ticket_id = insert_db('Tickets',
            ['Title','Description','PriorityID','TeamID','StatusID','CreatedAt','CreatedByAgentID','Source','ContactName','ContactPhone','ContactEmail','ContactEmployeeID','FacilityID','LocationID','DepartmentID'],
            [$title,$description,$priority_id,$team_id,1,get_local_timestamp(),$agent['AgentID'],$source,$contact_name,$contact_phone,$contact_email,$contact_employee_id,$facility_id,$location_id,$department_id]
        );

        $assigned_agent = $_POST['assigned_agent'] ?? '';
        if ($assigned_agent) {
            $info = query_db('SELECT AgentName FROM Agents WHERE AgentID = ?', [$assigned_agent], true);
            if ($info) {
                insert_db('TicketAssignees', ['TicketID','AgentID','AgentName','AssignedAt'], [$ticket_id,$assigned_agent,$info['AgentName'],get_local_timestamp()]);
                $assign_time = (new DateTime('now', new DateTimeZone('Europe/Berlin')))->format('d.m.Y H:i');
                $assign_note = $agent['AgentName'] . ' hat am ' . $assign_time . ' ' . $info['AgentName'] . ' zugewiesen.';
                insert_db('TicketUpdates', ['TicketID','UpdatedByName','UpdateText','IsSolution','UpdatedAt'], [$ticket_id,$agent['AgentName'],$assign_note,0,get_local_timestamp()]);
            }
        }
        if (!empty($_FILES['attachment']['name']) && allowed_file($_FILES['attachment']['name'])) {
            $filename = basename($_FILES['attachment']['name']);
            $save_name = time() . '_' . $filename;
            $path = $UPLOAD_FOLDER . '/' . $save_name;
            move_uploaded_file($_FILES['attachment']['tmp_name'], $path);
            optimize_image($path);
            $size = filesize($path);
            insert_db('TicketAttachments',
                ['TicketID','FileName','StoragePath','FileSize','UploadedAt'],
                [$ticket_id,$filename,$save_name,$size,get_local_timestamp()]
            );
        }
        header('Location: index.php?action=view_ticket&id=' . $ticket_id);
        exit;
    }
    $priorities = get_all_priorities();
    $teams = get_all_teams();
    $agents = load_agents();
    include 'templates/ticket_form.php';
    exit;
}

if ($action === 'update_ticket') {
    $ticket_id = intval($_GET['id']);
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $ticket = get_ticket_by_id($ticket_id);
        if ($ticket) {
            $status_id = $_POST['status_id'] ?? '';
            $priority_id = $_POST['priority_id'] ?? '';
            $assign_agent = $_POST['assign_agent'] ?? '';
            $update_text = trim($_POST['update_text'] ?? '');
            $is_solution = isset($_POST['is_solution']) ? 1 : 0;

            if ($is_solution) {
                $solved = query_db("SELECT StatusID FROM TicketStatus WHERE StatusName = 'Gelöst'", [], true);
                if ($solved) { $status_id = $solved['StatusID']; }
            }

            if ($status_id && $status_id != $ticket['StatusID']) {
                update_db('Tickets', 'TicketID', $ticket_id, ['StatusID'], [$status_id]);
            }

            if ($priority_id && $priority_id != $ticket['PriorityID']) {
                update_db('Tickets', 'TicketID', $ticket_id, ['PriorityID'], [$priority_id]);
            }

            if ($assign_agent) {
                $existing = query_db('SELECT 1 FROM TicketAssignees WHERE TicketID = ? AND AgentID = ?', [$ticket_id, $assign_agent], true);
                if (!$existing) {
                    $info = query_db('SELECT AgentName FROM Agents WHERE AgentID = ?', [$assign_agent], true);
                    if ($info) {
                        insert_db('TicketAssignees', ['TicketID','AgentID','AgentName','AssignedAt'], [$ticket_id,$assign_agent,$info['AgentName'],get_local_timestamp()]);
                        $assign_time = (new DateTime('now', new DateTimeZone('Europe/Berlin')))->format('d.m.Y H:i');
                        $assign_note = $agent['AgentName'] . ' hat am ' . $assign_time . ' ' . $info['AgentName'] . ' zugewiesen.';
                        if ($update_text === '') {
                            $update_text = $assign_note;
                        } else {
                            $update_text .= "\n\n" . $assign_note;
                        }
                    }
                }
            }

            if ($update_text !== '' || $is_solution) {
                insert_db('TicketUpdates', ['TicketID','UpdatedByName','UpdateText','IsSolution','UpdatedAt'], [$ticket_id,$agent['AgentName'],$update_text,$is_solution,get_local_timestamp()]);
            }

            if (!empty($_FILES['attachment']['name']) && allowed_file($_FILES['attachment']['name'])) {
                $filename = basename($_FILES['attachment']['name']);
                $save_name = time() . '_' . $filename;
                $path = $UPLOAD_FOLDER . '/' . $save_name;
                move_uploaded_file($_FILES['attachment']['tmp_name'], $path);
                optimize_image($path);
                $size = filesize($path);
                insert_db('TicketAttachments', ['TicketID','FileName','StoragePath','FileSize','UploadedAt'], [$ticket_id,$filename,$save_name,$size,get_local_timestamp()]);
            }
        }
        header('Location: index.php?action=view_ticket&id=' . $ticket_id);
        exit;
    }
}

if ($action === 'view_ticket') {
    $ticket_id = intval($_GET['id']);
    $ticket = get_ticket_by_id($ticket_id);
    $attachments = get_ticket_attachments($ticket_id);
    $updates = get_ticket_updates($ticket_id);
    $assignees = get_ticket_assignees($ticket_id);
    $related_person = [];
    $related_facility = [];
    $related_location = [];
    $seen_ids = [];

    if ($ticket && $ticket['ContactEmployeeID']) {
        $related_person = get_related_tickets_by_person($ticket['ContactEmployeeID'], $ticket_id);
        foreach ($related_person as $rp) {
            $seen_ids[$rp['TicketID']] = true;
        }
    }

    if ($ticket && $ticket['FacilityID']) {
        $temp = get_related_tickets_by_facility($ticket['FacilityID'], $ticket_id);
        foreach ($temp as $row) {
            if (!isset($seen_ids[$row['TicketID']])) {
                $related_facility[] = $row;
                $seen_ids[$row['TicketID']] = true;
            }
        }
    }

    if ($ticket && $ticket['LocationID']) {
        $temp = get_related_tickets_by_location($ticket['LocationID'], $ticket_id, $ticket['FacilityID'] ?? null);
        foreach ($temp as $row) {
            if (!isset($seen_ids[$row['TicketID']])) {
                $related_location[] = $row;
                $seen_ids[$row['TicketID']] = true;
            }
        }
    }

    $facility_info = null;
    $location_info = null;
    if ($ticket && $ticket['FacilityID']) {
        $facility_info = get_facility_info($ticket['FacilityID']);
    }
    if ($ticket && $ticket['LocationID']) {
        $location_info = get_location_info($ticket['LocationID']);
    }
    $statuses = get_all_statuses();
    $priorities = get_all_priorities();
    $agents = load_agents();
    include 'templates/ticket_view.php';
    exit;
}

// default dashboard with filters
$team_filter = $_GET['team'] ?? 'mine';
$status_filter = $_GET['status'] ?? 'open';
$search_value = trim($_GET['q'] ?? '');
$agent_filter_param = $_GET['agent'] ?? null;

$team_id = null;
$filter_agent = null;
$assigned_only = false;

if ($team_filter === 'my_team') {
    $team_id = $agent['TeamID'];
} elseif ($team_filter === 'all') {
    $team_id = null;
} elseif ($team_filter === 'mine') {
    $filter_agent = $agent['AgentID'];
    $assigned_only = true;
} elseif (ctype_digit($team_filter)) {
    $team_id = intval($team_filter);
}

if ($agent_filter_param) {
    $filter_agent = intval($agent_filter_param);
    $assigned_only = true;
}

$tickets = get_tickets_with_filters($team_id, $status_filter, $search_value ?: null, $filter_agent, $assigned_only);

// mark unassigned tickets that exceed configured thresholds based on priority
foreach ($tickets as &$t) {
    $created = new DateTime($t['CreatedAtTS']);
    $now = new DateTime('now', new DateTimeZone('Europe/Berlin'));
    $hours_total = ($now->getTimestamp() - $created->getTimestamp()) / 3600;
    $t['AgeHours'] = (int) floor($hours_total);
    $t['Delayed'] = false;
    if (empty($t['AssignedAgents'])) {
        $prio = $t['PriorityID'];
        $threshold = $UNASSIGNED_WARNING_HOURS[$prio] ?? null;
        if ($threshold !== null && $t['AgeHours'] > $threshold) {
            $t['Delayed'] = true;
        }
    }
}
unset($t);
$teams = get_all_teams();
$statuses = get_all_statuses();
$agents = load_agents();
$current_team_filter = $team_filter;
$current_status_filter = $status_filter;
$current_agent_filter = $agent_filter_param;
$search_term = $search_value;
include 'templates/dashboard.php';
