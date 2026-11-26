<?php

use App\Libraries\ProductImageStorage;
use App\Models\ProductModel;

$action = $product === null ? url_to($catalogRole . '-products-store') : url_to($catalogRole . '-products-update', $product['id_product']);
$slots = max(0, ProductImageStorage::MAX_FILES - count($images));
?>
<div class="dashboard-product-panel admin-product-form p-3 p-md-4">
    <?php if ($errors !== []): ?>
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0 ps-3">
                <?php foreach ($errors as $message): ?>
                    <li><?= esc($message) ?></li>
                <?php endforeach ?>
            </ul>
        </div>
    <?php endif ?>
    <?php if ($categories === []): ?>
        <p>Produk memerlukan kategori. Tambahkan kategori sebelum membuat produk.</p>
        <?php if ($isAdmin): ?>
            <a class="btn btn-dashboard-primary" href="<?= esc(url_to('admin-categories-create'), 'attr') ?>">Tambah Kategori</a>
        <?php else: ?>
            <p>Hubungi Administrator untuk menambahkan kategori.</p>
        <?php endif ?>
        <a class="btn btn-outline-store" href="<?= esc(url_to($catalogRole . '-products'), 'attr') ?>">Kembali</a>
    <?php else: ?>
        <form method="post" enctype="multipart/form-data" action="<?= esc($action, 'attr') ?>">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label for="name" class="form-label fw-semibold">Nama produk</label>
                <input id="name" name="name" class="form-control" maxlength="150" required
                    value="<?= esc($values['name'] ?? '', 'attr') ?>">
            </div>
            <div class="mb-3">
                <label for="id-category" class="form-label fw-semibold">Kategori</label>
                <select id="id-category" name="id_category" class="form-select" required>
                    <option value="">Pilih kategori</option>
                    <?php foreach ($categories as $category): ?>
                        <?php $categoryValue = (string) $category['id_category']; ?>
                        <option value="<?= esc($categoryValue, 'attr') ?>"<?= $categoryValue === (string) ($values['id_category'] ?? '') ? ' selected' : '' ?>><?= esc($category['name']) ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label fw-semibold">Deskripsi (opsional)</label>
                <textarea id="description" name="description" class="form-control" rows="4" maxlength="16383"><?= esc($values['description'] ?? '') ?></textarea>
            </div>
            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-6">
                    <label for="price" class="form-label fw-semibold">Harga (Rp)</label>
                    <input id="price" name="price" class="form-control" inputmode="decimal" required aria-describedby="price-help"
                        value="<?= esc((string) ($values['price'] ?? ''), 'attr') ?>">
                    <div id="price-help" class="form-text">Gunakan koma atau titik untuk desimal, misalnya 12500,50.</div>
                </div>
                <div class="col-12 col-sm-6">
                    <label for="stock" class="form-label fw-semibold">Stok</label>
                    <input id="stock" name="stock" class="form-control" type="number" min="0" max="4294967295" step="1"
                        required value="<?= esc((string) $values['stock'], 'attr') ?>">
                </div>
                <div class="col-12 col-sm-6">
                    <label for="status" class="form-label fw-semibold">Status</label>
                    <select id="status" name="status" class="form-select" required>
                        <?php foreach (ProductModel::STATUSES as $option): ?>
                            <option value="<?= esc($option, 'attr') ?>"<?= $values['status'] === $option ? ' selected' : '' ?>><?= esc(catalog_status_label($option)) ?></option>
                        <?php endforeach ?>
                    </select>
                </div>
            </div>
            <?php if ($slots > 0): ?>
                <div class="mb-4">
                    <label for="product-images" class="form-label fw-semibold"><?= $product === null ? 'Gambar produk (opsional)' : 'Tambah gambar' ?></label>
                    <input id="product-images" name="images[]" class="form-control" type="file" multiple
                        accept="image/jpeg,image/png,image/webp" aria-describedby="product-image-help"
                        data-image-slots="<?= esc((string) $slots, 'attr') ?>">
                    <div id="product-image-help" class="form-text">JPEG, PNG, atau WebP. Maksimal 2 MB per file dan <?= esc((string) $slots) ?> gambar baru.</div>
                    <p id="product-image-status" class="small mt-2 mb-0" aria-live="polite"></p>
                    <ul id="product-image-preview" class="product-image-grid list-unstyled mt-2 mb-0"></ul>
                </div>
            <?php endif ?>
            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-dashboard-primary" type="submit">Simpan Produk</button>
                <a class="btn btn-outline-store" href="<?= esc(url_to($catalogRole . '-products'), 'attr') ?>">Batal</a>
            </div>
        </form>
    <?php endif ?>
</div>
