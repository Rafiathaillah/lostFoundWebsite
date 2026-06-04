<?php
// Wajib ditaruh di baris nomor 1 sebelum kode apa pun
session_start(); 
require_once 'connection.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nim = trim($_POST['nim']);
    $password = $_POST['password'];

    if (!empty($nim) && !empty($password)) {
        // Ambil data user berdasarkan NIM
        $stmt = $pdo->prepare("SELECT * FROM `user` WHERE `nim` = ?");
        $stmt->execute([$nim]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Cek cocok atau tidaknya password
            if (password_verify($password, $user['password'])) {
                
                // Set Session dengan Sempurna
                $_SESSION['userID'] = $user['ID'];
                $_SESSION['fullName'] = $user['fullName'];
                
                // Redirect menggunakan JavaScript sebagai cadangan jika header() diblokir server
                echo "<script>
                        alert('Login Berhasil! Mengalihkan...');
                        window.location.href = 'index.php';
                      </script>";
                
                // Tetap pasang header bawaan PHP
                header("Location: index.php");
                exit;
            } else {
                $error = "Password salah! (Catatan seeder: passwordnya adalah 'mahasiswa123')";
            }
        } else {
            $error = "NIM tidak ditemukan di database. Silakan register dulu.";
        }
    } else {
        $error = "Silakan isi semua kolom.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Lost & Found</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-96">
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Lost & Found Login</h2>
        
        <!-- Jika ada error, kotak merah ini WAJIB muncul -->
        <?php if($error): ?> 
            <div class="bg-red-100 border border-red-400 text-red-700 p-3 rounded mb-4 text-sm font-semibold">
                ⚠️ <?= $error ?>
            </div> 
        <?php endif; ?>
        
        <form action="" method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">NIM</label>
                <input type="text" name="nim" class="mt-1 block w-full border border-gray-300 rounded p-2 focus:ring-blue-500" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" class="mt-1 block w-full border border-gray-300 rounded p-2 focus:ring-blue-500" required>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white p-2 rounded font-semibold hover:bg-blue-700 transition">Masuk</button>
        </form>
        <p class="text-sm text-center text-gray-600 mt-4">Belum punya akun? <a href="register.php" class="text-blue-600 hover:underline">Register</a></p>
    </div>
</body>
</html>