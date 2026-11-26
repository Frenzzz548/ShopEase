<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <h1 class="dashboard-title mb-0"><?= esc($title) ?></h1>
    <?php if ($isAdmin): ?>
        <a class="btn btn-category-primary" href="<?= esc(url_to('admin-categories-create'), 'attr') ?>">Tambah Kategori</a>
    <?php endif ?>
</div>

<?php if ($success = session()->getFlashdata('success')): ?>
    <p class="alert alert-success" role="status"><?= esc($success) ?></p>
<?php endif ?>
<?php if ($error = session()->getFlashdata('error')): ?>
    <p class="alert alert-danger" role="alert"><?= esc($error) ?></p>
<?php endif ?>

<section class="dashboard-product-panel" aria-labelledby="category-list">
    <div class="dashboard-panel-header">
        <h2 id="category-list" class="dashboard-heading mb-0">Daftar kategori</h2>
        <?php if ($isAdmin && $categories !== []): ?>
            <p class="small text-body-secondary mb-0 mt-1">Kategori yang masih memiliki produk tidak dapat dihapus.</p>
        <?php endif ?>
    </div>
    <?php if ($categories === []): ?>
        <p class="dashboard-empty mb-0">Belum ada kategori.</p>
    <?php else: ?>
        <p class="dashboard-table-tip d-md-none">Geser tabel untuk melihat kolom lainnya.</p>
        <div class="table-responsive" tabindex="0" role="region" aria-labelledby="category-list">
            <table class="table table-sm align-middle dashboard-products category-table mb-0">
                <thead>
                    <tr>
                        <th scope="col">No.</th>
                        <th scope="col">Nama kategori</th>
                        <th scope="col">Slug</th>
                        <th scope="col">Deskripsi</th>
                        <th scope="col" class="text-end">Produk</th>
                        <?php if ($isAdmin): ?>
                            <th scope="col">Aksi</th>
                        <?php endif ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $index => $category): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <th scope="row"><?= esc($category['name']) ?></th>
                            <td class="category-slug"><?= esc($category['slug']) ?></td>
                            <td class="category-description"><?= esc(mb_strimwidth($category['description'] ?? '—', 0, 100, '…')) ?></td>
                            <td class="text-end"><?= esc(number_format((int) $category['product_count'], 0, ',', '.')) ?></td>
                            <?php if ($isAdmin): ?>
                                <td>
                                    <div class="d-flex flex-wrap align-items-start gap-2">
                                        <a class="btn btn-outline-store btn-sm" href="<?= esc(url_to('admin-categories-edit', $category['id_category']), 'attr') ?>"
                                            aria-label="Edit kategori <?= esc($category['name'], 'attr') ?>">Edit</a>
                                        <?php if ((int) $category['product_count'] === 0): ?>
                                            <details class="category-delete">
                                                <summary class="btn btn-outline-danger btn-sm"
                                                    aria-label="Hapus kategori <?= esc($category['name'], 'attr') ?>">Hapus</summary>
                                                <form method="post" action="<?= esc(url_to('admin-categories-delete', $category['id_category']), 'attr') ?>">
                                                    <?= csrf_field() ?>
                                                    <p class="small mb-2">Hapus kategori <strong><?= esc($category['name']) ?></strong>?</p>
                                                    <button type="submit" class="btn btn-danger btn-sm">Ya, hapus</button>
                                                </form>
                                            </details>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" disabled>Hapus</button>
                                        <?php endif ?>
                                    </div>
                                </td>
                            <?php endif ?>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    <?php endif ?>
</section>
<?= $this->endSection() ?>
