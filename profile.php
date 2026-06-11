<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$uid = currentUserId();

$p = $pdo->prepare("SELECT r.*, c.name AS categoryName,
        (SELECT COUNT(*) FROM claim cl WHERE cl.reportID = r.ID AND cl.claimStatus='pending') AS pendingClaims
    FROM report r JOIN category c ON r.categoryID = c.ID
    WHERE r.userID = ? ORDER BY r.createdAt DESC, r.ID DESC");
$p->execute([$uid]);
$myPosts = $p->fetchAll();

$cl = $pdo->prepare("SELECT cl.*, r.itemName, r.reportType, r.itemPhoto, r.ID AS reportID
    FROM claim cl JOIN report r ON cl.reportID = r.ID
    WHERE cl.userID = ? ORDER BY cl.createdAt DESC, cl.ID DESC");
$cl->execute([$uid]);
$myClaims = $cl->fetchAll();

$inc = $pdo->prepare("SELECT cl.*, r.itemName, r.itemPhoto, r.ID AS reportID, u.fullName, u.nim
    FROM claim cl
    JOIN report r ON cl.reportID = r.ID
    JOIN `user` u ON cl.userID = u.ID
    WHERE r.userID = ?
    ORDER BY (cl.claimStatus='pending') DESC, cl.createdAt DESC");
$inc->execute([$uid]);
$incoming = $inc->fetchAll();
$incomingPending = array_filter($incoming, fn($c) => $c['claimStatus'] === 'pending');

$pageTitle = 'Profil Saya';
$activeNav = 'profile';
require __DIR__ . '/includes/header.php';
?>

<div class="profile-head">
    <span class="avatar"><?= e(initials(currentUserName())) ?></span>
    <div>
        <h2><?= e(currentUserName()) ?></h2>
        <p class="sub">Kelola laporan dan klaim barangmu di sini.</p>
    </div>
</div>

<div class="tabs">
    <button class="tab active" data-tab="posts"><?= icon('box') ?> Postingan Saya <span class="count"><?= count($myPosts) ?></span></button>
    <button class="tab" data-tab="claims"><?= icon('tag') ?> Klaim Saya <span class="count"><?= count($myClaims) ?></span></button>
    <button class="tab" data-tab="incoming" id="incomingTab"><?= icon('inbox') ?> Klaim Masuk
        <span class="count <?= count($incomingPending) ? 'alert' : '' ?>"><?= count($incomingPending) ?: count($incoming) ?></span>
    </button>
</div>

<section class="tab-panel active" id="tab-posts">
    <?php if ($myPosts): ?>
        <?php foreach ($myPosts as $post): ?>
            <div class="list-row">
                <div class="lr-main">
                    <img class="lr-thumb" src="uploads/<?= e($post['itemPhoto']) ?>" alt="" onerror="this.onerror=null;this.src='assets/img/default.svg';">
                    <div style="min-width:0">
                        <h4><a href="report_detail.php?id=<?= $post['ID'] ?>"><?= e($post['itemName']) ?></a></h4>
                        <div class="lr-meta">
                            <?= typeBadge($post['reportType']) ?>
                            <?= statusBadge($post['status']) ?>
                            <span><?= e($post['categoryName']) ?></span>
                            <span>· <?= timeAgo($post['createdAt']) ?></span>
                            <?php if ($post['pendingClaims'] > 0): ?>
                                <span class="badge badge-process"><?= $post['pendingClaims'] ?> klaim menunggu</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="lr-actions">
                    <a href="editReport.php?id=<?= $post['ID'] ?>" class="btn btn-soft btn-sm">Edit</a>
                    <form method="POST" action="deleteReport.php" onsubmit="return confirm('Hapus laporan ini? Tindakan ini tidak bisa dibatalkan.');" style="display:inline">
                        <?= csrfField() ?>
                        <input type="hidden" name="id" value="<?= $post['ID'] ?>">
                        <button class="btn btn-danger btn-sm" type="submit">Hapus</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty"><?= icon('box') ?><h3>Belum ada laporan</h3><p>Laporan yang kamu buat akan muncul di sini. <a href="index.php" style="color:var(--green-700);font-weight:600">Buat laporan</a></p></div>
    <?php endif; ?>
</section>

<section class="tab-panel" id="tab-claims">
    <?php if ($myClaims): ?>
        <?php foreach ($myClaims as $claim): ?>
            <div class="list-row">
                <div class="lr-main">
                    <img class="lr-thumb" src="uploads/<?= e($claim['itemPhoto']) ?>" alt="" onerror="this.onerror=null;this.src='assets/img/default.svg';">
                    <div style="min-width:0">
                        <h4><a href="report_detail.php?id=<?= $claim['reportID'] ?>"><?= e($claim['itemName']) ?></a></h4>
                        <div class="lr-meta">
                            <?= claimBadge($claim['claimStatus']) ?>
                            <span>· diajukan <?= timeAgo($claim['createdAt']) ?></span>
                        </div>
                        <p class="text-muted" style="font-size:.82rem;margin-top:6px">Bukti: <?= e($claim['bukti']) ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty"><?= icon('tag') ?><h3>Belum ada klaim</h3><p>Klaim atas barang yang kamu temukan kembali akan tampil di sini.</p></div>
    <?php endif; ?>
</section>

<section class="tab-panel" id="tab-incoming">
    <?php if ($incoming): ?>
        <?php foreach ($incoming as $claim): ?>
            <div class="list-row">
                <div class="lr-main">
                    <img class="lr-thumb" src="uploads/<?= e($claim['itemPhoto']) ?>" alt="" onerror="this.onerror=null;this.src='assets/img/default.svg';">
                    <div style="min-width:0">
                        <h4><a href="report_detail.php?id=<?= $claim['reportID'] ?>"><?= e($claim['itemName']) ?></a></h4>
                        <div class="lr-meta">
                            <span>Diklaim oleh <strong style="color:var(--green-900)"><?= e($claim['fullName']) ?></strong> (<?= e($claim['nim']) ?>)</span>
                            <span>· <?= timeAgo($claim['createdAt']) ?></span>
                            <?= claimBadge($claim['claimStatus']) ?>
                        </div>
                        <p class="text-muted" style="font-size:.82rem;margin-top:6px">Bukti: <?= e($claim['bukti']) ?></p>
                    </div>
                </div>
                <?php if ($claim['claimStatus'] === 'pending'): ?>
                    <div class="lr-actions">
                        <form method="POST" action="verifyClaim.php" style="display:inline">
                            <?= csrfField() ?>
                            <input type="hidden" name="claimID" value="<?= $claim['ID'] ?>">
                            <input type="hidden" name="action" value="accept">
                            <button class="btn btn-primary btn-sm" type="submit"><?= icon('check') ?> Terima</button>
                        </form>
                        <form method="POST" action="verifyClaim.php" onsubmit="return confirm('Tolak klaim ini?');" style="display:inline">
                            <?= csrfField() ?>
                            <input type="hidden" name="claimID" value="<?= $claim['ID'] ?>">
                            <input type="hidden" name="action" value="reject">
                            <button class="btn btn-danger btn-sm" type="submit">Tolak</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty"><?= icon('inbox') ?><h3>Belum ada klaim masuk</h3><p>Saat seseorang mengklaim barang yang kamu temukan, permintaannya muncul di sini untuk kamu verifikasi.</p></div>
    <?php endif; ?>
</section>

<script>
    const tabs = document.querySelectorAll('.tab');
    const panels = document.querySelectorAll('.tab-panel');
    function activate(name){
        tabs.forEach(t => t.classList.toggle('active', t.dataset.tab === name));
        panels.forEach(p => p.classList.toggle('active', p.id === 'tab-' + name));
    }
    tabs.forEach(t => t.addEventListener('click', () => activate(t.dataset.tab)));
    if (location.hash === '#incoming') activate('incoming');
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
