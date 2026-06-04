<?php
// koneksi.php
$host = 'localhost';
$username = 'root';
$password = '';
$db_name = 'lostfounddb';

try {
    // Coba langsung konek ke database target
    $pdo = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Jika eror karena database belum ada di device tersebut, jalankan init_db.php otomatis
    if ($e->getCode() == 1049) { 
        require_once 'initDb.php';
        // Konek ulang setelah db berhasil dibuat oleh init_db
        $pdo = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    } else {
        die("Koneksi gagal: " . $e->getMessage());
    }
}