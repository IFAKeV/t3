<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models.php';

header('Content-Type: application/json');
$employee_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($employee_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid ID']);
    exit;
}
$employee = get_employee_details($employee_id);
if (!$employee) {
    http_response_code(404);
    echo json_encode(['error' => 'Mitarbeiter nicht gefunden']);
    exit;
}
echo json_encode($employee);

