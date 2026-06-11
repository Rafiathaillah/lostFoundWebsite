# Campus Lost & Found

Aplikasi web lost & found internal kampus (PHP + MySQL/PDO). Mahasiswa bisa
melaporkan barang hilang/ditemukan, mengklaim barang temuan, dan pelapor
memverifikasi klaim yang masuk.

## Cara menjalankan (XAMPP / Laragon)

1. Salin seluruh folder ini ke dalam `htdocs` (mis. `htdocs/lostfound`).
2. Jalankan **Apache** dan **MySQL** dari panel XAMPP.
3. Buka di browser: `http://localhost/lostfound/`
   - Database `lostfounddb` akan dibuat **otomatis** saat pertama kali diakses.
   - Untuk reset penuh + data contoh, buka:
     `http://localhost/lostfound/includes/initDb.php`
4. Login dengan akun demo:
   - **NIM:** `2021001234`
   - **Password:** `mahasiswa123`

> Jika user/password MySQL-mu berbeda, ubah di `includes/connection.php`
> dan `includes/initDb.php`.

## Struktur

```
lostfound/
├── index.php            Dashboard: statistik, cari/filter, grid, modal post
├── report_detail.php    Detail barang + form klaim
├── createReport.php     Simpan laporan (upload foto tervalidasi)
├── editReport.php       Edit laporan (pemilik)
├── deleteReport.php     Hapus laporan (POST + CSRF)
├── claimItem.php        Ajukan klaim
├── verifyClaim.php      Terima / tolak klaim
├── profile.php          Postingan saya · Klaim saya · Klaim masuk
├── login.php / register.php / logout.php
├── includes/            connection, initDb, functions, header, footer
├── assets/css/style.css Tema hijau & krem
├── assets/img/          Placeholder
└── uploads/             Foto barang (otomatis dibuat)
```

## Alur status

- **report.status:** `pending` (terbuka) → `process` (ada klaim menunggu) → `resolved` (selesai)
- **claim.claimStatus:** `pending` → `verified` / `rejected`
- Mengklaim barang *found* menaikkan status laporan ke `process`.
- Pelapor **menerima** klaim → laporan `resolved`, klaim lain otomatis `rejected`.
- Pelapor **menolak** semua klaim → laporan kembali `pending`.

## Fitur

- Autentikasi (bcrypt), proteksi halaman, CSRF token, logout bersih.
- Upload foto tervalidasi (tipe MIME + ukuran), nama file diacak.
- Pencarian + filter jenis/kategori/status, kartu barang, statistik.
- Sistem klaim & verifikasi end-to-end + badge notifikasi klaim masuk.
- Timestamp "x menit lalu", desain responsif (mobile-friendly).
