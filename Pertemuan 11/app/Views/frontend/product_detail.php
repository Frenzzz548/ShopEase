<?= $this->extend('layouts/store') ?>

<?= $this->section('content') ?>
<nav aria-label="Breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= esc(url_to('home'), 'attr') ?>">Beranda</a></li>
        <li class="breadcrumb-item"><a href="<?= esc(url_to('products'), 'attr') ?>">Produk</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= esc($product['name']) ?></li>
    </ol>
</nav>

<article class="row gx-lg-5 gy-4 align-items-md-center">
    <div class="col-md-6">
        <?php $imageUrl = catalog_image_url($product['primary_image']['image'] ?? null); ?>
        <?php if ($imageUrl !== null): ?>
            <img class="product-image detail-image" src="<?= esc($imageUrl, 'attr') ?>"
                width="720" height="480" alt="<?= esc($product['name'], 'attr') ?>">
        <?php else: ?>
            <div class="product-image product-image-placeholder detail-image">Foto belum tersedia</div>
        <?php endif ?>
    </div>
    <div class="col-md-6">
        <p class="product-category"><?= esc($product['category_name']) ?></p>
        <h1 class="page-title"><?= esc($product['name']) ?></h1>
        <p class="detail-price"><?= esc(catalog_price($product['price'])) ?></p>
        <p class="detail-description"><?= esc($product['description'] ?? '') ?></p>
        <p class="product-status"><?= esc(catalog_status_label($product['status'])) ?></p>
        <p class="detail-stock"><?= $product['stock'] > 0 ? 'Stok ' . esc((string) $product['stock']) . ' unit' : 'Stok habis (0 unit)' ?></p>
        <a class="btn btn-outline-store" href="<?= esc(url_to('products'), 'attr') ?>">
            <span aria-hidden="true">&larr;</span> Kembali ke Katalog
        </a>
    </div>
</article>
<?= $this->endSection() ?>
