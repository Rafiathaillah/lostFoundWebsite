<?php
session_start();
require_once 'connection.php';

// Proteksi halaman
if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit;
}

// Ambil data kiriman report dan join dengan kategori serta user penyetor
$query = "SELECT r.*, c.name AS categoryName, u.fullName FROM `report` r 
          JOIN `category` c ON r.categoryID = c.ID 
          JOIN `user` u ON r.userID = u.ID 
          ORDER BY r.ID DESC";
$stmt = $pdo->query($query);
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Lost & Found Collage</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen pb-12">
    <nav class="bg-white shadow-sm border-b sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center">
            <h1 class="text-xl font-bold text-blue-600 tracking-wide">Lost & Found</h1>
            <div class="flex items-center space-x-6">
                <span class="text-gray-600 text-sm font-medium">Halo, <strong><?= htmlspecialchars($_SESSION['fullName']) ?></strong></span>
                <a href="profile.php" class="text-gray-700 hover:text-blue-600 font-medium transition">Profil Saya</a>
                <a href="logout.php" class="text-red-600 hover:text-red-800 text-sm font-medium transition">Keluar</a>
            </div>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-4 mt-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Daftar Laporan Barang</h2>
                <p class="text-gray-500 text-sm">Temukan atau laporkan penemuan barang di lingkungan kampus</p>
            </div>
            <a href="createReport.php" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2.5 rounded-lg shadow-sm flex items-center space-x-2 transition">
                <span>Post Barang</span>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php if (count($reports) > 0): ?>
                <?php foreach ($reports as $item): ?>
                    <div class="bg-white border rounded-xl overflow-hidden shadow-sm hover:shadow-md transition flex flex-col justify-between">
                        <div class="p-5">
                            <div class="flex justify-between items-center mb-3">
                                <span class="px-2.5 py-1 text-xs font-bold uppercase rounded-full <?= $item['reportType'] === 'lost' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' ?>">
                                    <?= $item['reportType'] === 'lost' ? 'Kehilangan' : 'Ditemukan' ?>
                                </span>
                                <span class="text-xs text-gray-400 font-medium"><?= htmlspecialchars($item['categoryName']) ?></span>
                            </div>

                            <h3 class="text-lg font-bold text-gray-800 mb-1"><?= htmlspecialchars($item['itemName']) ?></h3>
                            <p class="text-xs text-gray-400 mb-3">Dilaporkan oleh: <span class="text-gray-600 font-medium"><?= htmlspecialchars($item['fullName']) ?></span></p>
                            <p class="text-sm text-gray-600 line-clamp-3 mb-4"><?= htmlspecialchars($item['description']) ?></p>
                            
                            <div class="text-xs text-gray-500 space-y-1 bg-gray-50 p-2.5 rounded-lg">
                                <div><strong>Lokasi terakhir:</strong> <?= htmlspecialchars($item['lastLocation']) ?></div>
                                <div>Status: <span class="font-semibold text-amber-600"><?= htmlspecialchars($item['status']) ?></span></div>
                            </div>
                        </div>

                        <div class="p-4 bg-gray-50 border-t flex justify-end">
                            <?php if ($item['reportType'] === 'found' && $item['userID'] != $_SESSION['userID']): ?>
                                <a href="claim_item.php?id=<?= $item['ID'] ?>" class="text-xs bg-blue-100 hover:bg-blue-200 text-blue-700 font-bold px-3 py-1.5 rounded transition">Klaim Barang</a>
                            <?php else: ?>
                                <span class="text-xs text-gray-400 italic">Informasi Kampus</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-3 text-center py-12 text-gray-500">Belum ada barang yang dilaporkan.</div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>