<?php
session_start();
require_once 'connection.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nim = trim($_POST['nim']);
    $fullName = trim($_POST['fullName']);
    $password = $_POST['password'];

    if (!empty($nim) && !empty($fullName) && !empty($password)) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO `user` (`nim`, `fullName`, `password`) VALUES (?, ?, ?)");
            $stmt->execute([$nim, $fullName, $hashedPassword]);
            $success = "Registrasi sukses! Silakan login.";
        } catch (PDOException $e) {
            $error = "NIM sudah terdaftar atau terjadi kesalahan sistem.";
        }
    } else {
        $error = "Semua kolom wajib diisi.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Lost & Found</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-96">
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Daftar Akun</h2>
        <?php if($error): ?> <div class="bg-red-100 text-red-700 p-2 rounded mb-4 text-sm"><?= $error ?></div> <?php endif; ?>
        <?php if($success): ?> <div class="bg-green-100 text-green-700 p-2 rounded mb-4 text-sm"><?= $success ?></div> <?php endif; ?>
        
        <form action="" method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">NIM</label>
                <input type="text" name="nim" class="mt-1 block w-full border border-gray-300 rounded p-2 focus:ring-blue-500 focus:border-blue-500" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                <input type="text" name="fullName" class="mt-1 block w-full border border-gray-300 rounded p-2 focus:ring-blue-500 focus:border-blue-500" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" class="mt-1 block w-full border border-gray-300 rounded p-2 focus:ring-blue-500 focus:border-blue-500" required>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white p-2 rounded font-semibold hover:bg-blue-700 transition">Daftar</button>
        </form>
        <p class="text-sm text-center text-gray-600 mt-4">Sudah punya akun? <a href="login.php" class="text-blue-600 hover:underline">Login disini</a></p>
    </div>
</body>
</html>