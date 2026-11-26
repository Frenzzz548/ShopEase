<?php $action = $category === null ? url_to('admin-categories-store') : url_to('admin-categories-update', $category['id_category']); ?>
<div class="dashboard-product-panel category-form p-3 p-md-4">
    <?php if ($errors !== []): ?>
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0 ps-3">
                <?php foreach ($errors as $message): ?>
                    <li><?= esc($message) ?></li>
                <?php endforeach ?>
            </ul>
        </div>
    <?php endif ?>

    <form method="post" action="<?= esc($action, 'attr') ?>">
        <?= csrf_field() ?>
        <div class="mb-3">
            <label class="form-label fw-semibold" for="name">Nama kategori</label>
            <input class="form-control" id="name" name="name" maxlength="100" required
                aria-describedby="name-help" value="<?= esc($values['name'] ?? '', 'attr') ?>">
            <p class="form-text mb-0" id="name-help"><?= $category === null
                ? 'Slug dibuat otomatis dari nama kategori.'
                : 'Slug saat ini: ' . esc($category['slug']) . '. Mengubah nama juga memperbarui slug.' ?></p>
        </div>
        <div class="mb-4">
            <label class="form-label fw-semibold" for="description">Deskripsi (opsional)</label>
            <textarea class="form-control" id="description" name="description" rows="4" maxlength="16383"><?= esc($values['description'] ?? '') ?></textarea>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-category-primary">Simpan Kategori</button>
            <a class="btn btn-outline-store" href="<?= esc(url_to('admin-categories'), 'attr') ?>">Batal</a>
        </div>
    </form>
</div>
