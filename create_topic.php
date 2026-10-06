<?php
require 'config.php';
requireLogin();

$pageTitle = 'Новая тема';
$error = '';
$title = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $title = trim(postString('title'));

    // Проверяем данные на сервере, даже если в HTML-поле задан maxlength.
    if ($title === '') {
        $error = 'Введите название темы.';
    } elseif (mb_strlen($title, 'UTF-8') > 255) {
        $error = 'Название темы не должно превышать 255 символов.';
    } else {
        // Связь с автором темы берём из сессии, а не из отправленной формы.
        $stmt = $pdo->prepare('INSERT INTO topics (user_id, title) VALUES (?, ?)');
        $stmt->execute([(int)$_SESSION['user_id'], $title]);
        header('Location: topic.php?id=' . $pdo->lastInsertId());
        exit;
    }
}

require 'header.php';
?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Темы форума</a></li>
        <li class="breadcrumb-item active">Новая тема</li>
    </ol>
</nav>
<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <h1 class="h4 mb-3">Создать тему</h1>
        <?php if ($error !== ''): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
            <div class="mb-3">
                <label class="form-label" for="title">Название темы</label>
                <input id="title" type="text" name="title" maxlength="255" class="form-control" value="<?= e($title) ?>" required>
            </div>
            <button class="btn btn-primary" type="submit">Создать</button>
            <a href="index.php" class="btn btn-outline-secondary">Отмена</a>
        </form>
    </div>
</div>
<?php require 'footer.php'; ?>
