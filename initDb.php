<?php
$host = 'localhost';
$username = 'root'; // Sesuaikan dengan device masing-masing
$password = '';     // Sesuaikan dengan device masing-masing
$db_name = 'lostFoundDb';

try {
    // 1. Koneksi ke MySQL server
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. Hancurkan database lama jika ada (Fresh Drop)
    $pdo->exec("DROP DATABASE IF EXISTS `$db_name`;");
    echo "Database lama berhasil dihapus.\n";

    // 3. Buat ulang database baru dari nol
    $pdo->exec("CREATE DATABASE `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    echo "Database '$db_name' berhasil dibuat baru.\n";

    // 4. Gunakan database baru
    $pdo->exec("USE `$db_name`;");

    // ==========================================
    // PEMBUATAN TABEL (Berdasarkan Mapping Final)
    // ==========================================

    // Tabel User
    $pdo->exec("CREATE TABLE `user` (
        `ID` INT AUTO_INCREMENT PRIMARY KEY,
        `nim` VARCHAR(20) NOT NULL UNIQUE,
        `fullName` VARCHAR(100) NOT NULL,
        `password` VARCHAR(255) NOT NULL
    );");
    echo "Tabel user berhasil dibuat.\n";

    // Tabel Category
    $pdo->exec("CREATE TABLE `category` (
        `ID` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(50) NOT NULL
    );");
    echo "Tabel category berhasil dibuat.\n";

    // Tabel Report
    $pdo->exec("CREATE TABLE `report` (
        `ID` INT AUTO_INCREMENT PRIMARY KEY,
        `categoryID` INT NOT NULL,
        `userID` INT NOT NULL,
        `reportType` ENUM('lost', 'found') NOT NULL,
        `itemName` VARCHAR(100) NOT NULL,
        `description` TEXT,
        `lastLocation` VARCHAR(255),
        `itemPhoto` VARCHAR(255),
        `status` VARCHAR(50) DEFAULT 'pending',
        FOREIGN KEY (`categoryID`) REFERENCES `category`(`ID`) ON DELETE CASCADE,
        FOREIGN KEY (`userID`) REFERENCES `user`(`ID`) ON DELETE CASCADE
    );");
    echo "Tabel report berhasil dibuat.\n";

    // Tabel Claim
    $pdo->exec("CREATE TABLE `claim` (
        `ID` INT AUTO_INCREMENT PRIMARY KEY,
        `reportID` INT NOT NULL,
        `userID` INT NOT NULL,
        `claimStatus` VARCHAR(50) DEFAULT 'pending',
        `bukti` TEXT,
        FOREIGN KEY (`reportID`) REFERENCES `report`(`ID`) ON DELETE CASCADE,
        FOREIGN KEY (`userID`) REFERENCES `user`(`ID`) ON DELETE CASCADE
    );");
    echo "Tabel claim berhasil dibuat.\n";


    // ==========================================
    // DATA SEEDERS (Data Awal untuk Testing)
    // ==========================================
    echo "\nMengisi data awal untuk testing...\n";

    // Seed Data User (Password menggunakan password_hash demi standar keamanan)
    $hashed_password = password_hash('mahasiswa123', PASSWORD_BCRYPT);
    $stmt_user = $pdo->prepare("INSERT INTO `user` (`nim`, `fullName`, `password`) VALUES (?, ?, ?)");
    $stmt_user->execute(['1076012510020', 'Rafi', $hashed_password]);
    $stmt_user->execute(['1076012510019', 'Dava', $hashed_password]);
    echo "2 Akun mahasiswa berhasil dimasukkan ke dalam database.\n";

    // Seed Data Kategori
    $stmt_cat = $pdo->prepare("INSERT INTO `category` (`name`) VALUES (?)");
    $categories = ['Elektronik', 'Dokumen & Barang berharga', 'Kunci', 'Pakaian', 'Alat Tulis'];
    foreach ($categories as $cat) {
        $stmt_cat->execute([$cat]);
    }
    echo "Kategori default (" . implode(', ', $categories) . ") siap digunakan.\n";

    echo "\n====== SISTEM SUDAH SIAP DIGUNAKAMN ======\n";

} catch (PDOException $e) {
    die("Gagal menjalankan database: " . $e->getMessage());
}
?>