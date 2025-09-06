<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models.php';

header('Content-Type: application/json');
$term = $_GET['term'] ?? '';
$person = $_GET['person'] ?? null;
$facility = $_GET['facility'] ?? null;
if (strlen($term) < 2 && !$person && !$facility) {
    echo json_encode([]);
    exit;
}
$results = search_tickets($term, $person, $facility);
echo json_encode($results);
