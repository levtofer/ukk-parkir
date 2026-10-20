<?php

/**
 * Render the sidebar with active navigation state
 *
 * @param string $activePage The title/name of the currently active menu item
 */
function render_sidebar($activePage = '')
{
    $role = $_SESSION['role'] ?? '';

    $navItems = [];

    if ($role === 'admin') {
        $navItems = [
            ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/index.php'],
            ['title' => 'Area Parkir', 'url' => BASE_URL . '/admin/area/index.php'],
            ['title' => 'Tarif Parkir', 'url' => BASE_URL . '/admin/tarif/index.php'],
            ['title' => 'Management User', 'url' => BASE_URL . '/admin/user/index.php'],
            ['title' => 'Member', 'url' => BASE_URL . '/admin/member/index.php'],
            ['title' => 'Log Aktivitas', 'url' => BASE_URL . '/admin/log/index.php'],
        ];
    } elseif ($role === 'petugas') {
        $navItems = [
            ['title' => 'Dashboard', 'url' => BASE_URL . '/petugas/index.php'],
            ['title' => 'Transaksi Parkir', 'url' => BASE_URL . '/petugas/transaksi/index.php'],
        ];
    } elseif ($role === 'owner') {
        $navItems = [
            ['title' => 'Dashboard', 'url' => BASE_URL . '/owner/index.php'],
            ['title' => 'Rekap Laporan', 'url' => BASE_URL . '/owner/rekap.php'],
        ];
    }
?>
    <aside class="sidebar">
        <div class="brand">
            <img src="<?= BASE_URL; ?>/assets/images/logo.png" alt="Logo" class="brand-logo">
            <span>Aplikasi Parkir</span>
        </div>
        <nav>
            <?php foreach ($navItems as $item): ?>
                <?php $isActive = (strtolower($item['title']) === strtolower($activePage)) ? 'active' : ''; ?>
                <a href="<?= $item['url']; ?>" class="<?= $isActive; ?>">
                    <span class="icon">#</span> <?= htmlspecialchars($item['title']); ?>
                </a>
            <?php endforeach; ?>
            <a href="<?= BASE_URL; ?>/logout.php" class="logout">
                <span class="icon">#</span> Logout
            </a>
        </nav>
    </aside>

    <script>
        (function() {
            let link = document.querySelector("link[rel~='icon']");
            if (!link) {
                link = document.createElement('link');
                link.rel = 'icon';
                document.getElementsByTagName('head')[0].appendChild(link);
            }
            link.type = 'image/x-icon';
            link.href = '<?= BASE_URL; ?>/assets/images/favicon.ico';
        })();
    </script>
<?php
}
?>