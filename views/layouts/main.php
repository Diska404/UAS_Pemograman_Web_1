<?php
$currentUser = \App\Services\Auth::user();
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$notice = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$sections = [
    '' => ['dashboard' => ['grid', 'Dashboard']],
    'MASTER DATA' => ['barang' => ['box', 'Data Barang'], 'gudang' => ['warehouse', 'Gudang'], 'supplier' => ['supplier', 'Supplier']],
    'TRANSAKSI' => ['barang-masuk' => ['in', 'Barang Masuk'], 'barang-keluar' => ['out', 'Barang Keluar']],
    'LAPORAN' => ['reports' => ['report', 'Laporan Inventori']],
];
if (($currentUser['role'] ?? '') === 'Admin') {
    $sections['SISTEM'] = ['users' => ['users', 'Pengguna'], 'audit-logs' => ['clock', 'Audit Log']];
}
$sections['AKUN'] = ['profile' => ['user', 'Profil'], 'profile#password-panel' => ['lock', 'Ganti Password']];
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="<?= e(\App\Core\Csrf::token()) ?>">
    <title><?= e($title) ?> · SIM Inventory</title>
    <link rel="stylesheet" href="/assets/vendor/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/vendor/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="/assets/css/app.css">
    <script defer src="/assets/vendor/jquery.min.js"></script>
    <script defer src="/assets/vendor/bootstrap.bundle.min.js"></script>
    <script defer src="/assets/vendor/dataTables.min.js"></script>
    <script defer src="/assets/vendor/dataTables.bootstrap5.min.js"></script>
    <script defer src="/assets/vendor/chart.umd.min.js"></script>
    <script defer src="/assets/vendor/sweetalert2.all.min.js"></script>
    <script type="module" src="/assets/js/app.js"></script>
    <?php if ($path === '/dashboard'): ?><script type="module" src="/assets/js/dashboard.js"></script><?php endif; ?>
    <?php if ($path === '/barang'): ?><script type="module" src="/assets/js/inventory-view.js"></script><?php endif; ?>
    <?php if ($path === '/reports'): ?><script type="module" src="/assets/js/report.js"></script><?php endif; ?>
</head>
<body class="<?= $currentUser ? 'app-body' : 'auth-body' ?>">
<a class="skip-link" href="#main">Lewati navigasi</a>
<?php if ($currentUser): ?>
    <button class="sidebar-backdrop" id="sidebar-backdrop" aria-label="Tutup navigasi" tabindex="-1" hidden></button>
    <aside class="sidebar" id="sidebar" aria-label="Navigasi workspace">
        <div class="sidebar-brand-row">
            <a class="brand" href="/dashboard" aria-label="SIM Inventory Dashboard"><span class="brand-icon"><?= icon('box') ?></span><span class="brand-label">SIM Inventory<small>INVENTORY WORKSPACE</small></span></a>
            <button class="icon-button sidebar-close" id="sidebar-close" aria-label="Tutup navigasi"><?= icon('close') ?></button>
        </div>
        <nav class="sidebar-nav" aria-label="Navigasi utama">
        <?php foreach ($sections as $heading => $links): ?>
            <?php if ($heading): ?><div class="nav-caption"><?= e($heading) ?></div><?php endif; ?>
            <?php foreach ($links as $url => [$symbol, $label]): $active = '/' . $url === $path || (!str_contains($url, '#') && str_starts_with($path, '/' . $url . '/')); ?>
            <a class="nav-item <?= $active ? 'active' : '' ?>" href="/<?= e($url) ?>" title="<?= e($label) ?>" aria-label="<?= e($label) ?>" <?= $active ? 'aria-current="page"' : '' ?>><?= icon($symbol) ?><span class="nav-label"><?= e($label) ?></span></a>
            <?php endforeach; ?>
        <?php endforeach; ?>
        </nav>
        <div class="sidebar-bottom">
            <a class="account-summary" href="/profile" aria-label="Profil akun"><span class="avatar"><?= e(mb_substr($currentUser['name'], 0, 1)) ?></span><span class="account-copy"><strong><?= e($currentUser['name']) ?></strong><small><?= e($currentUser['role']) ?></small></span></a>
            <form action="/logout" method="post"><?= csrf_field() ?><button class="logout-btn" type="submit" title="Keluar dari aplikasi" aria-label="Keluar dari aplikasi"><?= icon('logout') ?><span class="nav-label">Keluar dari aplikasi</span></button></form>
        </div>
    </aside>
    <div class="workspace" id="workspace">
        <header class="topbar">
            <div class="topbar-left"><button class="icon-button" id="menu-toggle" aria-label="Ciutkan navigasi" aria-expanded="true" aria-controls="sidebar"><?= icon('menu') ?></button><span class="breadcrumb-text">Workspace <span>/</span> <strong><?= e($title) ?></strong></span></div>
            <div class="topbar-right"><span class="today"><?= icon('clock') ?> <?= date('d M Y') ?></span><span class="topbar-divider"></span><a href="/profile" class="role-pill"><span class="status-dot"></span><?= e($currentUser['role']) ?></a></div>
        </header>
        <main id="main" class="main-content" tabindex="-1"><?= $content ?></main>
        <footer class="app-footer"><span><strong>Inventory Barang By Diska Kurnia Azzahra Putra</strong></span><span>Pemrograman Web 1</span></footer>
    </div>
<?php else: ?>
    <main id="main"><?= $content ?></main>
<?php endif; ?>
<?php if ($notice): ?><div id="flash" role="status" data-message="<?= e($notice['message']) ?>" data-type="<?= e($notice['type']) ?>" class="flash-fallback"><?= e($notice['message']) ?></div><?php endif; ?>
</body>
</html>
