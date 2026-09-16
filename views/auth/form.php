<div class="auth-shell">
    <section class="auth-story">
        <a href="/login" class="brand auth-reveal auth-reveal-brand"><span class="brand-icon">▦</span><span>SIM Inventory<small>INVENTORY MANAGEMENT</small></span></a>
        <div class="auth-story-content">
            <div class="eyebrow auth-reveal auth-reveal-eyebrow">LEBIH RAPI. LEBIH TERKENDALI.</div>
            <h1 class="auth-reveal auth-reveal-title">Setiap barang,<br>tercatat dengan<br><em>baik.</em></h1>
            <p class="auth-reveal auth-reveal-copy">Satu tempat untuk mengelola stok, mencatat pergerakan barang, dan melihat gambaran inventori Anda.</p>
            <div class="story-grid auth-reveal auth-reveal-capabilities"><div><strong>01</strong><span>Kelola inventori</span></div><div><strong>02</strong><span>Pantau transaksi</span></div><div><strong>03</strong><span>Susun laporan</span></div></div>
        </div>
        <small class="auth-reveal auth-reveal-footer">SIM Inventory · Project Pemrograman Web 1</small>
    </section>
    <section class="auth-form-panel">
        <div class="auth-form <?= $message ? 'has-error' : '' ?>">
            <span class="eyebrow auth-reveal auth-form-eyebrow">WORKSPACE ANDA</span>
            <h2 class="auth-reveal auth-form-title"><?= e($title) ?></h2>
            <p class="text-secondary auth-reveal auth-form-copy"><?= $mode === 'login' ? 'Masuk untuk melanjutkan pengelolaan inventori.' : 'Daftar untuk mulai mencatat transaksi inventori.' ?></p>
            <?php if ($message): ?><div class="alert alert-danger auth-error" role="alert"><?= e($message) ?></div><?php endif; ?>
            <form method="post" action="/<?= e($mode) ?>" class="auth-fields auth-reveal" data-submit-feedback><?= csrf_field() ?>
                <?php if ($mode === 'register'): ?><div class="auth-field"><label class="form-label" for="name">Nama lengkap</label><input class="form-control" id="name" name="name" maxlength="100" required autocomplete="name" value="<?= e($old['name'] ?? '') ?>"><div class="field-error"><?= e($errors['name'] ?? '') ?></div></div><?php endif; ?>
                <div class="auth-field"><label class="form-label" for="email">Alamat email</label><input class="form-control" type="email" name="email" id="email" maxlength="150" required autocomplete="<?= $mode === 'login' ? 'username' : 'email' ?>" placeholder="nama@example.com" value="<?= e($old['email'] ?? '') ?>"><div class="field-error"><?= e($errors['email'] ?? '') ?></div></div>
                <?php if ($mode === 'login'): ?><div class="auth-remember"><input class="form-check-input" type="checkbox" id="remember-email"><label class="form-check-label" for="remember-email">Ingat email saya</label><small>Hanya alamat email yang disimpan di perangkat ini.</small></div><?php endif; ?>
                <div class="auth-field"><label class="form-label" for="password">Password</label><div class="password-field"><input class="form-control" type="password" name="password" id="password" required maxlength="72" <?= $mode === 'register' ? 'minlength="10"' : '' ?> autocomplete="<?= $mode === 'login' ? 'current-password' : 'new-password' ?>"><button class="password-toggle" type="button" aria-label="Tampilkan password" aria-controls="password">Tampilkan</button></div><div class="field-error"><?= e($errors['password'] ?? '') ?></div></div>
                <?php if ($mode === 'register'): ?><div class="auth-field"><label class="form-label" for="password_confirmation">Konfirmasi password</label><div class="password-field"><input class="form-control" type="password" id="password_confirmation" name="password_confirmation" minlength="10" maxlength="72" required autocomplete="new-password"><button class="password-toggle" type="button" aria-label="Tampilkan konfirmasi password" aria-controls="password_confirmation">Tampilkan</button></div><div class="field-error"><?= e($errors['password_confirmation'] ?? '') ?></div></div><p class="small text-secondary">Akun baru memiliki akses Operator. Password minimal 10 karakter.</p><?php endif; ?>
                <button class="btn btn-primary auth-submit" type="submit" data-loading-label="Memproses…"><span class="button-spinner" aria-hidden="true"></span><span class="button-label"><?= $mode === 'login' ? 'Masuk ke workspace →' : 'Buat akun Operator →' ?></span></button>
            </form>
            <p class="auth-switch auth-reveal auth-form-switch"><?= $mode === 'login' ? 'Belum memiliki akun? <a href="/register">Daftar Operator</a>' : 'Sudah memiliki akun? <a href="/login">Masuk di sini</a>' ?></p>
            <div class="auth-security auth-reveal auth-form-security">◈ Akses berbasis role · Sesi terlindungi</div>
        </div>
    </section>
</div>
