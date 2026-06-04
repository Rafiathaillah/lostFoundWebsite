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

    // 1. Set nama file default jika user tidak mengunggah foto
    $filenameToSave = 'default.jpg';

    // 2. LOGIKA UPLOAD FOTO
    // Cek apakah ada kiriman file bernama 'itemPhoto' dan tidak ada eror sistem
    if (isset($_FILES['itemPhoto']) && $_FILES['FILES_ERROR'] === 0 || $_FILES['itemPhoto']['error'] === UPLOAD_ERR_OK) {

        $fileTmpPath = $_FILES['itemPhoto']['tmp_name']; // Jalur transit sementara
        $fileName = $_FILES['itemPhoto']['name'];         // Nama asli file (misal: "dompet.jpg")

        // Ambil ekstensi file (mengubah ke lowercase agar seragam, misal: "jpg")
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Beri nama acak baru + eksetensinya (misal: "65b2f1a3c4d2e.jpg")
        // Ini wajib agar jika ada 2 user mengunggah file bernama "foto.jpg", data tidak saling menimpa
        $newFileName = uniqid() . '.' . $fileExtension;

        // Tentukan folder tujuan penyimpanan
        $uploadFileDir = './upload/';

        // PERINTAH CERDAS: Buat folder 'uploads' otomatis via kode jika kamu lupa membuatnya manual
        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0775, true);
        }

        // Gabungkan folder tujuan dengan nama file unik baru
        $dest_path = $uploadFileDir . $newFileName;

        // Pindahkan file dari folder transit ke folder uploads/ proyek kita
        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            // Jika perpindahan fisik berhasil, siapkan nama file ini untuk masuk ke database
            $filenameToSave = $newFileName;
        }
    }

    if (!empty($categoryID) && !empty($reportType) && !empty($itemName)) {
        $stmt = $pdo->prepare("INSERT INTO `report` (`categoryID`, `userID`, `reportType`, `itemName`, `description`, `lastLocation`, `itemPhoto`, `status`) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
        // Masukkan variabel $filenameToSave ke kolom itemPhoto
        $stmt->execute([$categoryID, $_SESSION['userID'], $reportType, $itemName, $description, $lastLocation, $filenameToSave]);

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
        <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
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
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Foto Barang Bukti</label>
                <input type="file"
                    name="itemPhoto"
                    id="itemPhoto"
                    accept="image/*"
                    class="block w-full text-sm text-gray-500 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none file:mr-4 file:py-2 file:px-4 file:rounded-l-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition"
                    required>
                <p class="mt-1 text-xs text-gray-500">Format yang didukung: JPG, JPEG, atau PNG.</p>
            </div>
            <div class="flex space-x-3 pt-4">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded font-semibold hover:bg-blue-700 transition">Kirim Postingan</button>
                <a href="index.php" class="bg-gray-200 text-gray-700 px-4 py-2 rounded font-semibold hover:bg-gray-300 transition">Batal</a>
            </div>
        </form>
    </div>
</body>

</html>