<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/auth_check.php';

$pdo = getPDO();

try {
    $stmt = $pdo->query(<<<SQL
        SELECT i.*, 
               CONCAT(c.first_name, ' ', c.last_name) AS kunde_name, 
               c.company
        FROM invoices i
        JOIN contacts c ON i.kunden_id = c.id
        ORDER BY i.datum DESC
    SQL);

    $invoices = $stmt->fetchAll();

    header('Content-Type: application/json');
    echo json_encode($invoices);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Fehler beim Abrufen der Rechnungen.',
        'details' => $e->getMessage()
    ]);
}
