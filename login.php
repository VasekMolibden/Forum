<?php
require 'config.php';

$pageTitle = 'Вход';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $username = trim(postString('username'));
    $password = postString('password');

    $stmt = $pdo->prepare('SELECT id, username, password_hash, role FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Пароль сравнивается с хешем, сохранённым при регистрации.
    if ($user && password_verify($password, $user['password_hash'])) {
        // Меняем идентификатор сессии после входа, чтобы защититься от фиксации сессии.
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        unset($_SESSION['csrf_token']);
        header('Location: index.php');
        exit;
    }
    $error = 'Неверный логин или пароль.';
}

require 'header.php';
?>
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
<?php require 'footer.php'; ?>
