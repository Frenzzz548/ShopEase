<?php use App\Libraries\ProductImageStorage; ?>

<section id="product-gallery" class="dashboard-product-panel mt-4 p-3 p-md-4" aria-labelledby="gallery-title">
    <h2 id="gallery-title" class="dashboard-heading">Galeri Produk</h2>
    <?php if (session()->has('success')): ?>
        <p class="alert alert-success" role="status"><?= esc(session('success')) ?></p>
    <?php elseif (session()->has('error')): ?>
        <p class="alert alert-danger" role="alert"><?= esc(session('error')) ?></p>
    <?php endif ?>
    <p class="small mb-3"><?= esc((string) count($images)) ?> dari <?= esc((string) ProductImageStorage::MAX_FILES) ?> gambar</p>
    <?php if ($images === []): ?>
        <p class="mb-0">Foto belum tersedia.</p>
    <?php else: ?>
        <ul class="product-image-grid list-unstyled mb-0">
            <?php foreach ($images as $index => $photo): ?>
                <?php
                $url = catalog_image_url($photo['image']);
                $label = 'Gambar ' . ($index + 1);
                ?>
                <li class="product-gallery-card">
                    <?php if ($url === null): ?>
                        <div class="product-gallery-photo product-image-placeholder">Foto belum tersedia</div>
                    <?php else: ?>
                        <img class="product-gallery-photo" src="<?= esc($url, 'attr') ?>" alt="<?= esc($label, 'attr') ?>"
                            width="240" height="160" loading="lazy">
                    <?php endif ?>
                    <div class="p-2">
                        <p class="fw-semibold small mb-2"><?= esc($label) ?><?= (int) $photo['is_primary'] === 1 ? ' · Utama' : '' ?></p>
                        <div class="d-flex flex-wrap gap-2">
                            <?php if ((int) $photo['is_primary'] !== 1): ?>
                                <form method="post" action="<?= esc(url_to($catalogRole . '-product-image-primary', $product['id_product'], $photo['id_image']), 'attr') ?>">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-outline-store btn-sm" aria-label="Jadikan <?= esc($label, 'attr') ?> utama">Jadikan utama</button>
                                </form>
                            <?php endif ?>
                            <?php if ($isAdmin): ?>
                                <details>
                                    <summary class="btn btn-outline-danger btn-sm" aria-label="Hapus <?= esc($label, 'attr') ?>">Hapus</summary>
                                    <form method="post" class="mt-2" action="<?= esc(url_to('admin-product-image-delete', $product['id_product'], $photo['id_image']), 'attr') ?>">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-danger btn-sm" aria-label="Ya, hapus <?= esc($label, 'attr') ?>">Ya, hapus</button>
                                    </form>
                                </details>
                            <?php endif ?>
                        </div>
                    </div>
                </li>
            <?php endforeach ?>
        </ul>
    <?php endif ?>
</section>
