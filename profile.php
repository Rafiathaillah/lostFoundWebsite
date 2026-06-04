<?php
session_start();
require_once 'connection.php';

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit;
}

$userID = $_SESSION['userID'];

// 1. Ambil data 'My Post'
$stmt_posts = $pdo->prepare("SELECT r.*, c.name AS categoryName FROM `report` r JOIN `category` c ON r.categoryID = c.ID WHERE r.userID = ? ORDER BY r.ID DESC");
$stmt_posts->execute([$userID]);
$myPosts = $stmt_posts->fetchAll(PDO::FETCH_ASSOC);

// 2. Ambil data 'My Claim'
$stmt_claims = $pdo->prepare("SELECT c.*, r.itemName, r.reportType FROM `claim` c JOIN `report` r ON c.reportID = r.ID WHERE c.userID = ? ORDER BY c.ID DESC");
$stmt_claims->execute([$userID]);
$myClaims = $stmt_claims->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profil Saya - Lost & Found</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <nav class="bg-white shadow-sm border-b p-4">
        <div class="max-w-6xl mx-auto flex justify-between items-center">
            <a href="index.php" class="text-blue-600 font-bold">← Kembali ke Dashboard</a>
            <h1 class="text-lg font-semibold text-gray-700">Manajemen Akun</h1>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-4 mt-8 grid grid-cols-1 lg:grid-cols-2 gap-8">
        <section class="bg-white p-6 rounded-xl border shadow-sm">
            <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">📋 Postingan Saya (My Post)</h2>
            <div class="space-y-4">
                <?php if(count($myPosts) > 0): ?>
                    <?php foreach($myPosts as $post): ?>
                        <div class="border p-4 rounded-lg flex justify-between items-center bg-gray-50">
                            <div>
                                <h4 class="font-bold text-gray-800"><?= htmlspecialchars($post['itemName']) ?></h4>
                                <span class="text-xs font-semibold px-2 py-0.5 rounded <?= $post['reportType'] === 'lost' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' ?>">
                                    <?= $post['reportType'] ?>
                                </span>
                                <p class="text-xs text-gray-500 mt-1">Status: <?= $post['status'] ?></p>
                            </div>
                            <div class="flex space-x-2">
                                <a href="editReport.php?id=<?= $post['ID'] ?>" class="bg-amber-500 hover:bg-amber-600 text-white text-xs px-3 py-1.5 rounded font-medium transition">Edit</a>
                                <a href="deleteReport.php?id=<?= $post['ID'] ?>" onclick="return confirm('Hapus postingan ini?')" class="bg-red-600 hover:bg-red-700 text-white text-xs px-3 py-1.5 rounded font-medium transition">Hapus</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-gray-500 text-sm italic">Kamu belum pernah membuat laporan.</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="bg-white p-6 rounded-xl border shadow-sm">
            <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">🔑 Klaim Saya (My Claim)</h2>
            <div class="space-y-4">
                <?php if(count($myClaims) > 0): ?>
                    <?php foreach($myClaims as $claim): ?>
                        <div class="border p-4 rounded-lg bg-gray-50">
                            <div class="flex justify-between items-center">
                                <h4 class="font-bold text-gray-800"><?= htmlspecialchars($claim['itemName']) ?></h4>
                                <span class="text-xs px-2.5 py-1 rounded-full font-bold bg-blue-100 text-blue-700">
                                    <?= htmlspecialchars($claim['claimStatus']) ?>
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Bukti kepemilikan: <span class="text-gray-700 font-medium"><?= htmlspecialchars($claim['bukti']) ?></span></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-gray-500 text-sm italic">Kamu belum mengajukan klaim barang apa pun.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>
</body>
</html>