<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/config.php';

$pdo = getPDO();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$method = $_SERVER['REQUEST_METHOD'];
$override = isset($_GET['_method']) ? strtoupper($_GET['_method']) : null;

if (!($method === 'PUT' || ($method === 'POST' && $override === 'PUT'))) {
    http_response_code(405);
    echo json_encode(['error' => 'Methode nicht erlaubt.']);
    exit;
}

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Ungültige Passwort-ID.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['title'], $data['username'], $data['password'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Fehlende Pflichtfelder: title, username, password']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE passwords SET
            title = :title,
            username = :username,
            password = :password,
            notes = :notes,
            last_modified = NOW()
        WHERE id = :id
    ");

    $stmt->execute([
        ':title' => $data['title'],
        ':username' => $data['username'],
        ':password' => $data['password'],
        ':notes' => $data['notes'] ?? null,
        ':id' => $id
    ]);

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Fehler beim Aktualisieren des Passworts.',
        'details' => $e->getMessage()
    ]);
}
