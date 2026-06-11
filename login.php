<?php
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) { header('Location: index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nim = trim($_POST['nim'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($nim === '' || $password === '') {
        $error = 'NIM dan password wajib diisi.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM `user` WHERE `nim` = ?");
        $stmt->execute([$nim]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['userID']   = (int)$user['ID'];
            $_SESSION['fullName'] = $user['fullName'];
            header('Location: index.php');
            exit;
        }
        $error = 'NIM atau password salah.';
    }
}

$pageIcons = ['wallet','key','bottle','card','phone','tag'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Campus Lost &amp; Found</title>
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
        <p>Membantu mahasiswa menemukan kembali barang yang hilang di lingkungan kampus.</p>
    </aside>

    <section class="auth__form">
        <div class="auth__card">
            <h2>Selamat Datang!</h2>
            <p class="auth__sub">Masuk dengan menggunakan NIM mu</p>

            <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

            <form method="POST" action="">
                <?= csrfField() ?>
                <div class="field">
                    <label class="label">NIM (Student ID)</label>
                    <input class="input" type="text" name="nim" placeholder="cth. 2021001234" value="<?= e($_POST['nim'] ?? '') ?>" required autofocus>
                </div>
                <div class="field">
                    <label class="label">Password</label>
                    <div class="input-group">
                        <input class="input" type="password" name="password" id="pw" placeholder="Masukkan password" required>
                        <button type="button" class="toggle-eye" id="eyeBtn" aria-label="Tampilkan password"><?= icon('eye') ?></button>
                    </div>
                </div>
                <button class="btn btn-primary btn-block" type="submit">Masuk</button>
            </form>

            <p class="auth__alt">Belum punya akun? <a href="register.php">Daftar di sini</a></p>
        </div>
    </section>
</div>

<script>
    const pw = document.getElementById('pw');
    const eyeBtn = document.getElementById('eyeBtn');
    const eye = `<?= str_replace(["\n","\r"], '', icon('eye')) ?>`;
    const eyeOff = `<?= str_replace(["\n","\r"], '', icon('eye-off')) ?>`;
    eyeBtn.addEventListener('click', () => {
        const show = pw.type === 'password';
        pw.type = show ? 'text' : 'password';
        eyeBtn.innerHTML = show ? eyeOff : eye;
    });
</script>
</body>
</html>
