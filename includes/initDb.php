<?php
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'lostfounddb';

$runningStandalone = (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'initDb.php');

try {
    $pdo = new PDO("mysql:host=$DB_HOST;charset=utf8mb4", $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $pdo->exec("DROP DATABASE IF EXISTS `$DB_NAME`");
    $pdo->exec("CREATE DATABASE `$DB_NAME` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$DB_NAME`");

    $pdo->exec("CREATE TABLE `user` (
        `ID` INT AUTO_INCREMENT PRIMARY KEY,
        `nim` VARCHAR(20) NOT NULL UNIQUE,
        `fullName` VARCHAR(100) NOT NULL,
        `password` VARCHAR(255) NOT NULL,
        `createdAt` DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE `category` (
        `ID` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(50) NOT NULL,
        `icon` VARCHAR(30) DEFAULT 'box'
    )");

    $pdo->exec("CREATE TABLE `report` (
        `ID` INT AUTO_INCREMENT PRIMARY KEY,
        `categoryID` INT NOT NULL,
        `userID` INT NOT NULL,
        `reportType` ENUM('lost','found') NOT NULL,
        `itemName` VARCHAR(100) NOT NULL,
        `description` TEXT,
        `lastLocation` VARCHAR(255),
        `itemPhoto` VARCHAR(255) DEFAULT 'default.jpg',
        `status` ENUM('pending','process','resolved') DEFAULT 'pending',
        `createdAt` DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`categoryID`) REFERENCES `category`(`ID`) ON DELETE CASCADE,
        FOREIGN KEY (`userID`)     REFERENCES `user`(`ID`)     ON DELETE CASCADE
    )");

    $pdo->exec("CREATE TABLE `claim` (
        `ID` INT AUTO_INCREMENT PRIMARY KEY,
        `reportID` INT NOT NULL,
        `userID` INT NOT NULL,
        `claimStatus` ENUM('pending','verified','rejected') DEFAULT 'pending',
        `bukti` TEXT,
        `createdAt` DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`reportID`) REFERENCES `report`(`ID`) ON DELETE CASCADE,
        FOREIGN KEY (`userID`)   REFERENCES `user`(`ID`)   ON DELETE CASCADE
    )");

    $pw = password_hash('mahasiswa123', PASSWORD_BCRYPT);
    $u = $pdo->prepare("INSERT INTO `user` (`nim`,`fullName`,`password`) VALUES (?,?,?)");
    $u->execute(['1076012510020', 'Rafi Pratama', $pw]);
    $u->execute(['1076012510019', 'Dava Saputra', $pw]);
    $u->execute(['1076012510018', 'Nadia Lestari', $pw]);

    $cats = [
        ['Elektronik', 'phone'],
        ['Dokumen & Kartu', 'card'],
        ['Kunci', 'key'],
        ['Dompet & Tas', 'wallet'],
        ['Pakaian', 'shirt'],
        ['Botol & Tumbler', 'bottle'],
        ['Alat Tulis', 'pen'],
        ['Lainnya', 'box'],
    ];
    $c = $pdo->prepare("INSERT INTO `category` (`name`,`icon`) VALUES (?,?)");
    foreach ($cats as $cat) $c->execute($cat);

    $r = $pdo->prepare("INSERT INTO `report`
        (`categoryID`,`userID`,`reportType`,`itemName`,`description`,`lastLocation`,`itemPhoto`,`status`)
        VALUES (?,?,?,?,?,?,?,?)");
    $r->execute([1, 1, 'found', 'iPhone 13 warna biru', 'Ditemukan dalam keadaan layar terkunci, wallpaper kucing.', 'Lapangan', 'iphone13.jpg', 'pending']);
    $r->execute([4, 2, 'lost',  'Dompet kulit coklat',  'Berisi KTP dan Kartu ATM. Coklat polos. Ditemukan di kantin.', 'Kantin', 'wallet.jpg', 'pending']);
    $r->execute([3, 1, 'found', 'Kunci motor',    'Tanpa gantungan kunci, kunci manual bukan remote', 'Parkiran', 'key.jpg', 'pending']);
    $r->execute([6, 3, 'found', 'Tumbler hijau',         'Tumbler stainless, ada sticker kaktus di tengah', 'Perpustakaan', 'tumbler.jpg', 'pending']);

    echo $runningStandalone
        ? "Database '$DB_NAME' berhasil dibuat ulang beserta data awal."
        : '';
} catch (PDOException $e) {
    die('Gagal inisialisasi database: ' . htmlspecialchars($e->getMessage()));
}
