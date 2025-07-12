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

if ($action === 'view_ticket') {
    $ticket_id = intval($_GET['id']);
    $ticket = get_ticket_by_id($ticket_id);
    $attachments = query_db('SELECT FileName, StoragePath FROM TicketAttachments WHERE TicketID = ?', [$ticket_id]);
    include 'templates/ticket_view.php';
    exit;
}

// default dashboard
$team_id = null;
$tickets = get_tickets_with_filters($team_id, 'open', null, $agent['AgentID']);
include 'templates/dashboard.php';
