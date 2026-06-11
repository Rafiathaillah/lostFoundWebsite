<?php
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'lostfounddb';   

$DB_OPTIONS = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, $DB_OPTIONS);
} catch (PDOException $e) {
    if ((int)$e->getCode() === 1049) {
        require __DIR__ . '/initDb.php';
        $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, $DB_OPTIONS);
    } else {
        die('Koneksi database gagal: ' . htmlspecialchars($e->getMessage()));
    }
}

if (!isset($pdo)) {
    die('Koneksi database gagal: objek PDO tidak terbentuk. Periksa MySQL aktif dan kredensial di connection.php.');
}
