<?php $heading = $activePage === 'home' ? 'h3' : 'h2'; ?>
<?php if ($products === []): ?>
    <p class="store-note mb-0"><?= esc($emptyProductsMessage) ?></p>
<?php else: ?>
<div class="row row-cols-2 row-cols-md-3 g-2 g-md-3 g-lg-4">
    <?php foreach ($products as $product): ?>
        <?php $imageUrl = catalog_image_url($product['primary_image']['image'] ?? null); ?>
        <div class="col">
            <article class="card product-card h-100">
                <?php if ($imageUrl !== null): ?>
                    <img class="product-image" src="<?= esc($imageUrl, 'attr') ?>" alt="" width="720" height="480" loading="lazy">
                <?php else: ?>
                    <div class="product-image product-image-placeholder">Foto belum tersedia</div>
                <?php endif ?>
                <div class="card-body d-flex flex-column">
                    <p class="product-category"><?= esc($product['category_name']) ?></p>
                    <<?= $heading ?> class="product-name"><?= esc($product['name']) ?></<?= $heading ?>>
                    <p class="product-price"><?= esc(catalog_price($product['price'])) ?></p>
                    <p class="product-status mt-auto"><?= esc(catalog_status_label($product['status'])) ?></p>
                    <p class="product-stock"><?= $product['stock'] > 0 ? 'Stok ' . esc((string) $product['stock']) : 'Stok habis' ?></p>
                    <a class="btn btn-outline-store btn-sm product-detail-link" href="<?= esc(url_to('product-detail', $product['id_product']), 'attr') ?>"
                        aria-label="Lihat Detail <?= esc($product['name'], 'attr') ?>">Lihat Detail</a>
                </div>
            </article>
        </div>
    <?php endforeach ?>
</div>
<?php endif ?>
