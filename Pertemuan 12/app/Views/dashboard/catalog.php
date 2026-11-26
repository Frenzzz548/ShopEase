<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('content') ?>
<h1 class="dashboard-title"><?= esc($title) ?></h1>

<section class="dashboard-section" aria-labelledby="catalog-summary">
    <h2 class="dashboard-heading" id="catalog-summary">Ringkasan katalog</h2>
    <dl class="dashboard-metrics">
        <?php foreach ($metrics as $label => $value): ?>
            <div class="dashboard-metric">
                <dt><?= esc($label) ?></dt>
                <dd><?= esc(number_format($value, 0, ',', '.')) ?></dd>
            </div>
        <?php endforeach ?>
    </dl>
</section>

<section class="dashboard-section dashboard-product-panel" aria-labelledby="product-summary">
    <div class="dashboard-panel-header">
        <h2 class="dashboard-heading mb-0" id="product-summary"><?= $isAdmin ? 'Produk terbaru' : 'Stok produk terbaru' ?></h2>
    </div>
    <?php if ($products === []): ?>
        <p class="dashboard-empty mb-0">Belum ada produk dalam katalog.</p>
    <?php else: ?>
        <?php if ($isAdmin): ?>
            <p class="dashboard-table-tip d-md-none">Geser tabel untuk melihat kolom lainnya.</p>
        <?php endif ?>
        <div class="table-responsive" tabindex="0" role="region" aria-labelledby="product-summary">
            <table class="table table-sm align-middle dashboard-products<?= $isAdmin ? ' dashboard-products-admin' : '' ?> mb-0">
                <thead>
                    <tr>
                        <th scope="col">Nama produk</th>
                        <?php if ($isAdmin): ?>
                            <th scope="col">Kategori</th>
                            <th scope="col" class="text-end">Harga</th>
                        <?php endif ?>
                        <th scope="col" class="text-end">Stok</th>
                        <th scope="col">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <th scope="row"><?= esc($product['name']) ?></th>
                            <?php if ($isAdmin): ?>
                                <td><?= esc($product['category_name']) ?></td>
                                <td class="text-end text-nowrap"><?= esc(catalog_price($product['price'])) ?></td>
                            <?php endif ?>
                            <td class="text-end">
                                <span<?= (int) $product['stock'] === 0 ? ' class="dashboard-stock-empty"' : '' ?>>
                                    <?= esc(number_format((int) $product['stock'], 0, ',', '.')) ?>
                                </span>
                            </td>
                            <td>
                                <span class="dashboard-status<?= $product['status'] === \App\Models\ProductModel::STATUS_AVAILABLE ? '' : ' dashboard-status-muted' ?>">
                                    <?= esc(catalog_status_label($product['status'])) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    <?php endif ?>
</section>
<?= $this->endSection() ?>
