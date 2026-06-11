<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$categories = $pdo->query("SELECT * FROM category ORDER BY name")->fetchAll();

$q        = trim($_GET['q'] ?? '');
$fType    = $_GET['type'] ?? '';
$fCat     = $_GET['category'] ?? '';
$fStatus  = $_GET['status'] ?? '';

$where = [];
$args  = [];
if ($q !== '')                      { $where[] = "(r.itemName LIKE ? OR r.description LIKE ? OR r.lastLocation LIKE ?)"; $like = "%$q%"; array_push($args, $like, $like, $like); }
if (in_array($fType, ['lost','found'], true))                  { $where[] = "r.reportType = ?"; $args[] = $fType; }
if ($fCat !== '' && ctype_digit($fCat))                         { $where[] = "r.categoryID = ?"; $args[] = $fCat; }
if (in_array($fStatus, ['pending','process','resolved'], true)) { $where[] = "r.status = ?"; $args[] = $fStatus; }
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$sql = "SELECT r.*, c.name AS categoryName, c.icon AS categoryIcon, u.fullName
        FROM report r
        JOIN category c ON r.categoryID = c.ID
        JOIN `user` u   ON r.userID = u.ID
        $whereSql
        ORDER BY r.createdAt DESC, r.ID DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($args);
$reports = $stmt->fetchAll();

