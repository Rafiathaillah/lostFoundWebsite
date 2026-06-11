<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !checkCsrf()) { header('Location: profile.php'); exit; }

$id = $_POST['id'] ?? '';
if (ctype_digit((string)$id)) {
    $f = $pdo->prepare("SELECT itemPhoto FROM report WHERE ID = ? AND userID = ?");
    $f->execute([$id, currentUserId()]);
    $photo = $f->fetchColumn();
    if ($photo && $photo !== 'default.jpg') {
        $path = __DIR__ . '/uploads/' . basename($photo);
        if (is_file($path)) @unlink($path);
    }
    $pdo->prepare("DELETE FROM report WHERE ID = ? AND userID = ?")->execute([$id, currentUserId()]);
}

header('Location: profile.php');
exit;
