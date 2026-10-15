<?php require __DIR__ . '/header.php'; ?>
<div class="auth-wrapper">
    <div class="card shadow-sm border-0"><div class="card-body p-4">
        <h1 class="h4 mb-3 text-center">Вход</h1>
        <?php if (!empty($_GET['registered'])): ?><div class="alert alert-success">Регистрация прошла успешно — войдите.</div><?php endif; ?>
        <?php if ($error !== ''): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
            <div class="mb-3">
                <label class="form-label" for="username">Логин</label>
                <input id="username" type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">Пароль</label>
                <input id="password" type="password" name="password" class="form-control" required>
            </div>
            <button class="btn btn-primary w-100" type="submit">Войти</button>
        </form>
        <hr>
        <p class="text-center mb-0">Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
    </div></div>
</div>
<?php require __DIR__ . '/footer.php'; ?>
