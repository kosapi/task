<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

$jsonFile = __DIR__ . '/../data/checklist.json';

if (!file_exists($jsonFile)) {
    http_response_code(404);
    echo json_encode(['error' => 'checklist.json not found']);
    exit;
}

echo file_get_contents($jsonFile);
