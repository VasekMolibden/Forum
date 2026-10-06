<?php
require 'config.php';

$pageTitle = 'Регистрация';
$error = '';
$username = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $username = trim(postString('username'));
    $password = postString('password');

    if ($username === '' || $password === '') {
        $error = 'Заполните все поля.';
    } elseif (mb_strlen($username, 'UTF-8') < 3 || mb_strlen($username, 'UTF-8') > 50) {
        $error = 'Логин должен содержать от 3 до 50 символов.';
    } elseif (mb_strlen($password, 'UTF-8') < 6) {
        $error = 'Пароль должен содержать не менее 6 символов.';
    } else {
        // Уникальное имя проверяется до добавления новой записи в таблицу users.
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = 'Такой пользователь уже существует.';
        } else {
            // Пароль сохраняется только в виде надёжного хеша, а не открытым текстом.
            $stmt = $pdo->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)');
            $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
            header('Location: login.php?registered=1');
            exit;
        }
    }
}

require 'header.php';
?>
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
<?php require 'footer.php'; ?>
