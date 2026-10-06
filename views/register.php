<?php require __DIR__ . '/header.php'; ?>
<div class="auth-wrapper">
    <div class="card shadow-sm border-0"><div class="card-body p-4">
        <h1 class="h4 mb-3 text-center">Регистрация</h1>
        <?php if ($error !== ''): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
            <div class="mb-3">
                <label class="form-label" for="username">Логин</label>
                <input id="username" type="text" name="username" maxlength="50" class="form-control" value="<?= e($username) ?>" required>
                <div class="form-text">От 3 до 50 символов.</div>
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">Пароль</label>
                <input id="password" type="password" name="password" class="form-control" required>
                <div class="form-text">Минимум 6 символов.</div>
            </div>
            <button class="btn btn-primary w-100" type="submit">Зарегистрироваться</button>
        </form>
        <hr>
        <p class="text-center mb-0">Уже есть аккаунт? <a href="login.php">Войти</a></p>
    </div></div>
</div>
<?php require __DIR__ . '/footer.php'; ?>
