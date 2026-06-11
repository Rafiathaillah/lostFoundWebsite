<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php'); exit; }
if (!checkCsrf()) { header('Location: index.php?error=' . urlencode('Sesi tidak valid, coba lagi.')); exit; }

$categoryID   = $_POST['categoryID'] ?? '';
$reportType   = $_POST['reportType'] ?? '';
$itemName     = trim($_POST['itemName'] ?? '');
$description  = trim($_POST['description'] ?? '');
$lastLocation = trim($_POST['lastLocation'] ?? '');

if (!ctype_digit((string)$categoryID) || !in_array($reportType, ['lost','found'], true) || $itemName === '') {
    header('Location: index.php?error=' . urlencode('Lengkapi jenis, kategori, dan nama barang.'));
    exit;
}

/* ---------- Upload foto (opsional, divalidasi) ---------- */
$filename = 'default.jpg';
if (isset($_FILES['itemPhoto']) && $_FILES['itemPhoto']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['itemPhoto'];

    if ($file['size'] > 4 * 1024 * 1024) {
        header('Location: index.php?error=' . urlencode('Ukuran foto maksimal 4 MB.')); exit;
    }

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        header('Location: index.php?error=' . urlencode('Format foto harus JPG, PNG, atau WEBP.')); exit;
    }

    $dir = __DIR__ . '/uploads/';
    if (!is_dir($dir)) mkdir($dir, 0775, true);

    $filename = bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], $dir . $filename)) {
        $filename = 'default.jpg';
    }
}

$stmt = $pdo->prepare("INSERT INTO report
    (categoryID, userID, reportType, itemName, description, lastLocation, itemPhoto, status)
    VALUES (?,?,?,?,?,?,?, 'pending')");
$stmt->execute([$categoryID, currentUserId(), $reportType, $itemName, $description, $lastLocation, $filename]);

header('Location: index.php');
exit;
