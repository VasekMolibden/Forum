<?php
$pageTitle = $pageTitle ?? 'Форум';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body class="bg-body-tertiary">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php"><i class="bi bi-chat-square-text"></i> Форум</a>
        <div class="ms-auto d-flex align-items-center gap-2">
            <?php if (isLoggedIn()): ?>
                <span class="navbar-text text-white">
                    <i class="bi bi-person-circle"></i> <?= e($_SESSION['username'] ?? '') ?>
                    <?php if (isModerator()): ?><span class="badge bg-warning text-dark">модератор</span><?php endif; ?>
                </span>
                <form method="post" action="logout.php" class="mb-0">
                    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                    <button class="btn btn-light btn-sm" type="submit">Выйти</button>
                </form>
            <?php else: ?>
                <a class="btn btn-outline-light btn-sm" href="login.php">Войти</a>
                <a class="btn btn-light btn-sm" href="register.php">Регистрация</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<main class="container py-4">
<?php if ($flash = getFlash()): ?>
    <div class="alert alert-<?= e($flash['type']) ?>" role="alert"><?= e($flash['message']) ?></div>
<?php endif; ?>