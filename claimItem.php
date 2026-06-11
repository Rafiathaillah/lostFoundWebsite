<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !checkCsrf()) { header('Location: index.php'); exit; }

$reportID = $_POST['reportID'] ?? '';
$bukti    = trim($_POST['bukti'] ?? '');
if (!ctype_digit((string)$reportID) || $bukti === '') { header('Location: index.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM report WHERE ID = ?");
$stmt->execute([$reportID]);
$report = $stmt->fetch();

if (!$report
    || $report['reportType'] !== 'found'
    || (int)$report['userID'] === currentUserId()
    || $report['status'] === 'resolved') {
    header('Location: report_detail.php?id=' . (int)$reportID);
    exit;
}

$dup = $pdo->prepare("SELECT COUNT(*) FROM claim WHERE reportID = ? AND userID = ? AND claimStatus IN ('pending','verified')");
$dup->execute([$reportID, currentUserId()]);
if ($dup->fetchColumn() > 0) {
    header('Location: report_detail.php?id=' . (int)$reportID);
    exit;
}

$pdo->beginTransaction();
try {
    $ins = $pdo->prepare("INSERT INTO claim (reportID, userID, claimStatus, bukti) VALUES (?,?, 'pending', ?)");
    $ins->execute([$reportID, currentUserId(), $bukti]);

    if ($report['status'] === 'pending') {
        $pdo->prepare("UPDATE report SET status = 'process' WHERE ID = ?")->execute([$reportID]);
    }
    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
}

header('Location: report_detail.php?id=' . (int)$reportID);
exit;
