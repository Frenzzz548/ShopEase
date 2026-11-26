<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title) ?> | ShopEase</title>
    <link href="<?= esc(base_url('css/bootstrap/bootstrap.min.css'), 'attr') ?>" rel="stylesheet">
    <link href="<?= esc(base_url('css/shopease.css'), 'attr') ?>" rel="stylesheet">
    <link href="<?= esc(base_url('css/dashboard.css'), 'attr') ?>" rel="stylesheet">
</head>
<body class="dashboard-shell">
    <a class="visually-hidden-focusable skip-link" href="#main-content">Lewati ke konten</a>

    <aside class="dashboard-sidebar">
        <a class="site-brand dashboard-brand" href="<?= esc(url_to('home'), 'attr') ?>">Shop<span>Ease</span></a>
        <nav class="dashboard-nav" aria-label="Navigasi dashboard">
            <a class="dashboard-link" href="<?= esc(url_to($dashboardRoute), 'attr') ?>"
                <?= $activeSection === 'dashboard' ? 'aria-current="page"' : '' ?>>Dashboard</a>
            <a class="dashboard-link" href="<?= esc(url_to($catalogRole . '-categories'), 'attr') ?>"
                <?= $activeSection === 'categories' ? 'aria-current="page"' : '' ?>>Kategori</a>
            <a class="dashboard-link" href="<?= esc(url_to($catalogRole . '-products'), 'attr') ?>"
                <?= $activeSection === 'products' ? 'aria-current="page"' : '' ?>>Produk</a>
        </nav>
        <form class="dashboard-logout" method="post" action="<?= esc(url_to('logout'), 'attr') ?>">
            <?= csrf_field() ?>
            <button class="dashboard-link dashboard-logout-button" type="submit">Logout</button>
        </form>
    </aside>

    <div class="dashboard-workspace">
        <header class="dashboard-topbar">
            <a class="store-link" href="<?= esc(url_to('home'), 'attr') ?>">Lihat toko</a>
            <div class="dashboard-account">
                <span class="dashboard-account-name"><?= esc($accountName) ?></span>
                <span class="dashboard-role"><?= esc($roleLabel) ?></span>
            </div>
        </header>

        <main id="main-content" class="dashboard-main">
            <?= $this->renderSection('content') ?>
        </main>
    </div>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
