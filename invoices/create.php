<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/config.php';

$pdo = getPDO();
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['kunden_id'], $data['datum'], $data['beschreibung'], $data['betrag'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Fehlende Pflichtfelder.']);
    exit;
}

$max = $pdo->query("SELECT IFNULL(MAX(rechnungsnr), 0) FROM invoices")->fetchColumn();
$nextNr = $max + 1;

try {
    $stmt = $pdo->prepare("
        INSERT INTO invoices (
            rechnungsnr, kunden_id, datum, beschreibung, betrag, kommentar, status
        ) VALUES (
            :rechnungsnr, :kunden_id, :datum, :beschreibung, :betrag, :kommentar, :status
        )
    ");

    $stmt->execute([
        ':rechnungsnr'   => $nextNr,
        ':kunden_id'     => $data['kunden_id'],
        ':datum'         => $data['datum'],
        ':beschreibung'  => $data['beschreibung'],
        ':betrag'        => $data['betrag'],
        ':kommentar'     => $data['kommentar'] ?? null,
        ':status'        => $data['status'] ?? 'offen'
    ]);

    echo json_encode(['success' => true, 'rechnungsnr' => $nextNr]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Fehler beim Erstellen der Rechnung.',
        'details' => $e->getMessage()
    ]);
}
