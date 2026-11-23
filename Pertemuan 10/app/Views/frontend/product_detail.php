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
        <img class="product-image detail-image" src="<?= esc(base_url('images/shopease/' . $product['image']), 'attr') ?>"
            width="720" height="480" alt="<?= esc($product['name'], 'attr') ?>">
    </div>
    <div class="col-md-6">
        <p class="product-category"><?= esc($categoryName) ?></p>
        <h1 class="page-title"><?= esc($product['name']) ?></h1>
        <p class="detail-price">Rp <?= esc(number_format($product['price'], 0, ',', '.')) ?></p>
        <p class="detail-description"><?= esc($product['description']) ?></p>
        <p class="detail-stock"><?= $product['stock'] > 0 ? 'Stok tersedia ' . esc((string) $product['stock']) . ' unit' : 'Stok habis' ?></p>
        <a class="btn btn-outline-store" href="<?= esc(url_to('products'), 'attr') ?>">
            <span aria-hidden="true">&larr;</span> Kembali ke Katalog
        </a>
    </div>
</article>
<?= $this->endSection() ?>
