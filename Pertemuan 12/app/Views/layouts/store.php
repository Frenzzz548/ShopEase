<?php

$navigation = [
    'home' => 'Beranda',
    'products' => 'Produk',
    'about' => 'Tentang',
];
$session = session();
$accountRole = $session->get('isLoggedIn') === true ? $session->get('role') : null;
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title) ?> | ShopEase</title>
    <link href="<?= esc(base_url('css/bootstrap/bootstrap.min.css'), 'attr') ?>" rel="stylesheet">
    <link href="<?= esc(base_url('css/shopease.css'), 'attr') ?>" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">
    <a class="visually-hidden-focusable skip-link" href="#main-content">Lewati ke konten</a>
    <header class="site-header border-bottom bg-white">
        <nav class="container site-navigation" aria-label="Navigasi utama">
            <a class="site-brand" href="<?= esc(url_to('home'), 'attr') ?>">Shop<span>Ease</span></a>
            <ul class="nav site-menu">
                <?php foreach ($navigation as $route => $label): ?>
                    <?php $isCurrent = $activePage === $route; ?>
                    <li class="nav-item">
                        <a class="nav-link<?= $isCurrent ? ' active' : '' ?>" href="<?= esc(url_to($route), 'attr') ?>"
                            <?= $isCurrent ? 'aria-current="' . esc($navigationCurrent ?? 'page', 'attr') . '"' : '' ?>><?= esc($label) ?></a>
                    </li>
                <?php endforeach ?>
            </ul>
            <div class="nav-account d-flex align-items-center justify-content-end gap-2">
                <?php if (in_array($accountRole, \App\Models\UserModel::ROLES, true)): ?>
                    <a class="btn btn-outline-store btn-sm" href="<?= esc(url_to($accountRole . '-dashboard'), 'attr') ?>"
                        <?= $activePage === 'dashboard' ? 'aria-current="page"' : '' ?>>Dashboard <?= esc(ucfirst($accountRole)) ?></a>
                    <form method="post" action="<?= esc(url_to('logout'), 'attr') ?>">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-outline-store btn-sm">Logout</button>
                    </form>
                <?php else: ?>
                    <a class="btn btn-outline-store btn-sm" href="<?= esc(url_to('login'), 'attr') ?>"
                        <?= $activePage === 'login' ? 'aria-current="page"' : '' ?>>Login</a>
                    <a class="btn btn-outline-store btn-sm" href="<?= esc(url_to('register'), 'attr') ?>"
                        <?= $activePage === 'register' ? 'aria-current="page"' : '' ?>>Daftar</a>
                <?php endif ?>
            </div>
        </nav>
    </header>

    <main id="main-content" class="container flex-grow-1 store-main">
        <?= $this->renderSection('content') ?>
    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-content d-flex flex-wrap justify-content-between align-items-start gap-4">
                <div>
                    <a class="site-brand" href="<?= esc(url_to('home'), 'attr') ?>">Shop<span>Ease</span></a>
                    <p class="mb-0 mt-2">Katalog toko online untuk praktikum PBKK.</p>
                </div>
                <nav aria-label="Navigasi footer">
                    <ul class="list-unstyled d-flex flex-wrap gap-3 mb-0">
                        <?php foreach ($navigation as $route => $label): ?>
                            <li><a href="<?= esc(url_to($route), 'attr') ?>"><?= esc($label) ?></a></li>
                        <?php endforeach ?>
                    </ul>
                </nav>
            </div>
            <p class="footer-copyright mb-0">&copy; <?= esc(date('Y')) ?> ShopEase</p>
        </div>
    </footer>
</body>
</html>
