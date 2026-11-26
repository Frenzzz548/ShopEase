<?= $this->extend('layouts/store') ?>

<?= $this->section('content') ?>
<section class="store-note" aria-labelledby="dashboard-title">
    <h1 class="page-title" id="dashboard-title"><?= esc($title) ?></h1>
    <p>Selamat datang, <?= esc($accountName) ?>.</p>
    <a class="btn btn-outline-store" href="<?= esc(url_to('home'), 'attr') ?>">Kembali ke Beranda</a>
</section>
<?= $this->endSection() ?>
