<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$id = $_GET['id'] ?? '';
if (!ctype_digit((string)$id)) { header('Location: profile.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM report WHERE ID = ? AND userID = ?");
$stmt->execute([$id, currentUserId()]);
$report = $stmt->fetch();

if (!$report) {
    $pageTitle = 'Edit Laporan'; require __DIR__ . '/includes/header.php';
    echo '<div class="empty" style="margin-top:60px">' . icon('inbox') . '<h3>Tidak dapat diakses</h3><p>Laporan tidak ditemukan atau bukan milikmu.</p></div>';
    require __DIR__ . '/includes/footer.php'; exit;
}

$categories = $pdo->query("SELECT * FROM category ORDER BY name")->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && checkCsrf()) {
    $categoryID   = $_POST['categoryID'] ?? '';
    $reportType   = $_POST['reportType'] ?? '';
    $itemName     = trim($_POST['itemName'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    $lastLocation = trim($_POST['lastLocation'] ?? '');
    $status       = $_POST['status'] ?? $report['status'];

    if ($report['status'] === 'process') $status = 'process';
    if (!in_array($status, ['pending','resolved','process'], true)) $status = $report['status'];

    if (!ctype_digit((string)$categoryID) || !in_array($reportType, ['lost','found'], true) || $itemName === '') {
        $error = 'Lengkapi jenis, kategori, dan nama barang.';
    } else {
        $up = $pdo->prepare("UPDATE report SET categoryID=?, reportType=?, itemName=?, description=?, lastLocation=?, status=? WHERE ID=? AND userID=?");
        $up->execute([$categoryID, $reportType, $itemName, $description, $lastLocation, $status, $id, currentUserId()]);
        header('Location: profile.php'); exit;
    }
}

$pageTitle = 'Edit Laporan';
$activeNav = '';
require __DIR__ . '/includes/header.php';
?>
<div style="max-width:560px;margin:30px auto">
    <a href="profile.php" class="btn btn-ghost btn-sm">← Kembali ke profil</a>
    <div class="panel" style="margin-top:16px">
        <h3 style="font-size:1.3rem;margin-bottom:18px">Ubah Laporan</h3>
        <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

        <form method="POST" action="">
            <?= csrfField() ?>
            <div class="field">
                <label class="label">Jenis laporan</label>
                <div class="seg">
                    <label><input type="radio" name="reportType" value="lost"  <?= $report['reportType']==='lost'?'checked':'' ?>><span class="opt">Hilang</span></label>
                    <label><input type="radio" name="reportType" value="found" <?= $report['reportType']==='found'?'checked':'' ?>><span class="opt">Ditemukan</span></label>
                </div>
            </div>
            <div class="field">
                <label class="label">Kategori</label>
                <select class="select" name="categoryID" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['ID'] ?>" <?= $report['categoryID']==$cat['ID']?'selected':'' ?>><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label class="label">Nama barang</label>
                <input class="input" type="text" name="itemName" value="<?= e($report['itemName']) ?>" required>
            </div>
            <div class="field">
                <label class="label">Deskripsi</label>
                <textarea class="textarea" name="description"><?= e($report['description']) ?></textarea>
            </div>
            <div class="field">
                <label class="label">Lokasi</label>
                <input class="input" type="text" name="lastLocation" value="<?= e($report['lastLocation']) ?>">
            </div>
            <div class="field">
                <label class="label">Status</label>
                <?php if ($report['status'] === 'process'): ?>
                    <div class="alert" style="background:var(--blue-bg);color:var(--blue-tx)">Sedang ada klaim berjalan. Status diatur otomatis lewat verifikasi klaim di Profil Saya.</div>
                <?php else: ?>
                    <select class="select" name="status">
                        <option value="pending"  <?= $report['status']==='pending'?'selected':'' ?>>Terbuka</option>
                        <option value="resolved" <?= $report['status']==='resolved'?'selected':'' ?>>Selesai</option>
                    </select>
                <?php endif; ?>
            </div>
            <div class="modal__foot">
                <a href="profile.php" class="btn btn-ghost">Batal</a>
                <button class="btn btn-primary" type="submit">Simpan perubahan</button>
            </div>
        </form>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
