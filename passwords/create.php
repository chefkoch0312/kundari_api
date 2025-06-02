<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/config.php';

$pdo = getPDO();

$data = json_decode(file_get_contents('php://input'), true);

if (
    !isset($data['title'], $data['username'], $data['password']) ||
    !isset($data['contact_id'])
) {
    http_response_code(400);
    echo json_encode(['error' => 'Fehlende Pflichtfelder: title, username, password, contact_id']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO passwords (title, username, password, notes)
        VALUES (:title, :username, :password, :notes)
    ");
    $stmt->execute([
        ':title' => $data['title'],
        ':username' => $data['username'],
        ':password' => $data['password'],
        ':notes' => $data['notes'] ?? null,
    ]);

    $passwordId = $pdo->lastInsertId();

    // Zuordnung zum Kontakt
    $link = $pdo->prepare("
        INSERT INTO contact_password (contact_id, password_id)
        VALUES (:contact_id, :password_id)
    ");
    $link->execute([
        ':contact_id' => $data['contact_id'],
        ':password_id' => $passwordId
    ]);

    echo json_encode(['success' => true, 'id' => $passwordId]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Fehler beim Erstellen des Passworts.',
        'details' => $e->getMessage()
    ]);
}
