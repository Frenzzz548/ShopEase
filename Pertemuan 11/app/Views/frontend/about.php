<?= $this->extend('layouts/store') ?>

<?= $this->section('content') ?>
<section class="store-note">
    <h1 class="page-title"><?= esc($title) ?></h1>
    <p class="mb-3">ShopEase adalah proyek toko online untuk praktikum PBKK menggunakan CodeIgniter 4.</p>
    <p class="mb-4">Jelajahi kategori dalam katalog. Halaman detail menampilkan foto, deskripsi, harga, stok, dan status ketersediaan.</p>
    <a class="btn btn-outline-store" href="<?= esc(url_to('products'), 'attr') ?>">Lihat Katalog</a>
</section>
<?= $this->endSection() ?>
