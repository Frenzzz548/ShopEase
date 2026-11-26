<?= $this->extend('layouts/store') ?>

<?= $this->section('content') ?>
<section class="store-hero" aria-labelledby="hero-title">
    <div class="hero-copy">
        <h1 id="hero-title">Pilihan untuk aktivitas harian</h1>
        <p>Dari sneakers kasual hingga perlengkapan olahraga, temukan barang yang pas untuk rutinitasmu di ShopEase.</p>
        <a class="btn btn-catalog" href="<?= esc(url_to('products'), 'attr') ?>">
            Belanja Sekarang <span aria-hidden="true">&rarr;</span>
        </a>
    </div>
    <img class="hero-image" src="<?= esc(base_url('images/shopease/hero.webp'), 'attr') ?>"
        width="1200" height="800" alt="Headphone, sneakers, ransel, dan botol minum pilihan ShopEase" fetchpriority="high">
</section>

<section class="store-section" aria-labelledby="categories-title">
    <h2 id="categories-title" class="section-title">Kategori Produk</h2>
    <?php if ($categories === []): ?>
        <p class="store-note mb-0">Belum ada kategori produk.</p>
    <?php else: ?>
        <div class="row row-cols-2 row-cols-md-4 g-2 g-lg-3">
        <?php foreach ($categories as $category): ?>
            <?php
            $categoryUrl = url_to('products') . '?' . http_build_query(['category' => $category['slug']]);
            $categoryImage = $categoryImages[$category['id_category']] ?? null;
            ?>
            <div class="col">
                <a class="category-card" href="<?= esc($categoryUrl, 'attr') ?>">
                    <?php if ($categoryImage !== null): ?>
                        <img src="<?= esc($categoryImage, 'attr') ?>" width="56" height="56" alt="">
                    <?php endif ?>
                    <span><?= esc($category['name']) ?></span>
                    <span class="category-arrow" aria-hidden="true">&rarr;</span>
                </a>
            </div>
        <?php endforeach ?>
        </div>
    <?php endif ?>
</section>

<section class="store-section" aria-labelledby="featured-title">
    <div class="section-heading">
        <h2 id="featured-title" class="section-title mb-0">Produk Unggulan</h2>
        <a class="store-link" href="<?= esc(url_to('products'), 'attr') ?>">Lihat semua produk <span aria-hidden="true">&rarr;</span></a>
    </div>
    <?php if ($products !== []): ?>
        <p class="small text-body-secondary mb-3">Hingga enam produk pertama berdasarkan urutan ID katalog.</p>
    <?php endif ?>
    <?= $this->include('frontend/partials/product_grid') ?>
</section>

<section class="store-section store-note" aria-labelledby="benefits-title">
    <h2 id="benefits-title">Jelajahi produk dengan mudah</h2>
    <p class="mb-0">Telusuri kategori dan cek foto, harga Rupiah, serta ketersediaan setiap produk sebelum menentukan pilihan.</p>
</section>
<?= $this->endSection() ?>
