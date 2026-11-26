<?= $this->extend('layouts/store') ?>

<?= $this->section('content') ?>
<section class="store-note auth-panel" aria-labelledby="auth-title">
    <h1 class="page-title" id="auth-title">Login</h1>
    <?php $success = session()->getFlashdata('success'); ?>
    <?php if ($success): ?>
        <p class="alert alert-success" role="status"><?= esc($success) ?></p>
    <?php endif ?>
    <?php if ($loginError !== null): ?>
        <p class="alert alert-danger" role="alert"><?= esc($loginError) ?></p>
    <?php endif ?>

    <form method="post" action="<?= esc(url_to('login'), 'attr') ?>">
        <?= csrf_field() ?>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input id="email" name="email" type="email" class="form-control" autocomplete="username" maxlength="100"
                required value="<?= esc($email, 'attr') ?>">
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input id="password" name="password" type="password" class="form-control" autocomplete="current-password"
                required maxlength="72">
        </div>
        <button type="submit" class="btn btn-catalog w-100">Login</button>
    </form>
    <p class="mt-3 mb-0">Belum punya akun? <a href="<?= esc(url_to('register'), 'attr') ?>">Daftar</a>.</p>
</section>
<?= $this->endSection() ?>
