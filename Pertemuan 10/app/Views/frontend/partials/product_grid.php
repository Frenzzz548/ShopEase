<?php $heading = $activePage === 'home' ? 'h3' : 'h2'; ?>
<div class="row row-cols-2 row-cols-md-3 g-2 g-md-3 g-lg-4">
    <?php foreach ($products as $id => $product): ?>
        <div class="col">
            <article class="card product-card h-100">
                <img class="product-image" src="<?= esc(base_url('images/shopease/' . $product['image']), 'attr') ?>"
                    alt="" width="720" height="480" loading="lazy">
                <div class="card-body d-flex flex-column">
                    <p class="product-category"><?= esc($categories[$product['category']]['name']) ?></p>
                    <<?= $heading ?> class="product-name"><?= esc($product['name']) ?></<?= $heading ?>>
                    <p class="product-price">Rp <?= esc(number_format($product['price'], 0, ',', '.')) ?></p>
                    <p class="product-stock mt-auto"><?= $product['stock'] > 0 ? 'Stok ' . esc((string) $product['stock']) : 'Stok habis' ?></p>
                    <a class="btn btn-outline-store btn-sm product-detail-link" href="<?= esc(url_to('product-detail', $id), 'attr') ?>"
                        aria-label="Lihat Detail <?= esc($product['name'], 'attr') ?>">Lihat Detail</a>
                </div>
            </article>
        </div>
    <?php endforeach ?>
</div>
