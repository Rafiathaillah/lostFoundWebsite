<?php
/* header.php — Layout atas untuk halaman setelah login.
   Set $pageTitle dan $activeNav sebelum require file ini. */
require_once __DIR__ . '/functions.php';
requireLogin();

$activeNav   = $activeNav   ?? '';
$pageTitle   = $pageTitle   ?? 'Campus Lost & Found';
$notifCount  = incomingClaimCount($pdo, currentUserId());
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<nav class="nav">
    <div class="container nav__inner">
        <a href="index.php" class="brand">
            <span class="logo"><?= icon('compass') ?></span>
            Campus Lost &amp; Found
        </a>
        <div class="nav__links">
            <a href="index.php" class="nav__link <?= $activeNav === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
            <a href="profile.php" class="nav__link <?= $activeNav === 'profile' ? 'active' : '' ?>">
                Profil Saya
                <?php if ($notifCount > 0): ?><span class="nav__notif"><?= $notifCount ?></span><?php endif; ?>
            </a>
            <div class="nav__user">
                <span class="hello">Halo, <strong style="color:var(--green-900)"><?= e(currentUserName()) ?></strong></span>
                <span class="avatar"><?= e(initials(currentUserName())) ?></span>
            </div>
            <a href="logout.php" class="nav__link" title="Keluar" style="padding:8px 10px;"><?= icon('logout', 'w-5') ?></a>
        </div>
    </div>
</nav>
<main class="container">
