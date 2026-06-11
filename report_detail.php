<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$id = $_GET['id'] ?? '';
if (!ctype_digit((string)$id)) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare("SELECT r.*, c.name AS categoryName, c.icon AS categoryIcon, u.fullName, u.nim
    FROM report r
    JOIN category c ON r.categoryID = c.ID
    JOIN `user` u   ON r.userID = u.ID
    WHERE r.ID = ?");
$stmt->execute([$id]);
$item = $stmt->fetch();

$pageTitle = 'Detail Laporan';
$activeNav = '';
require __DIR__ . '/includes/header.php';

if (!$item) {
    echo '<div class="empty" style="margin-top:60px">' . icon('inbox') . '<h3>Laporan tidak ditemukan</h3><p>Laporan mungkin sudah dihapus. <a href="index.php" style="color:var(--green-700);font-weight:600">Kembali ke dashboard</a></p></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$isOwner = ((int)$item['userID'] === currentUserId());

// Klaim milik user ini pada laporan ini (jika ada)
$myClaimStmt = $pdo->prepare("SELECT * FROM claim WHERE reportID = ? AND userID = ? ORDER BY ID DESC LIMIT 1");
$myClaimStmt->execute([$id, currentUserId()]);
$myClaim = $myClaimStmt->fetch();

// Daftar klaim (untuk pemilik)
$claims = [];
if ($isOwner) {
    $cs = $pdo->prepare("SELECT cl.*, u.fullName, u.nim FROM claim cl JOIN `user` u ON cl.userID = u.ID WHERE cl.reportID = ? ORDER BY cl.ID DESC");
    $cs->execute([$id]);
    $claims = $cs->fetchAll();
}

$canClaim = (!$isOwner && $item['reportType'] === 'found' && $item['status'] !== 'resolved' && !$myClaim);
?>

<div style="margin-top:24px">
    <a href="index.php" class="btn btn-ghost btn-sm">← Kembali ke dashboard</a>
</div>

<div class="detail">
    <div>
        <div class="detail__img">
            <img src="uploads/<?= e($item['itemPhoto']) ?>" alt="<?= e($item['itemName']) ?>"
                 onerror="this.onerror=null;this.src='assets/img/default.svg';">
        </div>
    </div>

    <div>
        <div class="flex gap-8 items-center" style="margin-bottom:12px">
            <?= typeBadge($item['reportType']) ?>
            <?= statusBadge($item['status']) ?>
            <span class="chip"><?= icon($item['categoryIcon']) ?> <?= e($item['categoryName']) ?></span>
        </div>
        <h2 style="font-size:1.8rem"><?= e($item['itemName']) ?></h2>
        <p class="text-muted" style="margin:6px 0 18px"><?= icon('clock') ?> Dilaporkan <?= timeAgo($item['createdAt']) ?></p>

        <div class="panel">
            <h3>Detail barang</h3>
            <dl class="dl">
                <dt>Deskripsi</dt><dd><?= e($item['description']) ?: '—' ?></dd>
                <dt>Lokasi</dt><dd><?= e($item['lastLocation']) ?: '—' ?></dd>
                <dt>Pelapor</dt><dd><?= e($item['fullName']) ?></dd>
            </dl>
        </div>

        <?php if ($isOwner): ?>
            <div class="panel">
                <h3>Ini laporanmu</h3>
                <p class="text-muted" style="font-size:.9rem">
                    <?= $claims
                        ? 'Ada ' . count($claims) . ' klaim masuk. Kelola verifikasinya di Profil Saya.'
                        : 'Belum ada yang mengajukan klaim untuk barang ini.' ?>
                </p>
                <div class="flex gap-8" style="margin-top:12px">
                    <a href="editReport.php?id=<?= $item['ID'] ?>" class="btn btn-soft btn-sm">Edit laporan</a>
                    <a href="profile.php#incoming" class="btn btn-ghost btn-sm">Lihat klaim masuk</a>
                </div>
            </div>

        <?php elseif ($myClaim): ?>
            <div class="panel">
                <h3>Status klaimmu</h3>
                <div style="margin-bottom:10px"><?= claimBadge($myClaim['claimStatus']) ?></div>
                <p class="text-muted" style="font-size:.9rem">Bukti yang kamu kirim: <em><?= e($myClaim['bukti']) ?: '—' ?></em></p>
            </div>

        <?php elseif ($canClaim): ?>
            <div class="panel">
                <h3>Klaim barang ini</h3>
                <p class="text-muted" style="font-size:.88rem;margin-bottom:14px">Jelaskan bukti kepemilikan agar pelapor bisa memverifikasi.</p>
                <form method="POST" action="claimItem.php">
                    <?= csrfField() ?>
                    <input type="hidden" name="reportID" value="<?= $item['ID'] ?>">
                    <div class="field">
                        <label class="label">Bukti kepemilikan</label>
                        <textarea class="textarea" name="bukti" placeholder="cth. Di dalam dompet ada KTM atas nama saya, ada foto..." required></textarea>
                    </div>
                    <button class="btn btn-primary btn-block" type="submit"><?= icon('check','w-4') ?> Ajukan klaim</button>
                </form>
            </div>

        <?php elseif ($item['reportType'] === 'lost'): ?>
            <div class="panel">
                <h3>Barang ini dilaporkan hilang</h3>
                <p class="text-muted" style="font-size:.9rem">Jika kamu menemukannya, hubungi pelapor melalui kanal kampus. Klaim hanya berlaku untuk barang berstatus <em>ditemukan</em>.</p>
            </div>
        <?php elseif ($item['status'] === 'resolved'): ?>
            <div class="panel">
                <h3>Sudah selesai</h3>
                <p class="text-muted" style="font-size:.9rem">Barang ini sudah diverifikasi dan dikembalikan ke pemiliknya.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
