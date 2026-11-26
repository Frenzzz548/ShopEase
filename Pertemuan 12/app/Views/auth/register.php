<?= $this->extend('layouts/store') ?>

<?= $this->section('content') ?>
<section class="store-note auth-panel" aria-labelledby="auth-title">
    <h1 class="page-title" id="auth-title">Daftar Akun</h1>
    <p>Buat akun Customer untuk masuk ke ShopEase.</p>

    <?php if ($errors !== []): ?>
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0 ps-3">
                <?php foreach ($errors as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach ?>
            </ul>
        </div>
    <?php endif ?>

    <form method="post" action="<?= esc(url_to('register'), 'attr') ?>">
        <?= csrf_field() ?>
        <div class="mb-3">
            <label for="name" class="form-label">Nama lengkap</label>
            <input id="name" name="name" class="form-control" autocomplete="name" maxlength="100"
                required value="<?= esc($values['name'] ?? '', 'attr') ?>">
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input id="email" name="email" type="email" class="form-control" autocomplete="email" maxlength="100"
                required value="<?= esc($values['email'] ?? '', 'attr') ?>">
        </div>
        <div class="mb-3">
            <label for="phone" class="form-label">Nomor telepon (opsional)</label>
            <input id="phone" name="phone" type="tel" class="form-control" autocomplete="tel" maxlength="20"
                value="<?= esc($values['phone'] ?? '', 'attr') ?>">
        </div>
        <div class="mb-3">
            <label for="address" class="form-label">Alamat (opsional)</label>
            <textarea id="address" name="address" class="form-control" autocomplete="street-address"
                rows="2" maxlength="16383"><?= esc($values['address'] ?? '') ?></textarea>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input id="password" name="password" type="password" class="form-control" autocomplete="new-password"
                required minlength="8" maxlength="72" aria-describedby="password-help">
            <div class="form-text" id="password-help">Minimal 8 karakter, maksimal 72 byte.</div>
        </div>
        <div class="mb-3">
            <label for="password-confirm" class="form-label">Konfirmasi password</label>
            <input id="password-confirm" name="password_confirm" type="password" class="form-control"
                autocomplete="new-password" required maxlength="72">
        </div>
        <button type="submit" class="btn btn-catalog w-100">Daftar</button>
    </form>
    <p class="mt-3 mb-0">Sudah punya akun? <a href="<?= esc(url_to('login'), 'attr') ?>">Login</a>.</p>
</section>
<?= $this->endSection() ?>
