<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !checkCsrf()) { header('Location: profile.php'); exit; }

$claimID = $_POST['claimID'] ?? '';
$action  = $_POST['action'] ?? '';
if (!ctype_digit((string)$claimID) || !in_array($action, ['accept','reject'], true)) {
    header('Location: profile.php#incoming'); exit;
}

$stmt = $pdo->prepare("SELECT cl.*, r.userID AS ownerID, r.ID AS reportID
    FROM claim cl JOIN report r ON cl.reportID = r.ID WHERE cl.ID = ?");
$stmt->execute([$claimID]);
$claim = $stmt->fetch();

if (!$claim || (int)$claim['ownerID'] !== currentUserId() || $claim['claimStatus'] !== 'pending') {
    header('Location: profile.php#incoming'); exit;
}

$reportID = (int)$claim['reportID'];

$pdo->beginTransaction();
try {
    if ($action === 'accept') {
        $pdo->prepare("UPDATE claim SET claimStatus = 'verified' WHERE ID = ?")->execute([$claimID]);
        $pdo->prepare("UPDATE claim SET claimStatus = 'rejected' WHERE reportID = ? AND ID <> ? AND claimStatus = 'pending'")
            ->execute([$reportID, $claimID]);
        $pdo->prepare("UPDATE report SET status = 'resolved' WHERE ID = ?")->execute([$reportID]);
    } else {
        $pdo->prepare("UPDATE claim SET claimStatus = 'rejected' WHERE ID = ?")->execute([$claimID]);
        $rest = $pdo->prepare("SELECT COUNT(*) FROM claim WHERE reportID = ? AND claimStatus = 'pending'");
        $rest->execute([$reportID]);
        if ((int)$rest->fetchColumn() === 0) {
            $pdo->prepare("UPDATE report SET status = 'pending' WHERE ID = ? AND status = 'process'")->execute([$reportID]);
        }
    }
    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
}

header('Location: profile.php#incoming');
exit;
