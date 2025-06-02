<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/config.php';

$pdo = getPDO();

$contactId = isset($_GET['contact_id']) ? (int) $_GET['contact_id'] : 0;
$passwordId = isset($_GET['password_id']) ? (int) $_GET['password_id'] : 0;

if ($contactId <= 0 || $passwordId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Ungültige ID-Angabe.']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        DELETE FROM contact_password
        WHERE contact_id = :contact_id AND password_id = :password_id
    ");
    $stmt->execute([
        ':contact_id' => $contactId,
        ':password_id' => $passwordId
    ]);

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Fehler beim Entknüpfen.',
        'details' => $e->getMessage()
    ]);
}
