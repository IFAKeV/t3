<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models.php';

header('Content-Type: application/json');
$term = $_GET['term'] ?? '';
if (strlen($term) < 2) {
    echo json_encode([]);
    exit;
}
$results = search_employees($term);
echo json_encode($results);
