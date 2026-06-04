<?php
session_start();
require_once 'connection.php';

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit;
}

// Ambil Kategori untuk drop-down selection
$categories = $pdo->query("SELECT * FROM `category`")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryID = $_POST['categoryID'];
    $reportType = $_POST['reportType'];
    $itemName = trim($_POST['itemName']);
    $description = trim($_POST['description']);
    $lastLocation = trim($_POST['lastLocation']);
    
    // Sederhanakan input gambar text/url untuk sementara prototype
    $itemPhoto = trim($_POST['itemPhoto'] ?? 'default.jpg'); 

    if (!empty($categoryID) && !empty($reportType) && !empty($itemName)) {
        $stmt = $pdo->prepare("INSERT INTO `report` (`categoryID`, `userID`, `reportType`, `itemName`, `description`, `lastLocation`, `itemPhoto`, `status`) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([$categoryID, $_SESSION['userID'], $reportType, $itemName, $description, $lastLocation, $itemPhoto]);
        header("Location: index.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Post Laporan - Lost & Found</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen py-8">
    <div class="max-w-xl mx-auto bg-white p-8 rounded-xl shadow-sm border">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Buat Laporan Baru</h2>
        <form action="" method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Jenis Laporan</label>
                <select name="reportType" class="mt-1 block w-full border border-gray-300 rounded p-2" required>
                    <option value="lost">Kehilangan (Lost)</option>
                    <option value="found">Menemukan (Found)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Kategori Barang</label>
                <select name="categoryID" class="mt-1 block w-full border border-gray-300 rounded p-2" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['ID'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Nama Barang</label>
                <input type="text" name="itemName" class="mt-1 block w-full border border-gray-300 rounded p-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Deskripsi Ciri Fisik</label>
                <textarea name="description" rows="3" class="mt-1 block w-full border border-gray-300 rounded p-2"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Lokasi Terakhir / Ditemukan</label>
                <input type="text" name="lastLocation" class="mt-1 block w-full border border-gray-300 rounded p-2">
            </div>
            <div class="flex space-x-3 pt-4">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded font-semibold hover:bg-blue-700 transition">Kirim Postingan</button>
                <a href="index.php" class="bg-gray-200 text-gray-700 px-4 py-2 rounded font-semibold hover:bg-gray-300 transition">Batal</a>
            </div>
        </form>
    </div>
</body>
</html>