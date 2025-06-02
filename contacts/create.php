<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/config.php';

$pdo = getPDO();

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['first_name'], $input['last_name'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Vor- und Nachname sind erforderlich.']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO contacts (
            first_name, last_name, company, email, phone, street, zip_code, city, country, notes, tags
        ) VALUES (
            :first_name, :last_name, :company, :email, :phone, :street, :zip_code, :city, :country, :notes, :tags
        )
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
    ]);

    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Datenbankfehler beim Einfügen.', 'details' => $e->getMessage()]);
}
