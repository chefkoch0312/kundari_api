<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/config.php';

$pdo = getPDO();

try {
    $stmt = $pdo->query("SELECT * FROM contacts ORDER BY last_name ASC, first_name ASC");
    $contacts = $stmt->fetchAll();

    header('Content-Type: application/json');
    echo json_encode($contacts);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Fehler beim Abrufen der Kontakte.',
        'details' => $e->getMessage()
    ]);
}
