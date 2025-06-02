<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/config.php';

$pdo = getPDO();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Ungültige Rechnungs-ID.']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT i.*, 
               CONCAT(c.first_name, ' ', c.last_name) AS kunde_name,
               c.company
        FROM invoices i
        JOIN contacts c ON i.kunden_id = c.id
        WHERE i.id = :id
    ");
    $stmt->execute([':id' => $id]);
    $invoice = $stmt->fetch();

    if (!$invoice) {
        http_response_code(404);
        echo json_encode(['error' => 'Rechnung nicht gefunden.']);
        exit;
    }

    header('Content-Type: application/json');
    echo json_encode($invoice);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Fehler beim Abrufen der Rechnung.',
        'details' => $e->getMessage()
    ]);
}
