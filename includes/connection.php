<?php
/* ============================================================
   connection.php — Koneksi PDO ke MySQL
   Jika database belum ada, initDb.php dijalankan otomatis.
   ============================================================ */

$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'lostfounddb';   // dipakai konsisten di seluruh proyek

$DB_OPTIONS = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Coba langsung konek ke database target
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, $DB_OPTIONS);
} catch (PDOException $e) {
    // 1049 = Unknown database → buat dulu lewat initDb.php, lalu konek ulang
    if ((int)$e->getCode() === 1049) {
        require __DIR__ . '/initDb.php';
        $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, $DB_OPTIONS);
    } else {
        die('Koneksi database gagal: ' . htmlspecialchars($e->getMessage()));
    }
}

// Pengaman terakhir: kalau entah kenapa $pdo belum terbentuk, hentikan dengan pesan jelas
if (!isset($pdo)) {
    die('Koneksi database gagal: objek PDO tidak terbentuk. Periksa MySQL aktif dan kredensial di connection.php.');
}
