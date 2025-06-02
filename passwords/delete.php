<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/config.php';

$pdo = getPDO();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$method = $_SERVER['REQUEST_METHOD'];
$override = isset($_GET['_method']) ? strtoupper($_GET['_method']) : null;

if (!($method === 'DELETE' || ($method === 'POST' && $override === 'DELETE'))) {
    http_response_code(405);
    echo json_encode(['error' => 'Methode nicht erlaubt.']);
    exit;
}

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Ungültige Passwort-ID.']);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM passwords WHERE id = :id");
    $stmt->execute([':id' => $id]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Passwort nicht gefunden oder bereits gelöscht.']);
        exit;
    }

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Fehler beim Löschen des Passworts.',
        'details' => $e->getMessage()
    ]);
}
