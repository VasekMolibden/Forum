<?php require __DIR__ . '/header.php'; ?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h4 mb-0">Настройки профиля</h1>
            <a class="btn btn-outline-secondary btn-sm" href="profile.php">Вернуться в профиль</a>
        </div>
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h2 class="h5 mb-3">Сменить пароль</h2>
                <?php if ($error !== ''): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                    <div class="mb-3">
                        <label class="form-label" for="current_password">Текущий пароль</label>
                        <input id="current_password" type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="new_password">Новый пароль</label>
                        <input id="new_password" type="password" name="new_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="confirm_password">Повторите новый пароль</label>
                        <input id="confirm_password" type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <button class="btn btn-primary" type="submit">Изменить пароль</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/footer.php'; ?>
