<?php
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) { header('Location: index.php'); exit; }

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nim      = trim($_POST['nim'] ?? '');
    $fullName = trim($_POST['fullName'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($nim === '' || $fullName === '' || $password === '') {
        $error = 'Semua kolom wajib diisi.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif (!ctype_digit($nim) || strlen($nim) < 8) {
        $error = 'NIM harus angka dan minimal 8 digit';
    } else {
        try {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO `user` (`nim`,`fullName`,`password`) VALUES (?,?,?)");
            $stmt->execute([$nim, $fullName, $hash]);
            $success = 'Registrasi berhasil! Silakan login.';
        } catch (PDOException $e) {
            $error = ($e->getCode() === '23000')
                ? 'NIM tersebut sudah terdaftar.'
                : 'Terjadi kesalahan sistem.';
        }
    }
}

$pageIcons = ['wallet','key','bottle','card','phone','tag'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar — Campus Lost &amp; Found</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="auth">
    <aside class="auth__brand">
        <span class="ring r1"></span>
        <span class="ring r2"></span>
        <div class="tiles">
            <?php foreach ($pageIcons as $ic): ?>
                <span class="tile"><?= icon($ic) ?></span>
            <?php endforeach; ?>
        </div>
        <h1>Campus<br>Lost &amp; Found</h1>
        <p>Daftarkan akunmu sekali, lalu laporkan atau klaim barang kapan saja.</p>
    </aside>

    <section class="auth__form">
        <div class="auth__card">
            <h2>Buat akun</h2>
            <p class="auth__sub">Gunakan data mahasiswa yang valid</p>

            <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?> <a href="login.php" style="text-decoration:underline">Login</a></div><?php endif; ?>

            <form method="POST" action="">
                <?= csrfField() ?>
                <div class="field">
                    <label class="label">NIM (Student ID)</label>
                    <input class="input" type="text" name="nim" placeholder="cth. 2021001234" value="<?= e($_POST['nim'] ?? '') ?>" required>
                </div>
                <div class="field">
                    <label class="label">Nama Lengkap</label>
                    <input class="input" type="text" name="fullName" placeholder="cth. Rafi Pratama" value="<?= e($_POST['fullName'] ?? '') ?>" required>
                </div>
                <div class="field">
                    <label class="label">Password</label>
                    <input class="input" type="password" name="password" placeholder="Minimal 6 karakter" required>
                </div>
                <button class="btn btn-primary btn-block" type="submit">Daftar</button>
            </form>

            <p class="auth__alt">Sudah punya akun? <a href="login.php">Login di sini</a></p>
        </div>
    </section>
</div>
</body>
</html>
