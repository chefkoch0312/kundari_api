<?php
require_once __DIR__ . '/config.php';

const API_KEY = 'super-secret-kundari-api-key'; 

$headers = getallheaders();
$clientKey = $headers['X-Kundari-Key'] ?? '';

if ($clientKey !== API_KEY) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized: API key missing or invalid.']);
    exit;
}
