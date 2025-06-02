<?php
require_once __DIR__ . '/../config/config.php';

$pdo = getPDO();

$pdo->exec("DROP TABLE IF EXISTS contact_password");
$pdo->exec("DROP TABLE IF EXISTS passwords");
$pdo->exec("DROP TABLE IF EXISTS invoices");
$pdo->exec("DROP TABLE IF EXISTS contacts");

$pdo->exec(<<<SQL
CREATE TABLE contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    company VARCHAR(150),
    email VARCHAR(150),
    phone VARCHAR(50),
    street VARCHAR(150),
    zip_code VARCHAR(20),
    city VARCHAR(100),
    country VARCHAR(100),
    notes TEXT,
    tags VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)
SQL);

$insert = $pdo->prepare(<<<SQL
INSERT INTO contacts (
    first_name, last_name, company, email, phone, street, zip_code, city, country, notes, tags
) VALUES 
    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?),
    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
SQL);

$insert->execute([
    'Anna', 'Bergmann', 'Bergmann Consulting', 'anna@bergmann.de', '0123-456789', 'Hauptstraße 1', '12345', 'Berlin', 'Deutschland', 'Langjährige Stammkundin', 'Stammkunde, Beratung',
    'Markus', 'Schneider', 'Schneider IT', 'markus@schneider-it.de', '0987-654321', 'Musterweg 42', '54321', 'Hamburg', 'Deutschland', 'Interessiert an langfristiger Betreuung', 'potenziell, IT'
]);

$includePasswords = isset($_GET['include_passwords']) && $_GET['include_passwords'] === 'true';
if ($includePasswords) {
    $pdo->exec(<<<SQL
    CREATE TABLE passwords (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(150),
        username VARCHAR(150),
        password TEXT,
        notes TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        last_modified DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )
    SQL);

    $pdo->exec(<<<SQL
    CREATE TABLE contact_password (
        contact_id INT NOT NULL,
        password_id INT NOT NULL,
        PRIMARY KEY (contact_id, password_id),
        FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE CASCADE,
        FOREIGN KEY (password_id) REFERENCES passwords(id) ON DELETE CASCADE
    )
    SQL);

    $stmt = $pdo->prepare(<<<SQL
        INSERT INTO passwords (title, username, password, notes)
        VALUES (?, ?, ?, ?), (?, ?, ?, ?)
    SQL);

    $stmt->execute([
        'Webhosting Admin', 'anna_admin', 'geheim123', 'cPanel Zugang für Bergmann Consulting',
        'FTP Zugang', 'markus_s', 'ftp!Pass2025', 'FTP-Zugang für Schneider IT Projektserver'
    ]);

    $pdo->exec("INSERT INTO contact_password (contact_id, password_id) VALUES (1, 1), (2, 2)");
}

$pdo->exec(<<<SQL
CREATE TABLE invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rechnungsnr INT UNIQUE,
    kunden_id INT NOT NULL,
    datum DATE NOT NULL,
    beschreibung TEXT NOT NULL,
    betrag DECIMAL(10,2) NOT NULL,
    kommentar TEXT,
    status VARCHAR(20) DEFAULT 'offen',
    created DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_modified DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (kunden_id) REFERENCES contacts(id) ON DELETE CASCADE
)
SQL);

$maxNr = $pdo->query("SELECT IFNULL(MAX(rechnungsnr), 0) FROM invoices")->fetchColumn();
$rechnungsnr1 = $maxNr + 1;
$rechnungsnr2 = $rechnungsnr1 + 1;

$rechnungen = $pdo->prepare(<<<SQL
INSERT INTO invoices (rechnungsnr, kunden_id, datum, beschreibung, betrag, kommentar, status)
VALUES 
    (?, 1, '2025-05-01', 'Beratung Website-Relaunch', 950.00, 'Kickoff & Konzeptphase abgeschlossen', 'bezahlt'),
    (?, 2, '2025-05-02', 'Netzwerkanalyse & IT-Diagnose', 450.00, 'Teilzahlung offen', 'offen')
SQL);
$rechnungen->execute([$rechnungsnr1, $rechnungsnr2]);

echo "✔️ Tabellen wurden erstellt und mit Beispieldaten gefüllt.";
if ($includePasswords) {
    echo "<br>✔️ Passwörter und Verknüpfungen wurden ebenfalls angelegt.";
}
