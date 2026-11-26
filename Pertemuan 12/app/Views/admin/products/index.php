<?php use App\Models\ProductModel; ?>
<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <h1 class="dashboard-title mb-0"><?= esc($title) ?></h1>
    <a class="btn btn-dashboard-primary" href="<?= esc(url_to($catalogRole . '-products-create'), 'attr') ?>">Tambah Produk</a>
</div>

<?php if ($success = session()->getFlashdata('success')): ?>
    <p class="alert alert-success" role="status"><?= esc($success) ?></p>
<?php endif ?>
<?php if ($error = session()->getFlashdata('error')): ?>
    <p class="alert alert-danger" role="alert"><?= esc($error) ?></p>
<?php endif ?>

<form class="dashboard-product-panel row g-2 mx-0 mb-4 p-3 align-items-end" method="get"
    action="<?= esc(url_to($catalogRole . '-products'), 'attr') ?>" aria-label="Cari dan filter produk">
    <div class="col-12 col-md">
        <label for="q" class="form-label small fw-semibold">Nama produk</label>
        <input id="q" name="q" class="form-control form-control-sm" type="search" maxlength="150"
            value="<?= esc($search, 'attr') ?>">
    </div>
    <div class="col-6 col-md">
        <label for="category" class="form-label small fw-semibold">Kategori</label>
        <select id="category" name="category" class="form-select form-select-sm">
            <option value="">Semua kategori</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= esc((string) $category['id_category'], 'attr') ?>"<?= $categoryId === (int) $category['id_category'] ? ' selected' : '' ?>><?= esc($category['name']) ?></option>
            <?php endforeach ?>
        </select>
    </div>
    <div class="col-6 col-md">
        <label for="status" class="form-label small fw-semibold">Status</label>
        <select id="status" name="status" class="form-select form-select-sm">
            <option value="">Semua status</option>
            <?php foreach (ProductModel::STATUSES as $option): ?>
                <option value="<?= esc($option, 'attr') ?>"<?= $status === $option ? ' selected' : '' ?>><?= esc(catalog_status_label($option)) ?></option>
            <?php endforeach ?>
        </select>
    </div>
    <div class="col-12 col-md-auto d-flex flex-wrap gap-2">
        <button class="btn btn-dashboard-primary btn-sm" type="submit">Terapkan</button>
        <?php if ($search !== '' || $categoryId !== null || $status !== ''): ?>
            <a class="btn btn-outline-store btn-sm" href="<?= esc(url_to($catalogRole . '-products'), 'attr') ?>">Reset filter</a>
        <?php endif ?>
    </div>
</form>

<section class="dashboard-product-panel" aria-labelledby="product-list">
    <div class="dashboard-panel-header">
        <h2 id="product-list" class="dashboard-heading mb-0">Daftar produk</h2>
    </div>
    <?php if ($products === []): ?>
        <p class="dashboard-empty mb-0"><?= $search !== '' || $categoryId !== null || $status !== ''
            ? 'Tidak ada produk yang cocok dengan filter.'
            : 'Belum ada produk dalam katalog.' ?></p>
    <?php else: ?>
        <p class="dashboard-table-tip d-xl-none">Geser tabel untuk melihat kolom lainnya.</p>
        <div class="table-responsive" tabindex="0" role="region" aria-labelledby="product-list">
            <table class="table table-sm align-middle dashboard-products admin-product-table mb-0">
                <thead>
                    <tr>
                        <th scope="col">No.</th>
                        <th scope="col">Gambar</th>
                        <th scope="col">Nama produk</th>
                        <th scope="col">Kategori</th>
                        <th scope="col" class="text-end">Harga</th>
                        <th scope="col" class="text-end">Stok</th>
                        <th scope="col">Status</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $index => $product): ?>
                        <?php $image = catalog_image_url($product['primary_image']['image'] ?? null); ?>
                        <tr>
                            <td><?= ($page - 1) * $perPage + $index + 1 ?></td>
                            <td>
                                <?php if ($image === null): ?>
                                    <span class="admin-product-thumb admin-product-thumb-empty">Foto belum tersedia</span>
                                <?php else: ?>
                                    <img class="admin-product-thumb" src="<?= esc($image, 'attr') ?>" alt="" width="72" height="48" loading="lazy">
                                <?php endif ?>
                            </td>
                            <th scope="row"><?= esc($product['name']) ?></th>
                            <td><?= esc($product['category_name']) ?></td>
                            <td class="text-end text-nowrap"><?= esc(catalog_price($product['price'])) ?></td>
                            <td class="text-end"><?= esc(number_format((int) $product['stock'], 0, ',', '.')) ?></td>
                            <td><span class="dashboard-status<?= $product['status'] === ProductModel::STATUS_AVAILABLE ? '' : ' dashboard-status-muted' ?>">
                                <?= esc(catalog_status_label($product['status'])) ?>
                            </span></td>
                            <td>
                                <div class="d-flex flex-wrap align-items-start gap-2">
                                    <a class="btn btn-outline-store btn-sm" href="<?= esc(url_to($catalogRole . '-products-show', $product['id_product']), 'attr') ?>"
                                        aria-label="Detail produk <?= esc($product['name'], 'attr') ?>">Detail</a>
                                    <a class="btn btn-outline-store btn-sm" href="<?= esc(url_to($catalogRole . '-products-edit', $product['id_product']), 'attr') ?>"
                                        aria-label="Edit produk <?= esc($product['name'], 'attr') ?>">Edit</a>
                                    <?php if ($isAdmin): ?>
                                        <details class="category-delete">
                                            <summary class="btn btn-outline-danger btn-sm"
                                                aria-label="Hapus produk <?= esc($product['name'], 'attr') ?>">Hapus</summary>
                                            <form method="post" action="<?= esc(url_to('admin-products-delete', $product['id_product']), 'attr') ?>">
                                                <?= csrf_field() ?>
                                                <p class="small mb-2">Hapus <strong><?= esc($product['name']) ?></strong>? Relasi gambar dan file upload yang tidak lagi dipakai ikut dibersihkan.</p>
                                                <button type="submit" class="btn btn-danger btn-sm">Ya, hapus</button>
                                            </form>
                                        </details>
                                    <?php endif ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    <?php endif ?>
</section>

<?php if ($pageCount > 1): ?>
    <?php
    $filters = array_filter(['q' => $search, 'category' => $categoryId, 'status' => $status],
        static fn ($value) => $value !== null && $value !== '');
    $pageUrl = static fn (int $target): string => url_to($catalogRole . '-products') . '?' . http_build_query($filters + ['page' => $target]);
    ?>
    <nav class="d-flex flex-wrap align-items-center gap-2 mt-3" aria-label="Halaman daftar produk">
        <?php if ($page > 1): ?>
            <a class="btn btn-outline-store btn-sm" href="<?= esc($pageUrl($page - 1), 'attr') ?>">Sebelumnya</a>
        <?php endif ?>
        <span class="small">Halaman <?= esc((string) $page) ?> dari <?= esc((string) $pageCount) ?></span>
        <?php if ($page < $pageCount): ?>
            <a class="btn btn-outline-store btn-sm" href="<?= esc($pageUrl($page + 1), 'attr') ?>">Berikutnya</a>
        <?php endif ?>
    </nav>
<?php endif ?>
<?= $this->endSection() ?>
