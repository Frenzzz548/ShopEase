<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('content') ?>
<h1 class="dashboard-title"><?= esc($title) ?></h1>
<?= $this->include('admin/products/form') ?>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= esc(base_url('js/admin-product-images.js'), 'attr') ?>" defer></script>
<?= $this->endSection() ?>
