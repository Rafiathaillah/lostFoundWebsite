<?php
session_start();
require_once 'connection.php';

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? null;
// Pastikan barang tersebut memang ada dan merupakan milik user yang login
$stmt = $pdo->prepare("SELECT * FROM `report` WHERE `ID` = ? AND `userID` = ?");
$stmt->execute([$id, $_SESSION['userID']]);
$report = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$report) {
    die("Laporan tidak ditemukan atau Anda tidak memiliki akses.");
}

$categories = $pdo->query("SELECT * FROM `category`")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryID = $_POST['categoryID'];
    $reportType = $_POST['reportType'];
    $itemName = trim($_POST['itemName']);
    $description = trim($_POST['description']);
    $lastLocation = trim($_POST['lastLocation']);
    $status = $_POST['status'];

    if (!empty($categoryID) && !empty($reportType) && !empty($itemName)) {
        $stmt_update = $pdo->prepare("UPDATE `report` SET `categoryID` = ?, `reportType` = ?, `itemName` = ?, `description` = ?, `lastLocation` = ?, `status` = ? WHERE `ID` = ?");
        $stmt_update->execute([$categoryID, $reportType, $itemName, $description, $lastLocation, $status, $id]);
        header("Location: profile.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Laporan</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen py-8">
    <div class="max-w-xl mx-auto bg-white p-8 rounded-xl shadow-sm border">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Ubah Data Laporan</h2>
        <form action="" method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Jenis Laporan</label>
                <select name="reportType" class="mt-1 block w-full border border-gray-300 rounded p-2">
                    <option value="lost" <?= $report['reportType'] === 'lost' ? 'selected' : '' ?>>Kehilangan (Lost)</option>
                    <option value="found" <?= $report['reportType'] === 'found' ? 'selected' : '' ?>>Menemukan (Found)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Kategori</label>
                <select name="categoryID" class="mt-1 block w-full border border-gray-300 rounded p-2">
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['ID'] ?>" <?= $report['categoryID'] == $cat['ID'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Nama Barang</label>
                <input type="text" name="itemName" value="<?= htmlspecialchars($report['itemName']) ?>" class="mt-1 block w-full border border-gray-300 rounded p-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
                <textarea name="description" rows="3" class="mt-1 block w-full border border-gray-300 rounded p-2"><?= htmlspecialchars($report['description']) ?></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Lokasi</label>
                <input type="text" name="lastLocation" value="<?= htmlspecialchars($report['lastLocation']) ?>" class="mt-1 block w-full border border-gray-300 rounded p-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Status Laporan</label>
                <select name="status" class="mt-1 block w-full border border-gray-300 rounded p-2">
                    <option value="pending" <?= $report['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="resolved" <?= $report['status'] === 'resolved' ? 'selected' : '' ?>>Selesai (Resolved)</option>
                </select>
            </div>
            <div class="flex space-x-3 pt-4">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded font-semibold hover:bg-blue-700 transition">Simpan Perubahan</button>
                <a href="profile.php" class="bg-gray-200 text-gray-700 px-4 py-2 rounded font-semibold hover:bg-gray-300 transition">Batal</a>
            </div>
        </form>
    </div>
</body>
</html>