<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/connection.php';

function isLoggedIn(): bool { return isset($_SESSION['userID']); }

function requireLogin(): void {
    if (!isLoggedIn()) { header('Location: login.php'); exit; }
}

function currentUserId(): ?int { return $_SESSION['userID'] ?? null; }
function currentUserName(): string { return $_SESSION['fullName'] ?? 'User'; }

function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function csrfToken(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrfField(): string {
    return '<input type="hidden" name="csrf" value="' . e(csrfToken()) . '">';
}
function checkCsrf(): bool {
    return isset($_POST['csrf']) && hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf']);
}

function initials(string $name): string {
    $parts = preg_split('/\s+/', trim($name));
    $a = mb_substr($parts[0] ?? '', 0, 1);
    $b = mb_substr($parts[1] ?? '', 0, 1);
    return mb_strtoupper($a . $b) ?: 'U';
}

function timeAgo(?string $datetime): string {
    if (!$datetime) return '';
    $ts = strtotime($datetime);
    $diff = time() - $ts;
    if ($diff < 60)        return 'baru saja';
    if ($diff < 3600)      return floor($diff / 60) . ' menit lalu';
    if ($diff < 86400)     return floor($diff / 3600) . ' jam lalu';
    if ($diff < 604800)    return floor($diff / 86400) . ' hari lalu';
    return date('d M Y', $ts);
}

function typeBadge(string $type): string {
    return $type === 'lost'
        ? '<span class="badge badge-lost">Hilang</span>'
        : '<span class="badge badge-found">Ditemukan</span>';
}
function statusBadge(string $status): string {
    $map = [
        'pending'  => ['badge-pending',  'Terbuka'],
        'process'  => ['badge-process',  'Proses klaim'],
        'resolved' => ['badge-resolved', 'Selesai'],
    ];
    [$cls, $label] = $map[$status] ?? ['badge-pending', $status];
    return "<span class=\"badge $cls\">" . e($label) . "</span>";
}
function claimBadge(string $status): string {
    $map = [
        'pending'  => ['badge-pending',  'Menunggu verifikasi'],
        'verified' => ['badge-verified', 'Terverifikasi'],
        'rejected' => ['badge-rejected', 'Ditolak'],
    ];
    [$cls, $label] = $map[$status] ?? ['badge-pending', $status];
    return "<span class=\"badge $cls\">" . e($label) . "</span>";
}

function incomingClaimCount(PDO $pdo, int $userId): int {
    $q = $pdo->prepare("SELECT COUNT(*) FROM claim c
        JOIN report r ON c.reportID = r.ID
        WHERE r.userID = ? AND c.claimStatus = 'pending'");
    $q->execute([$userId]);
    return (int)$q->fetchColumn();
}

function icon(string $name, string $cls = ''): string {
    $c = $cls ? " class=\"" . e($cls) . "\"" : '';
    $open = "<svg$c viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"1.8\" stroke-linecap=\"round\" stroke-linejoin=\"round\">";
    $paths = [
        'compass'  => '<circle cx="12" cy="12" r="9"/><polygon points="16 8 14 14 8 16 10 10 16 8"/>',
        'search'   => '<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>',
        'plus'     => '<path d="M12 5v14M5 12h14"/>',
        'pin'      => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
        'user'     => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'logout'   => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/>',
        'box'      => '<path d="m21 8-9-5-9 5v8l9 5 9-5Z"/><path d="m3 8 9 5 9-5M12 13v8"/>',
        'phone'    => '<rect x="7" y="3" width="10" height="18" rx="2"/><path d="M11 18h2"/>',
        'card'     => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18"/>',
        'key'      => '<circle cx="8" cy="8" r="4"/><path d="m10.5 10.5 8 8M16 16l2-2M19 13l1.5 1.5"/>',
        'wallet'   => '<rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18M16 14h2"/>',
        'shirt'    => '<path d="M16 3 12 6 8 3 3 6l2 4 2-1v11h10V9l2 1 2-4Z"/>',
        'bottle'   => '<path d="M10 2h4v3l1 2v13a2 2 0 0 1-2 2h-2a2 2 0 0 1-2-2V7l1-2Z"/><path d="M9 11h6"/>',
        'pen'      => '<path d="M12 19l7-7 3 3-7 7-3-3Z"/><path d="m18 13-1.5-7.5L2 2l3.5 14.5L13 18l5-5Z"/><path d="M2 2l7.586 7.586"/><circle cx="11" cy="11" r="2"/>',
        'image'    => '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-5-5L5 21"/>',
        'upload'   => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 9l5-5 5 5M12 4v12"/>',
        'check'    => '<path d="M20 6 9 17l-5-5"/>',
        'x'        => '<path d="M18 6 6 18M6 6l12 12"/>',
        'clock'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'inbox'    => '<path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5 5h14l3 7v6a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-6Z"/>',
        'tag'      => '<path d="M3 7v5l9 9 7-7-9-9H3Z"/><circle cx="7.5" cy="7.5" r="1.5"/>',
        'eye'      => '<path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>',
        'eye-off'  => '<path d="M9.9 5A10 10 0 0 1 22 12a13 13 0 0 1-2 2.5M6.6 6.6A13 13 0 0 0 2 12s4 7 10 7a10 10 0 0 0 4-.8"/><path d="m2 2 20 20"/>',
        'doc'      => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/>',
    ];
    return $open . ($paths[$name] ?? $paths['box']) . '</svg>';
}
