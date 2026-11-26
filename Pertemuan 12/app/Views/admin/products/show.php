<?php $image = catalog_image_url($product['primary_image']['image'] ?? null); ?>
<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('content') ?>
<h1 class="dashboard-title"><?= esc($title) ?></h1>
<article class="dashboard-product-panel p-3 p-md-4">
    <div class="row g-4">
        <div class="col-12 col-md-5">
            <?php if ($image === null): ?>
                <div class="product-image product-image-placeholder detail-image">Foto belum tersedia</div>
            <?php else: ?>
                <img class="product-image detail-image" src="<?= esc($image, 'attr') ?>"
                    alt="<?= esc($product['name'], 'attr') ?>" width="720" height="480">
            <?php endif ?>
        </div>
        <div class="col-12 col-md-7">
            <h2 class="dashboard-heading"><?= esc($product['name']) ?></h2>
            <dl class="row mb-3">
                <dt class="col-sm-4">Kategori</dt><dd class="col-sm-8"><?= esc($product['category_name']) ?></dd>
                <dt class="col-sm-4">Slug</dt><dd class="col-sm-8 text-break"><?= esc($product['slug']) ?></dd>
                <dt class="col-sm-4">Harga</dt><dd class="col-sm-8"><?= esc(catalog_price($product['price'])) ?></dd>
                <dt class="col-sm-4">Stok</dt><dd class="col-sm-8"><?= esc(number_format((int) $product['stock'], 0, ',', '.')) ?></dd>
                <dt class="col-sm-4">Status</dt><dd class="col-sm-8"><?= esc(catalog_status_label($product['status'])) ?></dd>
            </dl>
            <h3 class="h6 fw-bold">Deskripsi</h3>
            <p class="admin-product-description text-break"><?= esc($product['description'] ?? '—') ?></p>
            <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-dashboard-primary" href="<?= esc(url_to($catalogRole . '-products-edit', $product['id_product']), 'attr') ?>">Edit Produk</a>
                <a class="btn btn-outline-store" href="<?= esc(url_to($catalogRole . '-products'), 'attr') ?>">Kembali</a>
            </div>
        </div>
    </div>
</article>
<?= $this->endSection() ?>
