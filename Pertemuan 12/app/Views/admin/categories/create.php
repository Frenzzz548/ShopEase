<?= $this->extend('layouts/dashboard') ?>

<?= $this->section('content') ?>
<h1 class="dashboard-title"><?= esc($title) ?></h1>
<?= $this->include('admin/categories/form') ?>
<?= $this->endSection() ?>
