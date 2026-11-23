<?= $this->extend('layouts/store') ?>

<?= $this->section('content') ?>
<header class="page-heading">
    <h1 class="page-title"><?= esc($title) ?></h1>
    <p class="mb-0">Jelajahi koleksi ShopEase dan lihat harga serta ketersediaan setiap produk.</p>
</header>

<nav class="d-flex flex-wrap gap-2 mb-4" aria-label="Filter kategori">
    <a class="filter-link" href="<?= esc(url_to('products'), 'attr') ?>"<?= $selectedCategory === null ? ' aria-current="true"' : '' ?>>Semua</a>
    <?php foreach ($categories as $slug => $category): ?>
        <?php $categoryUrl = url_to('products') . '?' . http_build_query(['category' => $slug]); ?>
        <a class="filter-link" href="<?= esc($categoryUrl, 'attr') ?>"<?= $selectedCategory === $slug ? ' aria-current="true"' : '' ?>>
            <?= esc($category['name']) ?>
        </a>
    <?php endforeach ?>
</nav>

<?= $this->include('frontend/partials/product_grid') ?>
<?= $this->endSection() ?>
