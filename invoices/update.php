<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/config.php';

$pdo = getPDO();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$method = $_SERVER['REQUEST_METHOD'];
$override = $_GET['_method'] ?? null;

if (!($method === 'PUT' || ($method === 'POST' && strtoupper($override) === 'PUT'))) {
    http_response_code(405);
    echo json_encode(['error' => 'Methode nicht erlaubt.']);
    exit;
}

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Ungültige Rechnungs-ID.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['datum'], $data['beschreibung'], $data['betrag'], $data['status'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Fehlende Pflichtfelder.']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE invoices SET
            datum = :datum,
            beschreibung = :beschreibung,
            betrag = :betrag,
            kommentar = :kommentar,
            status = :status,
            last_modified = NOW()
        WHERE id = :id
    ");

    $stmt->execute([
        ':datum'        => $data['datum'],
        ':beschreibung' => $data['beschreibung'],
        ':betrag'       => $data['betrag'],
        ':kommentar'    => $data['kommentar'] ?? null,
        ':status'       => $data['status'],
        ':id'           => $id
    ]);

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Fehler beim Aktualisieren der Rechnung.',
        'details' => $e->getMessage()
    ]);
}
