<?= $this->extend('layouts/store') ?>

<?= $this->section('content') ?>
<section class="store-note">
    <h1 class="page-title"><?= esc($title) ?></h1>
    <p class="mb-3">ShopEase adalah proyek toko online untuk praktikum PBKK menggunakan CodeIgniter 4.</p>
    <p class="mb-4">Jelajahi fashion, elektronik, olahraga, dan aksesoris. Setiap halaman detail menampilkan foto, deskripsi, harga, dan stok contoh.</p>
    <a class="btn btn-outline-store" href="<?= esc(url_to('products'), 'attr') ?>">Lihat Katalog</a>
</section>
<?= $this->endSection() ?>
