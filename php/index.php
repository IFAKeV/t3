<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/models.php';

session_start();
$action = $_GET['action'] ?? 'dashboard';
$flash = '';
$agent = null;

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

if ($action === 'new_ticket') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $priority_id = $_POST['priority_id'];
        $team_id = $_POST['team_id'];
        $contact_name = $_POST['contact_name'];
        $ticket_id = insert_db('Tickets',
            ['Title','Description','PriorityID','TeamID','StatusID','CreatedAt','CreatedByAgentID','ContactName'],
            [$title,$description,$priority_id,$team_id,1,get_local_timestamp(),$agent['AgentID'],$contact_name]
        );
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
    $statuses = get_all_statuses();
    $priorities = get_all_priorities();
    $agents = load_agents();
    include 'templates/ticket_view.php';
    exit;
}

// default dashboard
$team_id = null;
$tickets = get_tickets_with_filters($team_id, 'open', null, $agent['AgentID']);
include 'templates/dashboard.php';
