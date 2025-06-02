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
    echo json_encode(['error' => 'Ungültige ID.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['first_name'], $input['last_name'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Vor- und Nachname sind erforderlich.']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE contacts SET
            first_name = :first_name,
            last_name = :last_name,
            company = :company,
            email = :email,
            phone = :phone,
            street = :street,
            zip_code = :zip_code,
            city = :city,
            country = :country,
            notes = :notes,
            tags = :tags
        WHERE id = :id
    ");

    $stmt->execute([
        ':first_name' => $input['first_name'],
        ':last_name'  => $input['last_name'],
        ':company'    => $input['company'] ?? null,
        ':email'      => $input['email'] ?? null,
        ':phone'      => $input['phone'] ?? null,
        ':street'     => $input['street'] ?? null,
        ':zip_code'   => $input['zip_code'] ?? null,
        ':city'       => $input['city'] ?? null,
        ':country'    => $input['country'] ?? null,
        ':notes'      => $input['notes'] ?? null,
        ':tags'       => $input['tags'] ?? null,
        ':id'         => $id
    ]);

    if ($stmt->rowCount() === 0) {
        echo json_encode(['warning' => 'Keine Änderungen vorgenommen oder Kontakt nicht gefunden.']);
    } else {
        echo json_encode(['success' => true]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Fehler beim Aktualisieren des Kontakts.', 'details' => $e->getMessage()]);
}
