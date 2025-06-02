<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/config.php';

$pdo = getPDO();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Ungültige Passwort-ID.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM passwords WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $password = $stmt->fetch();

    if (!$password) {
        http_response_code(404);
        echo json_encode(['error' => 'Passwort nicht gefunden.']);
        exit;
    }

    header('Content-Type: application/json');
    echo json_encode($password);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Fehler beim Abrufen des Passworts.',
        'details' => $e->getMessage()
    ]);
}