$stats = $pdo->query("SELECT
        COUNT(*) AS total,
        SUM(reportType='lost')   AS lost,
        SUM(reportType='found')  AS found,
        SUM(status='resolved')   AS resolved
    FROM report")->fetch();

$pageTitle = 'Dashboard — Campus Lost & Found';
$activeNav = 'dashboard';
require __DIR__ . '/includes/header.php';
?>

<div class="page-head">
    <div>
        <h2>Daftar Laporan Barang</h2>
        <p class="sub">Temukan barang yang hilang atau laporkan penemuanmu di lingkungan kampus.</p>
    </div>
    <button class="btn btn-primary" onclick="openModal()"><?= icon('plus','w-4') ?> Laporkan Barang</button>
</div>

<div class="stats">
    <div class="stat"><div class="n"><?= (int)$stats['total'] ?></div><div class="l"><?= icon('box') ?> Total laporan</div></div>
    <div class="stat"><div class="n"><?= (int)$stats['lost'] ?></div><div class="l"><?= icon('search') ?> Dilaporkan hilang</div></div>
    <div class="stat"><div class="n"><?= (int)$stats['found'] ?></div><div class="l"><?= icon('pin') ?> Ditemukan</div></div>
    <div class="stat"><div class="n"><?= (int)$stats['resolved'] ?></div><div class="l"><?= icon('check') ?> Sudah selesai</div></div>
</div>

<form class="toolbar" method="GET" action="index.php">
    <div class="field">
        <label class="label">Cari barang</label>
        <input class="input" type="text" name="q" value="<?= e($q) ?>" placeholder="Nama, deskripsi, atau lokasi…">
    </div>
    <div class="field">
        <label class="label">Jenis</label>
        <select class="select" name="type">
            <option value="">Semua</option>
            <option value="lost"  <?= $fType==='lost'?'selected':'' ?>>Hilang</option>
            <option value="found" <?= $fType==='found'?'selected':'' ?>>Ditemukan</option>
        </select>
    </div>
    <div class="field">
        <label class="label">Kategori</label>
        <select class="select" name="category">
            <option value="">Semua</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['ID'] ?>" <?= $fCat==(string)$cat['ID']?'selected':'' ?>><?= e($cat['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field">
        <label class="label">Status</label>
        <select class="select" name="status">
            <option value="">Semua</option>
            <option value="pending"  <?= $fStatus==='pending'?'selected':'' ?>>Terbuka</option>
            <option value="process"  <?= $fStatus==='process'?'selected':'' ?>>Proses klaim</option>
            <option value="resolved" <?= $fStatus==='resolved'?'selected':'' ?>>Selesai</option>
        </select>
    </div>
    <button class="btn btn-primary" type="submit"><?= icon('search','w-4') ?> Cari</button>
</form>

<div class="grid">
    <?php if ($reports): ?>
        <?php foreach ($reports as $item): ?>
            <a class="card" href="report_detail.php?id=<?= $item['ID'] ?>">
                <div class="card__img">
                    <?= typeBadge($item['reportType']) ?>
                    <img src="uploads/<?= e($item['itemPhoto']) ?>" alt="<?= e($item['itemName']) ?>"
                         onerror="this.onerror=null;this.src='assets/img/default.svg';">
                </div>
                <div class="card__body">
                    <div class="card__top">
                        <span class="chip"><?= icon($item['categoryIcon']) ?> <?= e($item['categoryName']) ?></span>
                        <?= statusBadge($item['status']) ?>
                    </div>
                    <h3><?= e($item['itemName']) ?></h3>
                    <p class="meta">oleh <?= e($item['fullName']) ?></p>
                    <p class="desc"><?= e($item['description']) ?: '<span style="color:var(--muted-2)">Tanpa deskripsi.</span>' ?></p>
                    <?php if ($item['lastLocation']): ?>
                        <p class="loc"><?= icon('pin') ?> <?= e($item['lastLocation']) ?></p>
                    <?php endif; ?>
                </div>
                <div class="card__foot">
                    <span class="time"><?= icon('clock') ?> <?= timeAgo($item['createdAt']) ?></span>
                    <span class="btn btn-soft btn-sm">Lihat detail</span>
                </div>
            </a>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty">
            <?= icon('inbox') ?>
            <h3>Belum ada laporan yang cocok</h3>
            <p>Coba ubah kata kunci atau filter, atau jadilah yang pertama melaporkan barang.</p>
        </div>
    <?php endif; ?>
</div>

<div class="modal" id="postModal">
    <div class="modal__box">
        <div class="modal__head">
            <h3>Laporkan Barang</h3>
            <button class="modal__close" onclick="closeModal()" aria-label="Tutup"><?= icon('x') ?></button>
        </div>
        <form class="modal__body" method="POST" action="createReport.php" enctype="multipart/form-data">
            <?= csrfField() ?>
            <div class="field">
                <label class="label">Jenis laporan</label>
                <div class="seg">
                    <label><input type="radio" name="reportType" value="lost" checked><span class="opt">LOST</span></label>
                    <label><input type="radio" name="reportType" value="found"><span class="opt">FOUND</span></label>
                </div>
            </div>
            <div class="field">
                <label class="label">Kategori</label>
                <select class="select" name="categoryID" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['ID'] ?>"><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label class="label">Nama barang</label>
                <input class="input" type="text" name="itemName" placeholder="cth. Dompet kulit coklat" required>
            </div>
            <div class="field">
                <label class="label">Deskripsi / ciri-ciri</label>
                <textarea class="textarea" name="description" placeholder="Warna, merek, isi, tanda khusus…"></textarea>
            </div>
            <div class="field">
                <label class="label">Lokasi terakhir / lokasi ditemukan</label>
                <input class="input" type="text" name="lastLocation" placeholder="cth. Perpustakaan Lt. 2">
            </div>
            <div class="field">
                <label class="label">Foto barang</label>
                <label class="file-drop" for="itemPhoto">
                    <?= icon('upload') ?>
                    <div>Klik untuk memilih foto</div>
                    <div class="fname" id="fileName">JPG, JPEG, PNG, atau WEBP · maks 4 MB</div>
                </label>
                <input type="file" name="itemPhoto" id="itemPhoto" accept="image/png,image/jpeg,image/webp" hidden>
            </div>
            <div class="modal__foot">
                <button type="button" class="btn btn-ghost" onclick="closeModal()">Batal</button>
                <button type="submit" class="btn btn-primary">Kirim laporan</button>
            </div>
        </form>
    </div>
</div>

<script>
    const modal = document.getElementById('postModal');
    function openModal(){ modal.classList.add('open'); document.body.style.overflow='hidden'; }
    function closeModal(){ modal.classList.remove('open'); document.body.style.overflow=''; }
    modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

    <?php if (!empty($_GET['error'])): ?>openModal();<?php endif; ?>

    document.getElementById('itemPhoto').addEventListener('change', function(){
        const f = this.files[0];
        document.getElementById('fileName').textContent = f ? f.name : 'JPG, JPEG, PNG, atau WEBP · maks 4 MB';
    });
</script>

<?php
if (!empty($_GET['error'])) {
    echo '<script>alert(' . json_encode('Gagal: ' . $_GET['error']) . ');</script>';
}
require __DIR__ . '/includes/footer.php';
