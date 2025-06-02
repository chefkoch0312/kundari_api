<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/config.php';

$pdo = getPDO();

$contactId = isset($_GET['contact_id']) ? (int) $_GET['contact_id'] : 0;

if ($contactId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Ungültige Kontakt-ID.']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT p.id, p.title, p.username, p.password, p.notes, p.created_at, p.last_modified
        FROM passwords p
        JOIN contact_password cp ON p.id = cp.password_id
        WHERE cp.contact_id = :contact_id
        ORDER BY p.title ASC
    ");
    $stmt->execute([':contact_id' => $contactId]);
    $passwords = $stmt->fetchAll();

    header('Content-Type: application/json');
    echo json_encode($passwords);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Fehler beim Abrufen der Passwörter.',
        'details' => $e->getMessage()
    ]);
}
