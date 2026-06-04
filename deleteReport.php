<?php
session_start();
require_once 'connection.php';

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? null;

if ($id) {
    // Validasi kepemilikan data sebelum dieksekusi demi keamanan
    $stmt = $pdo->prepare("DELETE FROM `report` WHERE `ID` = ? AND `userID` = ?");
    $stmt->execute([$id, $_SESSION['userID']]);
}

header("Location: profile.php");
exit;